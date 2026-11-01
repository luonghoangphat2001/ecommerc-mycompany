<?php

namespace App\Http\Controllers\Admin;

use App\Ecommerce\Shipping\Carriers\CarrierManager;
use App\Http\Controllers\Admin\Traits\HasShippingTabs;
use App\Http\Controllers\Controller;
use App\Models\OrderShipping;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingOrderController extends Controller
{
    use HasShippingTabs;

    public function __construct(protected CarrierManager $carrierManager)
    {
    }

    protected function activeTab(): string
    {
        return 'shipping-orders';
    }

    /**
     * Display list of order shipments.
     */
    public function index(Request $request): View
    {
        $query = OrderShipping::with(['order.shippingAddress', 'method'])
            ->latest('id');

        if ($q = $request->input('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('tracking_number', 'like', "%{$q}%")
                    ->orWhere('method', 'like', "%{$q}%")
                    ->orWhereHas('order', function ($o) use ($q) {
                        $o->where('number', 'like', "%{$q}%");
                    });
            });
        }

        $items = $query->paginate(20)->withQueryString();

        return view('admin.shipping.orders', [
            'title' => 'admin.sidebar.shipping_orders',
            'items' => $items,
            'tabs' => $this->tabs(),
            'activeTab' => $this->activeTab(),
        ]);
    }

    /**
     * Live carrier tracking lookup for an order shipment.
     */
    public function track(Request $request, OrderShipping $orderShipping): JsonResponse
    {
        $trackingNumber = $orderShipping->tracking_number;
        if (!$trackingNumber) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng này chưa có mã vận đơn (Tracking number).',
            ], 404);
        }

        $carrierId = $orderShipping->metadata['carrier'] ?? 'standard';
        $adapter = $this->carrierManager->getAdapter($carrierId);

        if ($adapter) {
            $tracking = $adapter->getTracking($trackingNumber);
            return response()->json([
                'success' => true,
                'data' => $tracking,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tracking_number' => $trackingNumber,
                'carrier' => $carrierId,
                'status' => $orderShipping->metadata['status'] ?? 'processing',
                'status_text' => $orderShipping->metadata['status_text'] ?? 'Đang giao hàng',
                'history' => [
                    ['time' => $orderShipping->updated_at?->format('Y-m-d H:i:s'), 'status' => 'Đã tạo vận đơn'],
                ],
            ],
        ]);
    }
}
