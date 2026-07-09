<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\OrderRefund;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return Order::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.orders';
    }

    protected function routePrefix(): string
    {
        return 'admin.orders';
    }

    protected function searchable(): array
    {
        return ['number', 'status', 'currency'];
    }

    protected function fields(): array
    {
        return [
            'status' => ['label' => 'Status', 'rules' => ['required', 'string', 'max:30']],
            'currency' => ['label' => 'Currency', 'rules' => ['required', 'string', 'max:10']],
            'total' => ['label' => 'Tổng tiền', 'type' => 'number', 'rules' => ['required', 'numeric', 'min:0']],
        ];
    }

    public function index(Request $request): View
    {
        $query = Order::with(['user', 'shippingAddress', 'payments', 'shipping']);
        
        // Search
        $this->applySearch($query, $request);

        // Filters
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($paymentStatus = $request->query('payment_status')) {
            $query->whereHas('payments', function ($q) use ($paymentStatus) {
                $q->where('status', $paymentStatus);
            });
        }
        if ($customer = $request->query('customer')) {
            $query->where(function ($q) use ($customer) {
                $q->whereHas('user', function ($u) use ($customer) {
                    $u->where('name', 'like', "%{$customer}%")->orWhere('email', 'like', "%{$customer}%");
                })->orWhereHas('shippingAddress', function ($a) use ($customer) {
                    $a->where('first_name', 'like', "%{$customer}%")
                      ->orWhere('last_name', 'like', "%{$customer}%")
                      ->orWhere('phone', 'like', "%{$customer}%");
                });
            });
        }
        if ($dateFrom = $request->query('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->query('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        if ($paymentMethod = $request->query('payment_method')) {
            $query->whereHas('payments', function ($q) use ($paymentMethod) {
                $q->where('method', $paymentMethod);
            });
        }
        if ($shippingMethod = $request->query('shipping_method')) {
            $query->whereHas('shipping', function ($q) use ($shippingMethod) {
                $q->where('method', $shippingMethod);
            });
        }

        $items = $query->latest('id')->paginate(15)->withQueryString();

        return view('admin.orders.index', [
            'title' => __($this->title()),
            'items' => $items,
            'routePrefix' => $this->routePrefix(),
            'canCreate' => $this->canCreate(),
            'canEdit' => $this->canEdit(),
            'canDelete' => $this->canDelete(),
            'canImportExport' => $this->canImportExport(),
            'headerActions' => $this->headerActions(),
        ]);
    }

    public function show(int $id): View
    {
        $order = Order::with([
            'items.product.featuredImage',
            'payments',
            'refunds',
            'metas',
            'coupons',
            'shipping',
            'shipping.tax',
            'shippingAddress',
            'billingAddress',
            'user',
            'taxes',
            'activities',
        ])->findOrFail($id);

        $checkoutSettings = app(\App\Settings\CheckoutSettings::class);
        $couponSettings = app(\App\Settings\CouponSettings::class);

        return view('admin.orders.show', compact('order', 'checkoutSettings', 'couponSettings'));
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,new,processing,delivering,completed,cancelled,refunded'],
        ]);

        $order = Order::findOrFail($id);
        
        $orderService = app(\App\Ecommerce\Order\Contracts\OrderServiceInterface::class);
        $orderService->updateStatus($order, \App\Ecommerce\Order\Enums\OrderStatus::from($data['status']));

        return redirect()->route('admin.orders.show', $order->id)->with('status', 'Đã cập nhật trạng thái đơn hàng');
    }

    public function storePayment(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'method' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'string', 'max:50'],
            'provider' => ['nullable', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $order = Order::findOrFail($id);

        Payment::create($data + [
            'order_id' => $order->id,
            'currency' => $order->currency ?: 'VND',
        ]);

        return redirect()->route('admin.orders.show', $order->id)->with('status', 'Đã thêm payment');
    }

    public function storeRefund(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:0'],
            'reason' => ['nullable', 'string'],
        ]);

        $order = Order::findOrFail($id);
        OrderRefund::create($data + ['order_id' => $order->id]);

        return redirect()->route('admin.orders.show', $order->id)->with('status', 'Đã tạo refund');
    }

    public function edit(int $id): View
    {
        $order = Order::with([
            'items.product.featuredImage',
            'payments',
            'refunds',
            'metas',
            'coupons',
            'shipping',
            'shippingAddress',
            'billingAddress',
            'user',
            'taxes',
        ])->findOrFail($id);

        $checkoutSettings = app(\App\Settings\CheckoutSettings::class);
        $couponSettings = app(\App\Settings\CouponSettings::class);

        return view('admin.orders.edit', compact('order', 'checkoutSettings', 'couponSettings'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);
        
        $data = $request->validate([
            'status' => ['required', 'string'],
            'items' => ['array'],
            'items.*.id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'internal_note' => ['nullable', 'string'],
            
            'shipping.first_name' => ['nullable', 'string'],
            'shipping.last_name' => ['nullable', 'string'],
            'shipping.phone' => ['nullable', 'string'],
            'shipping.address_detail' => ['nullable', 'string'],
            
            'billing.first_name' => ['nullable', 'string'],
            'billing.last_name' => ['nullable', 'string'],
            'billing.phone' => ['nullable', 'string'],
            'billing.address_detail' => ['nullable', 'string'],
        ]);

        // 1. Update items manually (since they are customly edited)
        if (isset($data['items'])) {
            foreach ($data['items'] as $itemData) {
                $item = $order->items()->find($itemData['id']);
                if ($item) {
                    $itemTotal = $itemData['qty'] * $itemData['unit_price'];
                    $item->update([
                        'qty' => $itemData['qty'],
                        'unit_price' => $itemData['unit_price'],
                        'total' => $itemTotal,
                    ]);
                }
            }
        }

        // 2. Update status
        $orderService = app(\App\Ecommerce\Order\Contracts\OrderServiceInterface::class);
        $orderService->updateStatus($order, \App\Ecommerce\Order\Enums\OrderStatus::from($data['status']));

        // 3. Update internal note
        if (isset($data['internal_note'])) {
            \App\Models\OrderMeta::updateOrCreate(
                ['order_id' => $order->id, 'key' => 'internal_note'],
                ['value' => $data['internal_note']]
            );
        }

        // 4. Update Addresses
        if ($order->shippingAddress) {
            $order->shippingAddress->update($data['shipping'] ?? []);
        }
        if ($order->billingAddress) {
            $order->billingAddress->update($data['billing'] ?? []);
        }

        // 5. DELEGATE TO SERVICE TO RECALCULATE ENTIRE ORDER (Shipping, Tax, Coupons, Totals)
        $orderService->recalculateTotals($order->fresh());

        return redirect()->route('admin.orders.show', $order->id)->with('status', __('admin.messages.updated'));
    }
}
