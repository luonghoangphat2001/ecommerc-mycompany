<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Ecommerce\Cart\Contracts\CartServiceInterface;
use App\Ecommerce\Cart\Contracts\CartCalculationServiceInterface;
use App\Ecommerce\Shipping\Contracts\ShippingServiceInterface;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CartServiceInterface $cartService,
        protected CartCalculationServiceInterface $calculationService,
        protected ShippingServiceInterface $shippingService
    ) {}

    /**
     * Get cart items with validation (POST vì FE gửi items từ localStorage)
     */
    public function index(Request $request): JsonResponse
    {
        $items = $request->input('items', []);
        $validated = $this->cartService->syncAndValidate($items);
        
        // Calculate totals using CartCalculationService
        $summary = $this->calculationService->calculate(
            $validated['items'] ?? [],
            $request->input('country', 'VN'),
            $request->input('state')
        );

        return $this->success([
            'items' => $validated['items'] ?? [],
            'summary' => $summary,
            'notifications' => $validated['notifications'] ?? [],
        ]);
    }

    /**
     * Sync and validate cart items
     */
    public function sync(Request $request): JsonResponse
    {
        $items = $request->input('items', []);
        $validated = $this->cartService->syncAndValidate($items);
        
        // Calculate with totals
        $summary = $this->calculationService->calculate(
            $validated['items'] ?? [],
            $request->input('country', 'VN'),
            $request->input('state')
        );

        return $this->success([
            'items' => $validated['items'] ?? [],
            'summary' => $summary,
            'notifications' => $validated['notifications'] ?? [],
        ]);
    }

    /**
     * Add item to cart (validation)
     */
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

    /**
     * Update cart item (validation)
     */
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

    /**
     * Remove item from cart
     */
    public function removeItem($itemId): JsonResponse
    {
        return $this->success(['removed' => true], 'Đã xóa sản phẩm khỏi giỏ hàng');
    }

    /**
     * Clear cart
     */
    public function clear(): JsonResponse
    {
        return $this->success(['cleared' => true], 'Đã xóa toàn bộ giỏ hàng');
    }

    /**
     * Get available shipping methods for cart
     */
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
