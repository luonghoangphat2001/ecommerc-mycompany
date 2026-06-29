<?php

namespace App\Ecommerce\Cart\Actions;

use App\Models\Product;

class CartValidationAction
{
    public function execute(array $items): array
    {
        $validatedItems = [];
        $notifications = [];
        $subtotal = 0;

        foreach ($items as $item) {
            $productId = $item['id'] ?? $item['product_id'] ?? null;
            $qty = $item['quantity'] ?? 1;
            $clientPrice = $item['price'] ?? 0;

            $product = Product::find($productId);

            if (!$product || !$product->is_visible) {
                $notifications[] = [
                    'type' => 'item_removed',
                    'message' => "Sản phẩm không còn tồn tại hoặc đã bị ẩn.",
                    'product_id' => $productId
                ];
                continue;
            }

            // Validate stock
            $finalQty = $qty;
            if ($product->qty < $qty) {
                $finalQty = $product->qty;
                if ($finalQty <= 0) {
                    $notifications[] = [
                        'type' => 'out_of_stock',
                        'message' => "Sản phẩm {$product->name} đã hết hàng.",
                        'product_id' => $productId
                    ];
                    continue;
                } else {
                    $notifications[] = [
                        'type' => 'stock_adjusted',
                        'message' => "Số lượng của {$product->name} được điều chỉnh về {$finalQty} do giới hạn tồn kho.",
                        'product_id' => $productId
                    ];
                }
            }

            // Validate price
            if (round($clientPrice, 2) != round($product->price, 2)) {
                $notifications[] = [
                    'type' => 'price_change',
                    'message' => "Giá của {$product->name} đã thay đổi.",
                    'product_id' => $productId
                ];
            }

            $itemSubtotal = $product->price * $finalQty;
            $subtotal += $itemSubtotal;

            $validatedItems[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float)$product->price,
                'quantity' => $finalQty,
                'subtotal' => (float)$itemSubtotal,
                'image' => $product->featuredImage ? \Illuminate\Support\Facades\Storage::url($product->featuredImage->path) : null,
            ];
        }

        // Return validated items and notifications
        // Calculations (subtotal, shipping, tax, total) are handled by CartCalculationService
        return [
            'items' => $validatedItems,
            'notifications' => $notifications,
        ];
    }
}
