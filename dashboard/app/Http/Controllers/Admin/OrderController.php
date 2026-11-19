<?php

namespace App\Http\Controllers\Admin;

use App\Ecommerce\Order\Actions\RefundOrderAction;
use App\Ecommerce\Order\Contracts\OrderServiceInterface;
use App\Ecommerce\Order\Enums\OrderStatus;
use App\Ecommerce\Payment\Contracts\PaymentServiceInterface;
use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Requests\Admin\OrderStatusRequest;
use App\Http\Requests\Admin\StorePaymentRequest;
use App\Http\Requests\Admin\StoreRefundRequest;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Settings\CheckoutSettings;
use App\Settings\CouponSettings;
use App\Settings\LoyaltySettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends BaseCrudController
{
    public function __construct(
        private readonly OrderServiceInterface $orderService,
        private readonly RefundOrderAction $refundOrderAction,
        private readonly PaymentServiceInterface $paymentService,
        private readonly CheckoutSettings $checkoutSettings,
        private readonly CouponSettings $couponSettings,
        private readonly LoyaltySettings $loyaltySettings
    ) {
        parent::__construct();
    }

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

        $items = $this->paginateIndex($query);

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
        $order = $this->findOrder($id, [
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

        $checkoutSettings = $this->checkoutSettings;
        $couponSettings = $this->couponSettings;
        $loyaltySettings = $this->loyaltySettings;
        $orderService = $this->orderService;

        return view('admin.orders.show', compact('order', 'checkoutSettings', 'couponSettings', 'loyaltySettings', 'orderService'));
    }

    public function updateStatus(OrderStatusRequest $request, int $id): RedirectResponse
    {
        $data = $request->validated();

        $order = $this->findOrder($id);

        $statusEnum = $data['status'] instanceof OrderStatus ? $data['status'] : OrderStatus::from($data['status']);
        $this->orderService->updateStatus($order, $statusEnum);

        return redirect()->route('admin.orders.show', $order->id)->with('status', __('admin.order.status_updated'));
    }

    public function storePayment(StorePaymentRequest $request, int $id): RedirectResponse
    {
        $data = $request->validated();

        $order = $this->findOrder($id);

        $this->paymentService->record($order, $data);

        return redirect()->route('admin.orders.show', $order->id)->with('status', __('admin.order.payment_added'));
    }

    public function storeRefund(StoreRefundRequest $request, int $id): RedirectResponse
    {
        $data = $request->validated();

        $order = $this->findOrder($id);

        try {
            $this->refundOrderAction->execute(
                $order,
                (int) $data['amount'],
                (string) ($data['reason'] ?? ''),
                [
                    'disposition' => $data['disposition'] ?? 'none',
                    'items' => $data['items'] ?? [],
                ],
                $data['idempotency_key'] ?? null,
            );
        } catch (\Throwable $e) {
            return $this->redirectBackWithError($e);
        }

        return redirect()->route('admin.orders.show', $order->id)->with('status', __('admin.order.refund_created'));
    }

    public function edit(int $id): View
    {
        $order = $this->findOrder($id, [
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

        $checkoutSettings = $this->checkoutSettings;
        $couponSettings = $this->couponSettings;
        $loyaltySettings = $this->loyaltySettings;
        $orderService = $this->orderService;
        $shippingMethods = ShippingMethod::where('is_enabled', true)->get();

        return view('admin.orders.edit', compact('order', 'checkoutSettings', 'couponSettings', 'loyaltySettings', 'orderService', 'shippingMethods'));
    }

    public function update(UpdateOrderRequest $request, int $id): RedirectResponse
    {
        $order = $this->findOrder($id);
        $data = $request->validated();

        // 1. Delegate entirely to OrderService
        try {
            $this->orderService->updateOrder($order, $data);

            return redirect()->route('admin.orders.show', $order->id)->with('status', __('admin.messages.updated'));
        } catch (\Exception $e) {
            return $this->redirectBackWithError($e);
        }
    }

    private function findOrder(int $id, array $relations = []): Order
    {
        return Order::query()
            ->when($relations !== [], fn ($query) => $query->with($relations))
            ->findOrFail($id);
    }
}
