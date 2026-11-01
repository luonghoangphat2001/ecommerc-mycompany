<?php

namespace App\Http\Controllers\Admin;

use App\Ecommerce\Shipping\Carriers\CarrierManager;
use App\Http\Controllers\Admin\Traits\HasShippingTabs;
use App\Http\Controllers\Controller;
use App\Models\ShippingZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingCarrierController extends Controller
{
    use HasShippingTabs;

    public function __construct(protected CarrierManager $carrierManager)
    {
    }

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
        $zones = ShippingZone::orderBy('sort')->get();

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
    public function update(Request $request, string $carrier): RedirectResponse
    {
        $adapter = $this->carrierManager->getAdapter($carrier);
        if (!$adapter) {
            return redirect()->back()->with('error', 'Hãng vận chuyển không tồn tại.');
        }

        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'sandbox' => 'nullable|boolean',
            'api_token' => 'nullable|string',
            'shop_id' => 'nullable|string',
            'client_id' => 'nullable|string',
            'client_secret' => 'nullable|string',
            'markup_amount' => 'nullable|integer|min:0',
            'markup_percent' => 'nullable|numeric|min:0|max:100',
            'fallback_rate' => 'nullable|integer|min:0',
            'sender_province' => 'nullable|string',
            'sender_district' => 'nullable|string',
        ]);

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
        if (!empty($validated['api_token']) && !str_contains($validated['api_token'], '••••••')) {
            $data['api_token'] = trim($validated['api_token']);
        }
        if (!empty($validated['shop_id'])) {
            $data['shop_id'] = trim($validated['shop_id']);
        }
        if (!empty($validated['client_id'])) {
            $data['client_id'] = trim($validated['client_id']);
        }
        if (!empty($validated['client_secret']) && !str_contains($validated['client_secret'], '••••••')) {
            $data['client_secret'] = trim($validated['client_secret']);
        }

        $this->carrierManager->saveCarrierConfig($carrier, $data);

        return redirect()->route('admin.shipping-carriers.index')
            ->with('status', 'Đã lưu cấu hình hãng vận chuyển ' . $adapter->getName() . ' thành công.');
    }
}
