<?php

namespace App\Http\Controllers\Api;

use App\Ecommerce\Checkout\Actions\PlaceOrderAction;
use App\Ecommerce\Order\Contracts\OrderServiceInterface;
use App\Ecommerce\Order\DTOs\Order\OrderDataDTO;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreOrderRequest;
use App\Http\Resources\Api\OrderResource;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Ecommerce\Customer\Contracts\CustomerResolverServiceInterface;
use OpenApi\Attributes as OAT;
use App\Swagger\Attributes\ApiGet;
use App\Swagger\Attributes\ApiList;
use App\Swagger\Attributes\ApiPost;
use App\Swagger\Attributes\ApiUpdate;
use App\Swagger\Attributes\ApiDelete;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PlaceOrderAction $placeOrderAction,
        protected OrderServiceInterface $orderService,
        protected CustomerResolverServiceInterface $customerResolver
    ) {}

    #[ApiList(
        path: '/orders',
        summary: 'List of User Orders',
        tags: 'Storefront - Orders',
        responseData: '#/components/schemas/OrderResource'
    )]
    public function index(Request $request)
    {
        $userId = $this->customerResolver->resolveCustomerId($request);

        $orders = $this->orderService->paginateFiltered(
            $request->get('per_page', 15),
            $userId
        );

        $meta = [
            'filters' => [
                'status' => \App\Ecommerce\Order\Enums\OrderStatus::cases(),
            ]
        ];

        return $this->success(OrderResource::collection($orders), __('admin.api.orders_retrieved'), 200, $meta);
    }

    #[ApiPost(
        path: '/orders',
        summary: 'Create New Order',
        tags: 'Storefront - Orders',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ['email', 'first_name', 'last_name', 'phone', 'address_line_1', 'city', 'country_id', 'items', 'payment_method_id'],
                properties: [
                    new OAT\Property(property: 'email', type: 'string', format: 'email', example: 'customer@example.com'),
                    new OAT\Property(property: 'first_name', type: 'string', example: 'Nguyen'),
                    new OAT\Property(property: 'last_name', type: 'string', example: 'Van A'),
                    new OAT\Property(property: 'phone', type: 'string', example: '0123456789'),
                    new OAT\Property(property: 'address_line_1', type: 'string', example: '123 Le Loi'),
                    new OAT\Property(property: 'city', type: 'string', example: 'Ho Chi Minh'),
                    new OAT\Property(property: 'country_id', type: 'integer', example: 1),
                    new OAT\Property(
                        property: 'items',
                        type: 'array',
                        items: new OAT\Items(
                            properties: [
                                new OAT\Property(property: 'product_id', type: 'integer', example: 1),
                                new OAT\Property(property: 'qty', type: 'integer', example: 2)
                            ]
                        )
                    ),
                    new OAT\Property(property: 'payment_method_id', type: 'integer', example: 1),
                    new OAT\Property(property: 'shipping_method_id', type: 'integer', example: 1)
                ]
            )
        ),
        responseData: '#/components/schemas/OrderResource'
    )]
    public function store(StoreOrderRequest $request)
    {
        $dto = OrderDataDTO::fromRequest($request->validated());

        $dto->customerId = $this->customerResolver->resolveCustomerId($request, $dto->email);

        $order = $this->placeOrderAction->execute($dto);

        return $this->created(
            new OrderResource($order->load(['items', 'shippingAddress', 'billingAddress'])),
            __('admin.api.order_placed')
        );
    }

    #[ApiGet(
        path: '/orders/{order}',
        summary: 'Order Details',
        tags: 'Storefront - Orders',
        parameters: [
            new OAT\Parameter(name: 'order', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Order ID')
        ],
        responseData: '#/components/schemas/OrderResource'
    )]
    public function show($id)
    {
        $order = $this->orderService->getFullOrder($id);

        if (!$order) {
            return $this->notFound(__('admin.api.order_not_found'));
        }

        $this->authorize('view', $order);

        return $this->ok(new OrderResource($order));
    }

    #[ApiUpdate(
        path: '/orders/{order}',
        summary: 'Update Order Status/Notes',
        tags: 'Storefront - Orders',
        parameters: [
            new OAT\Parameter(name: 'order', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Order ID')
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(property: 'status', type: 'string', example: 'processing'),
                    new OAT\Property(property: 'notes', type: 'string', example: 'Giao nhanh nhé')
                ]
            )
        ),
        responseData: '#/components/schemas/OrderResource'
    )]
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

    #[ApiDelete(
        path: '/orders/{order}',
        summary: 'Delete Order (Cancel)',
        tags: 'Storefront - Orders',
        parameters: [
            new OAT\Parameter(name: 'order', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Order ID')
        ]
    )]
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
