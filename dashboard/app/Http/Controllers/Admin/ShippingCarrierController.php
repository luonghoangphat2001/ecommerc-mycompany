<?php

namespace App\Http\Controllers\Admin;

use App\Ecommerce\Shipping\Carriers\CarrierManager;
use App\Http\Controllers\Admin\Traits\HasAdminSelectOptions;
use App\Http\Controllers\Admin\Traits\HasShippingTabs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CarrierUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingCarrierController extends Controller
{
    use HasAdminSelectOptions;
    use HasShippingTabs;

    public function __construct(protected CarrierManager $carrierManager) {}

    protected function activeTab(): string
    {
        return 'shipping-carriers';
    }

    /**
     * Display list of all carrier integrations.
     */
    public function index(Request $request): View
    {
        $carriers = $this->carrierManager->getAllCarriers();
        $zones = $this->shippingZoneRecords();

        return view('admin.shipping.carriers', [
            'title' => 'admin.sidebar.shipping_carriers',
            'carriers' => $carriers,
            'zones' => $zones,
            'tabs' => $this->tabs(),
            'activeTab' => $this->activeTab(),
        ]);
    }

    /**
     * Test connection to a carrier API.
     */
    public function testConnection(Request $request, string $carrier): JsonResponse
    {
        try {
            $result = $this->carrierManager->testConnection($carrier);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối: ' . $e->getMessage(),
                'details' => ['exception' => $e->getMessage()],
            ], 400);
        }
    }

    /**
     * Update configuration for a carrier.
     */
    public function update(CarrierUpdateRequest $request, string $carrier): RedirectResponse
    {
        $adapter = $this->carrierManager->getAdapter($carrier);
        if (! $adapter) {
            return redirect()->back()->with('error', 'Hãng vận chuyển không tồn tại.');
        }

        $validated = $request->validated();

        $data = [
            'enabled' => $request->boolean('enabled'),
            'sandbox' => $request->boolean('sandbox'),
            'markup_amount' => (int) ($validated['markup_amount'] ?? 0),
            'markup_percent' => (float) ($validated['markup_percent'] ?? 0),
            'fallback_rate' => (int) ($validated['fallback_rate'] ?? 35000),
            'sender_province' => $validated['sender_province'] ?? 'Hồ Chí Minh',
            'sender_district' => $validated['sender_district'] ?? 'Quận 1',
        ];

        // Only update credentials if non-masked values are supplied
        $this->addUnmaskedValue($data, $validated, 'api_token');
        $this->addUnmaskedValue($data, $validated, 'client_secret');
        $this->addTrimmedValue($data, $validated, 'shop_id');
        $this->addTrimmedValue($data, $validated, 'client_id');

        $this->carrierManager->saveCarrierConfig($carrier, $data);

        return redirect()->route('admin.shipping-carriers.index')
            ->with('status', 'Đã lưu cấu hình hãng vận chuyển ' . $adapter->getName() . ' thành công.');
    }

    private function addUnmaskedValue(array &$data, array $validated, string $key): void
    {
        if (! empty($validated[$key]) && ! str_contains($validated[$key], '••••••')) {
            $data[$key] = trim($validated[$key]);
        }
    }

    private function addTrimmedValue(array &$data, array $validated, string $key): void
    {
        if (! empty($validated[$key])) {
            $data[$key] = trim($validated[$key]);
        }
    }
}
