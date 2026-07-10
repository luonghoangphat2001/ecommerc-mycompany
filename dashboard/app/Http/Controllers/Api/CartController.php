<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Ecommerce\Cart\Contracts\CartServiceInterface;
use App\Ecommerce\Cart\Contracts\CartCalculationServiceInterface;
use App\Ecommerce\Shipping\Contracts\ShippingServiceInterface;
use App\Models\Product;
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
        protected ShippingServiceInterface $shippingService
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
    public function index(Request $request): JsonResponse
    {
        $items = $request->input('items', []);
        $validated = $this->cartService->syncAndValidate($items);
        
        $summary = $this->calculationService->calculate(
            $validated['items'] ?? [],
            $request->input('country', 'VN'),
            $request->input('state'),
            $request->input('shipping_method'),
            $request->input('coupon_code')
        );

        return $this->success([
            'items' => $validated['items'] ?? [],
            'summary' => $summary,
            'notifications' => $validated['notifications'] ?? [],
        ]);
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
    public function sync(Request $request): JsonResponse
    {
        $items = $request->input('items', []);
        $validated = $this->cartService->syncAndValidate($items);
        
        $summary = $this->calculationService->calculate(
            $validated['items'] ?? [],
            $request->input('country', 'VN'),
            $request->input('state'),
            $request->input('shipping_method'),
            $request->input('coupon_code')
        );

        return $this->success([
            'items' => $validated['items'] ?? [],
            'summary' => $summary,
            'notifications' => $validated['notifications'] ?? [],
        ]);
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
    public function addItem(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:shop_products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        
        if ($product->qty < $request->quantity) {
            return $this->error('Sản phẩm không đủ số lượng trong kho', 422);
        }

        return $this->success([
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => $request->quantity,
            'subtotal' => $product->price * $request->quantity,
            'available' => true,
        ], 'Thêm vào giỏ hàng thành công');
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
    public function updateItem(Request $request, $itemId): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $product = Product::find($itemId);
        
        if (!$product) {
            return $this->error('Sản phẩm không tồn tại', 404);
        }

        if ($request->quantity > 0 && $product->qty < $request->quantity) {
            return $this->error('Sản phẩm không đủ số lượng trong kho', 422);
        }

        return $this->success([
            'product_id' => $product->id,
            'quantity' => $request->quantity,
            'available' => $request->quantity > 0 ? $product->qty >= $request->quantity : true,
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
        return $this->success(['removed' => true], 'Đã xóa sản phẩm khỏi giỏ hàng');
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
        return $this->success(['cleared' => true], 'Đã xóa toàn bộ giỏ hàng');
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
        $items = $request->input('items', []);
        $subtotal = collect($items)->sum(fn($i) => ($i['price'] ?? 0) * ($i['quantity'] ?? 0));
        
        $methods = $this->shippingService->getAvailableMethods(
            $request->input('country', 'VN'),
            $request->input('state'),
            null,
            null,
            $subtotal
        );

        return $this->success($methods);
    }
}
