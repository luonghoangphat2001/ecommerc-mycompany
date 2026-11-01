<?php

namespace App\Ecommerce\Checkout\Services\Pipes;

use App\Ecommerce\Checkout\DTOs\CheckoutResultDTO;
use App\Ecommerce\Checkout\DTOs\CheckoutRequestDTO;
use App\Ecommerce\Shipping\Contracts\ShippingServiceInterface;
use App\Ecommerce\Shipping\Services\PackageCalculator;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\DB;
use App\Settings\InventorySettings;
use Closure;

class ResolveShippingFeePipe
{
    public function __construct(
        protected ShippingServiceInterface $shippingService,
        protected PackageCalculator $packageCalculator,
        protected InventorySettings $inventorySettings
    ) {}

    public function handle(array $passable, Closure $next)
    {
        /** @var CheckoutRequestDTO $request */
        $request = $passable['request'];
        /** @var CheckoutResultDTO $result */
        $result = $passable['result'];

        if (!$request->shippingAddress) {
            return $next($passable);
        }

        try {
            $destArray = $request->shippingAddress->toArray();
            $country = $destArray['country_code'] ?? $destArray['country'] ?? 'VN';
            $state = $destArray['state_id'] ?? $destArray['state'] ?? null;
            $ward = $destArray['ward_id'] ?? $destArray['ward'] ?? null;
            $postcode = $destArray['postal_code'] ?? $destArray['zip'] ?? null;

            // 1. Resolve matching zone for customer destination
            $zone = $this->shippingService->resolveMatchingZone($country, $state, $postcode, $ward);

            /** @var ShippingMethod|null $method */
            $method = null;
            $reqMethod = $request->shippingMethod;

            if ($zone) {
                if (is_numeric($reqMethod)) {
                    $method = $zone->methods()->where('id', (int) $reqMethod)->where('is_enabled', true)->first();
                }

                if (!$method && !empty($reqMethod)) {
                    $method = $zone->methods()->where('type', $reqMethod)->where('is_enabled', true)->first();
                }

                if (!$method && !empty($reqMethod)) {
                    $method = $zone->methods()->where('name', $reqMethod)->where('is_enabled', true)->first();
                }

                if (!$method) {
                    $method = $zone->methods()->where('is_enabled', true)->first();
                }
            }

            // Fallback global method if zone has none or not found
            if (!$method && is_numeric($reqMethod)) {
                $method = ShippingMethod::query()->where('id', (int) $reqMethod)->where('is_enabled', true)->first();
            }

            if (!$method && !empty($reqMethod)) {
                $method = ShippingMethod::query()->where('type', $reqMethod)->where('is_enabled', true)->first();
            }

            if (!$method) {
                $method = ShippingMethod::query()->where('is_enabled', true)->first();
            }

            if (!$method || !$method->is_enabled) {
                return $next($passable);
            }

            // 2. Calculate package weight and dimensions
            $package = $this->packageCalculator->calculatePackage($request->items ?? []);

            // 3. Count participating inventories if split shipping is enabled
            $totalPackages = 1;
            if ($this->inventorySettings->split_shipping_enabled) {
                $productIds = collect($request->items)->pluck('product_id')->filter()->toArray();
                if (!empty($productIds)) {
                    $inventoriesCount = DB::table('shop_product_inventory_stocks')
                        ->whereIn('shop_product_id', $productIds)
                        ->where('stock_quantity', '>', 0)
                        ->distinct()
                        ->pluck('warehouse_id')
                        ->count();

                    $totalPackages = max(1, $inventoriesCount);
                }
            }

            // 4. Calculate fee using centralized shipping service
            $destination = array_merge($destArray, [
                'country' => $country,
                'state' => $state,
                'ward' => $ward,
                'postcode' => $postcode,
                'subtotal' => $result->subtotal,
            ]);

            $shippingCost = $this->shippingService->calculateMethodFee(
                $method,
                $destination,
                $result->subtotal,
                $package
            );

            if ($shippingCost !== null && $shippingCost >= 0) {
                $finalShippingTotal = (int) ($shippingCost * $totalPackages);
                $result->shippingTotal = $finalShippingTotal;
                $result->total += $finalShippingTotal;
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('ResolveShippingFeePipe error: ' . $e->getMessage());
        }

        return $next($passable);
    }
}
