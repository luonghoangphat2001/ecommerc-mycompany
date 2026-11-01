<?php

namespace App\Http\Controllers\Api\Agent;

use App\Ecommerce\Shipping\Carriers\CarrierManager;
use App\Ecommerce\Shipping\Contracts\ShippingServiceInterface;
use App\Ecommerce\Shipping\Services\PackageCalculator;
use App\Ecommerce\Shipping\Support\VietnamLocations;
use App\Http\Controllers\Controller;
use App\Models\OrderShipping;
use App\Models\ShippingZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentShippingController extends Controller
{
    public function __construct(
        protected ShippingServiceInterface $shippingService,
        protected CarrierManager $carrierManager,
        protected PackageCalculator $packageCalculator
    ) {
    }

    /**
     * List all active shipping zones and rules.
     */
    public function zones(Request $request): JsonResponse
    {
        if (!app(\App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface::class)->isShippingEnabled()) {
            return response()->json([
                'success' => true,
                'shipping_enabled' => false,
                'data' => [],
            ]);
        }

        $zones = ShippingZone::with(['methods' => fn($q) => $q->where('is_enabled', true)])
            ->when(
                \Illuminate\Support\Facades\Schema::hasColumn('shop_shipping_zones', 'is_active'),
                fn($q) => $q->where('is_active', true)
            )
            ->orderBy('sort')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $zones->map(fn($z) => [
                'id' => $z->id,
                'code' => $z->code,
                'name' => $z->name,
                'priority' => $z->sort,
                'methods' => $z->methods->map(fn($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'type' => $m->type instanceof \BackedEnum ? $m->type->value : (string) $m->type,
                    'cost' => $m->settings['cost'] ?? 0,
                    'currency' => $m->settings['currency'] ?? 'VND',
                    'estimated_days' => $m->settings['estimated_days'] ?? null,
                    'min_amount' => $m->settings['min_amount'] ?? null,
                ]),
            ]),
        ]);
    }

    /**
     * Supported destinations breakdown.
     */
    public function destinations(Request $request): JsonResponse
    {
        $continents = config('continents', []);
        $countries = config('countries', []);

        return response()->json([
            'success' => true,
            'data' => [
                'domestic' => [
                    'country' => 'VN',
                    'country_name' => 'Việt Nam',
                    'north_provinces' => array_keys(VietnamLocations::NORTH_PROVINCES),
                    'central_provinces' => array_keys(VietnamLocations::CENTRAL_PROVINCES),
                    'south_provinces' => array_keys(VietnamLocations::SOUTH_PROVINCES),
                    'total_provinces' => 63,
                ],
                'international_continents' => array_keys($continents),
                'total_supported_countries' => count($countries),
            ],
        ]);
    }

    /**
     * Estimate shipping rates for AI Agent inquiry.
     */
    public function estimate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country' => 'nullable|string|max:10',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'ward' => 'nullable|string|max:100',
            'subtotal' => 'nullable|integer|min:0',
            'items' => 'nullable|array',
            'weight' => 'nullable|integer|min:0',
        ]);

        $items = $validated['items'] ?? [];
        $package = !empty($items)
            ? $this->packageCalculator->calculatePackage($items)
            : ['weight' => (int) ($validated['weight'] ?? 500), 'length' => 20, 'width' => 15, 'height' => 10];

        $subtotal = (int) ($validated['subtotal'] ?? 0);

        if (!app(\App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface::class)->isShippingEnabled()) {
            return response()->json([
                'success' => true,
                'shipping_enabled' => false,
                'destination' => [
                    'country' => $validated['country'] ?? 'VN',
                    'state' => $validated['state'] ?? null,
                ],
                'subtotal' => $subtotal,
                'package' => $package,
                'shipping_options' => [],
            ]);
        }

        $methods = $this->shippingService->getAvailableMethods(
            $validated['country'] ?? 'VN',
            $validated['state'] ?? null,
            null,
            $validated['ward'] ?? null,
            $subtotal,
            $package
        );

        return response()->json([
            'success' => true,
            'destination' => [
                'country' => $validated['country'] ?? 'VN',
                'state' => $validated['state'] ?? null,
            ],
            'subtotal' => $subtotal,
            'package' => $package,
            'shipping_options' => $methods,
        ]);
    }

    /**
     * Retrieve shipment tracking status.
     */
    public function track(Request $request, string $trackingNumber): JsonResponse
    {
        $orderShipping = OrderShipping::with('order')
            ->where('tracking_number', $trackingNumber)
            ->first();

        if ($orderShipping) {
            $carrier = $orderShipping->metadata['carrier'] ?? 'standard';
            $adapter = $this->carrierManager->getAdapter($carrier);
            $carrierTracking = $adapter ? $adapter->getTracking($trackingNumber) : null;

            return response()->json([
                'success' => true,
                'found' => true,
                'tracking_number' => $trackingNumber,
                'order_number' => $orderShipping->order?->number,
                'carrier' => $carrier,
                'method' => $orderShipping->method,
                'status' => $carrierTracking['status'] ?? ($orderShipping->metadata['status'] ?? 'processing'),
                'status_text' => $carrierTracking['status_text'] ?? ($orderShipping->metadata['status_text'] ?? 'Đang giao hàng'),
                'timeline' => $carrierTracking['history'] ?? [],
            ]);
        }

        // Check across carrier adapters directly if not in local order database
        foreach ($this->carrierManager->getAllCarriers() as $carrier) {
            $tracking = $carrier->getTracking($trackingNumber);
            if (!empty($tracking['history']) || ($tracking['status'] ?? '') !== 'unknown') {
                return response()->json([
                    'success' => true,
                    'found' => true,
                    'tracking_number' => $trackingNumber,
                    'carrier' => $carrier->getId(),
                    'status' => $tracking['status'],
                    'status_text' => $tracking['status_text'],
                    'timeline' => $tracking['history'],
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'found' => false,
            'message' => 'Không tìm thấy thông tin vận đơn với mã: ' . $trackingNumber,
        ], 404);
    }

    /**
     * Shipping policies, free shipping rules and SLAs.
     */
    public function policies(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'domestic' => [
                    'free_shipping_threshold' => 500000,
                    'free_shipping_text' => 'Miễn phí vận chuyển cho đơn hàng từ 500.000₫ toàn quốc.',
                    'sla_hanoi_hcm' => '1-2 ngày làm việc (Hỏa tốc 2 giờ tại nội thành).',
                    'sla_nationwide' => '2-4 ngày làm việc.',
                    'carriers' => ['GHN', 'GHTK', 'Viettel Post', 'VNPost'],
                ],
                'international' => [
                    'coverage' => 'Hơn 200 quốc gia trên 6 châu lục (Châu Á, Châu Âu, Bắc Mỹ, Nam Mỹ, Châu Phi, Châu Đại Dương).',
                    'sla_express' => '3-5 ngày làm việc qua DHL Express / FedEx.',
                    'sla_standard' => '7-14 ngày làm việc.',
                ],
            ],
        ]);
    }
}
