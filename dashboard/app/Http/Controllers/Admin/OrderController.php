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
            'status' => ['label' => __('admin.order.status'), 'rules' => ['required', 'string', 'max:30']],
            'currency' => ['label' => __('admin.order.currency'), 'rules' => ['required', 'string', 'max:10']],
            'total' => ['label' => __('admin.order.total_price'), 'type' => 'number', 'rules' => ['required', 'numeric', 'min:0']],
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
        $loyaltySettings = app(\App\Settings\LoyaltySettings::class);
        $orderService = app(\App\Ecommerce\Order\Contracts\OrderServiceInterface::class);

        return view('admin.orders.show', compact('order', 'checkoutSettings', 'couponSettings', 'loyaltySettings', 'orderService'));
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,new,processing,delivering,completed,cancelled,refunded'],
        ]);

        $order = Order::findOrFail($id);
        
        $orderService = app(\App\Ecommerce\Order\Contracts\OrderServiceInterface::class);
        $orderService->updateStatus($order, \App\Ecommerce\Order\Enums\OrderStatus::from($data['status']));

        return redirect()->route('admin.orders.show', $order->id)->with('status', __('admin.order.status_updated'));
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

        return redirect()->route('admin.orders.show', $order->id)->with('status', __('admin.order.payment_added'));
    }

    public function storeRefund(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:0'],
            'reason' => ['nullable', 'string'],
        ]);

        $order = Order::findOrFail($id);
        OrderRefund::create($data + ['order_id' => $order->id]);

        return redirect()->route('admin.orders.show', $order->id)->with('status', __('admin.order.refund_created'));
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
        $loyaltySettings = app(\App\Settings\LoyaltySettings::class);
        $orderService = app(\App\Ecommerce\Order\Contracts\OrderServiceInterface::class);
        $shippingMethods = \App\Models\ShippingMethod::where('is_enabled', true)->get();
        return view('admin.orders.edit', compact('order', 'checkoutSettings', 'couponSettings', 'loyaltySettings', 'orderService', 'shippingMethods'));
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
            'shipping_method_id' => ['nullable', 'integer', 'exists:shop_shipping_methods,id'],
            'coupon_code' => ['nullable', 'string', 'exists:shop_coupons,code'],
            'redeemed_points' => ['nullable', 'integer', 'min:0'],
            'manual_tax_amount' => ['nullable', 'integer', 'min:0'],
            
            // Refund Flow Fields
            'refund_type' => ['nullable', 'string', 'in:full,partial'],
            'refund_amount' => ['nullable', 'integer', 'min:0'],
            'refund_reason' => ['nullable', 'string'],

            'shipping.first_name' => ['nullable', 'string'],
            'shipping.last_name' => ['nullable', 'string'],
            'shipping.phone' => ['nullable', 'string'],
            'shipping.email' => ['nullable', 'email'],
            'shipping.address_detail' => ['nullable', 'string'],
            'shipping.address_line_2' => ['nullable', 'string'],
            'shipping.city_id' => ['nullable', 'string'],
            'shipping.state_id' => ['nullable', 'string'],
            'shipping.country_code' => ['nullable', 'string'],
            'shipping.postal_code' => ['nullable', 'string'],
            'billing.first_name' => ['nullable', 'string'],
            'billing.last_name' => ['nullable', 'string'],
            'billing.phone' => ['nullable', 'string'],
            'billing.email' => ['nullable', 'email'],
            'billing.address_detail' => ['nullable', 'string'],
            'billing.address_line_2' => ['nullable', 'string'],
            'billing.city_id' => ['nullable', 'string'],
            'billing.state_id' => ['nullable', 'string'],
            'billing.country_code' => ['nullable', 'string'],
            'billing.postal_code' => ['nullable', 'string'],
        ], [
            'coupon_code.exists' => __('admin.order.coupon_not_found'),
        ]);

        // 1. Delegate entirely to OrderService
        try {
            $orderService = app(\App\Ecommerce\Order\Contracts\OrderServiceInterface::class);
            $orderService->updateOrder($order, $data);
            return redirect()->route('admin.orders.show', $order->id)->with('status', __('admin.messages.updated'));
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
