<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Ecommerce\Cart\Contracts\CartServiceInterface;
use App\Ecommerce\Cart\Contracts\CartCalculationServiceInterface;
use App\Ecommerce\Shipping\Contracts\ShippingServiceInterface;
use App\Http\Requests\API\CartRequest;
use App\Http\Resources\Api\CartResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;
use App\Swagger\Attributes\ApiGet;
use App\Swagger\Attributes\ApiList;
use App\Swagger\Attributes\ApiPost;
use App\Swagger\Attributes\ApiUpdate;
use App\Swagger\Attributes\ApiDelete;

class CartController extends BaseApiController
{

    public function __construct(
        protected CartServiceInterface $cartService,
        protected CartCalculationServiceInterface $calculationService,
        protected ShippingServiceInterface $shippingService,
        protected \App\Ecommerce\Product\Contracts\ProductRepositoryInterface $productRepository
    ) {}

    #[ApiPost(
        path: '/cart',
        summary: 'Get Cart Information',
        tags: 'Storefront - Cart',
        requiresAuth: false,
        requestBody: new OAT\RequestBody(
            required: false,
            content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(property: 'items', type: 'array', items: new OAT\Items(type: 'object')),
                    new OAT\Property(property: 'country', type: 'string', example: 'VN'),
                    new OAT\Property(property: 'state', type: 'string', nullable: true)
                ]
            )
        ),
        responseData: [
            new OAT\Property(property: 'items', type: 'array', items: new OAT\Items(type: 'object')),
            new OAT\Property(property: 'summary', type: 'object', properties: [
                new OAT\Property(property: 'subtotal', type: 'number', format: 'float', example: 1000000),
                new OAT\Property(property: 'tax_amount', type: 'number', format: 'float', example: 100000),
                new OAT\Property(property: 'shipping_cost', type: 'number', format: 'float', example: 30000),
                new OAT\Property(property: 'total', type: 'number', format: 'float', example: 1130000),
            ]),
            new OAT\Property(property: 'notifications', type: 'array', items: new OAT\Items(type: 'string'))
        ]
    )]
    public function index(CartRequest $request): JsonResponse
    {
        $items = $this->extractCartItems($request);
        $validated = $this->cartService->syncAndValidate($items);
        
        $summary = $this->calculationService->calculate(
            $validated['items'] ?? [],
            $request->input('country', 'VN'),
            $request->input('state'),
            $request->input('shipping_method'),
            $request->input('coupon_code')
        );

        return $this->success(new CartResource([
            'items' => $validated['items'] ?? [],
            'summary' => $summary,
            'notifications' => $validated['notifications'] ?? [],
        ]));
    }

    protected function extractCartItems(Request $request): array
    {
        $items = $request->input('items');
        if (is_array($items)) {
            return $items;
        }

        $all = $request->all();
        if (is_array($all) && isset($all[0]) && is_array($all[0])) {
            return $all;
        }

        return [];
    }

    public function suggestions(Request $request): JsonResponse
    {
        $items = $this->extractCartItems($request);
        $excludeIds = collect($items)
            ->map(fn ($item) => $item['product_id'] ?? $item['id'] ?? null)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $limit = (int) $request->input('limit', 4);

        $products = \App\Models\Product::with(['categories', 'brand', 'featuredImage', 'inventories'])
            ->where('is_visible', true)
            ->when(!empty($excludeIds), fn ($q) => $q->whereNotIn('id', $excludeIds))
            ->orderByDesc('featured')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        return $this->ok(\App\Http\Resources\Api\ProductResource::collection($products));
    }

    #[ApiPost(
        path: '/cart/sync',
        summary: 'Sync Cart',
        tags: 'Storefront - Cart',
        requiresAuth: false,
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(property: 'items', type: 'array', items: new OAT\Items(type: 'object')),
                    new OAT\Property(property: 'country', type: 'string', example: 'VN'),
                    new OAT\Property(property: 'state', type: 'string', nullable: true)
                ]
            )
        ),
        responseData: [
            new OAT\Property(property: 'items', type: 'array', items: new OAT\Items(type: 'object')),
            new OAT\Property(property: 'summary', type: 'object', properties: [
                new OAT\Property(property: 'subtotal', type: 'number', format: 'float', example: 1000000),
                new OAT\Property(property: 'tax_amount', type: 'number', format: 'float', example: 100000),
                new OAT\Property(property: 'shipping_cost', type: 'number', format: 'float', example: 30000),
                new OAT\Property(property: 'total', type: 'number', format: 'float', example: 1130000),
            ]),
            new OAT\Property(property: 'notifications', type: 'array', items: new OAT\Items(type: 'string'))
        ]
    )]
    public function sync(CartRequest $request): JsonResponse
    {
        return $this->index($request);
    }

    #[ApiPost(
        path: '/cart/items',
        summary: 'Add Item to Cart',
        tags: 'Storefront - Cart',
        requiresAuth: false,
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ['product_id', 'quantity'],
                properties: [
                    new OAT\Property(property: 'product_id', type: 'integer', example: 1),
                    new OAT\Property(property: 'quantity', type: 'integer', example: 2)
                ]
            )
        ),
        responseData: [
            new OAT\Property(property: 'product_id', type: 'integer', example: 1),
            new OAT\Property(property: 'name', type: 'string', example: 'Sản phẩm 1'),
            new OAT\Property(property: 'price', type: 'number', format: 'float', example: 500000),
            new OAT\Property(property: 'quantity', type: 'integer', example: 2),
            new OAT\Property(property: 'subtotal', type: 'number', format: 'float', example: 1000000),
            new OAT\Property(property: 'available', type: 'boolean', example: true),
        ]
    )]
    public function addItem(CartRequest $request): JsonResponse
    {
        $policy = app(\App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface::class);

        if (!$policy->isEcommerceEnabled()) {
            return $this->error('Cửa hàng hiện đang tạm dừng hoạt động.', 503);
        }

        if ($policy->isCatalogMode()) {
            return $this->error('Hệ thống đang ở chế độ xem danh mục (Catalog Mode), không hỗ trợ thêm vào giỏ hàng.', 422);
        }

        $validated = $request->validated();
        $product = $this->productRepository->find((int)$validated['product_id']);
        
        if (!$product) {
            return $this->error(__('messages.product_not_found') ?? 'Product not found', 404);
        }

        if ($product->isCombo() && !$policy->isComboEnabled()) {
            return $this->error('Sản phẩm combo hiện đang tạm ngừng hỗ trợ.', 422);
        }

        if ($policy->isInventoryTrackingEnabled() && $product->qty < $validated['quantity']) {
            return $this->error(__('messages.insufficient_stock') ?? 'Insufficient product stock', 422);
        }

        return $this->success([
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => $validated['quantity'],
            'subtotal' => $product->price * $validated['quantity'],
            'available' => true,
        ], __('messages.cart_item_added') ?? 'Product added to cart');
    }

    #[ApiUpdate(
        path: '/cart/items/{itemId}',
        summary: 'Update Item Quantity',
        tags: 'Storefront - Cart',
        requiresAuth: false,
        parameters: [
            new OAT\Parameter(name: 'itemId', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Product ID')
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ['quantity'],
                properties: [
                    new OAT\Property(property: 'quantity', type: 'integer', example: 3)
                ]
            )
        ),
        responseData: [
            new OAT\Property(property: 'product_id', type: 'integer', example: 1),
            new OAT\Property(property: 'quantity', type: 'integer', example: 3),
            new OAT\Property(property: 'available', type: 'boolean', example: true),
        ]
    )]
    public function updateItem(CartRequest $request, $itemId): JsonResponse
    {
        $policy = app(\App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface::class);

        if (!$policy->isEcommerceEnabled()) {
            return $this->error('Cửa hàng hiện đang tạm dừng hoạt động.', 503);
        }

        if ($policy->isCatalogMode()) {
            return $this->error('Hệ thống đang ở chế độ xem danh mục (Catalog Mode), không hỗ trợ cập nhật giỏ hàng.', 422);
        }

        $validated = $request->validated();
        $product = $this->productRepository->find((int)$itemId);
        
        if (!$product) {
            return $this->error(__('messages.product_not_found') ?? 'Product not found', 404);
        }

        $quantity = (int) $validated['quantity'];
        if ($policy->isInventoryTrackingEnabled() && $quantity > 0 && $product->qty < $quantity) {
            return $this->error(__('messages.insufficient_stock') ?? 'Insufficient product stock', 422);
        }

        return $this->success([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'available' => $quantity > 0 ? (!$policy->isInventoryTrackingEnabled() || $product->qty >= $quantity) : true,
        ]);
    }

    #[ApiDelete(
        path: '/cart/items/{itemId}',
        summary: 'Remove Item from Cart',
        tags: 'Storefront - Cart',
        requiresAuth: false,
        parameters: [
            new OAT\Parameter(name: 'itemId', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Product ID')
        ],
        responseData: [
            new OAT\Property(property: 'removed', type: 'boolean', example: true)
        ]
    )]
    public function removeItem($itemId): JsonResponse
    {
        return $this->success(['removed' => true], __('messages.cart_item_removed') ?? 'Cart item removed');
    }

    #[ApiDelete(
        path: '/cart',
        summary: 'Clear Cart',
        tags: 'Storefront - Cart',
        requiresAuth: false,
        responseData: [
            new OAT\Property(property: 'cleared', type: 'boolean', example: true)
        ]
    )]
    public function clear(): JsonResponse
    {
        return $this->success(['cleared' => true], __('messages.cart_cleared') ?? 'Cart cleared');
    }

    #[ApiPost(
        path: '/cart/shipping-methods',
        summary: 'Get Shipping Methods',
        tags: 'Storefront - Cart',
        requiresAuth: false,
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(property: 'items', type: 'array', items: new OAT\Items(type: 'object')),
                    new OAT\Property(property: 'country', type: 'string', example: 'VN'),
                    new OAT\Property(property: 'state', type: 'string', nullable: true)
                ]
            )
        ),
        responseData: [
            new OAT\Property(property: 'methods', type: 'array', items: new OAT\Items(
                properties: [
                    new OAT\Property(property: 'id', type: 'integer', example: 1),
                    new OAT\Property(property: 'name', type: 'string', example: 'Giao hàng tiêu chuẩn'),
                    new OAT\Property(property: 'cost', type: 'number', format: 'float', example: 30000),
                ]
            ))
        ]
    )]
    public function shippingMethods(Request $request): JsonResponse
    {
        $policy = app(\App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface::class);
        if (!$policy->isShippingEnabled()) {
            return $this->success([]);
        }

        $items = $this->extractCartItems($request);
        $subtotal = collect($items)->sum(fn($i) => ($i['price'] ?? 0) * ($i['quantity'] ?? 0));
        $package = app(\App\Ecommerce\Shipping\Services\PackageCalculator::class)->calculatePackage($items);

        $methods = $this->shippingService->getAvailableMethods(
            $request->input('country', 'VN'),
            $request->input('state'),
            $request->input('postal_code') ?? $request->input('zip'),
            $request->input('ward') ?? $request->input('region'),
            $subtotal,
            $package
        );

        return $this->success($methods);
    }
}
