<?php

namespace App\Http\Controllers\Api;

use App\Actions\Order\PlaceOrderAction;
use App\Contracts\Services\OrderServiceInterface;
use App\DTOs\Order\OrderDataDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreOrderRequest;
use App\Http\Resources\Api\OrderResource;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * @group Shop
 *
 * APIs for managing customer orders and checkout.
 */
class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PlaceOrderAction $placeOrderAction,
        protected OrderServiceInterface $orderService
    ) {}

    /**
     * Get user order history.
     *
     * List all orders associated with the authenticated user.
     */
    public function index(Request $request)
    {
        $orders = $this->orderService->paginateFiltered($request->get('per_page', 15));

        $meta = [
            'filters' => [
                'status' => \App\Enums\OrderStatus::cases(),
            ]
        ];

        return $this->success(OrderResource::collection($orders), __('admin.api.orders_retrieved'), 200, $meta);
    }

    /**
     * Place a new order.
     */
    public function store(StoreOrderRequest $request)
    {
        $dto = OrderDataDTO::fromRequest($request->validated());

        $order = $this->placeOrderAction->execute($dto);

        return $this->created(
            new OrderResource($order->load(['items', 'shippingAddress', 'billingAddress'])),
            __('admin.api.order_placed')
        );
    }

    /**
     * Get order details.
     */
    public function show($id)
    {
        $order = $this->orderService->getFullOrder($id);

        if (!$order) {
            return $this->notFound(__('admin.api.order_not_found'));
        }

        $this->authorize('view', $order);

        return $this->ok(new OrderResource($order));
    }

    /**
     * Update order details.
     */
    public function update(Request $request, $id)
    {
        $order = $this->orderService->find($id);

        if (!$order) {
            return $this->notFound(__('admin.api.order_not_found'));
        }

        $this->authorize('update', $order);

        $this->orderService->updateOrder($order, $request->only(['status', 'notes']));

        return $this->ok(new OrderResource($order->fresh(['items', 'shippingAddress', 'billingAddress'])));
    }

    /**
     * Cancel/Delete order.
     */
    public function destroy($id)
    {
        $order = $this->orderService->find($id);

        if (!$order) {
            return $this->notFound(__('admin.api.order_not_found'));
        }

        $this->authorize('delete', $order);

        $this->orderService->deleteOrder($order);

        return $this->noContent();
    }
}
