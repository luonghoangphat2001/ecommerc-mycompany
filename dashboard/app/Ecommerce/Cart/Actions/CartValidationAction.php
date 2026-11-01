<?php

namespace App\Ecommerce\Cart\Actions;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Storage;

class CartValidationAction
{
    /**
     * Validate an array of raw cart items against database catalog and stock.
     *
     * @param array $items
     * @return array{items: array, notifications: array}
     */
    public function execute(array $items): array
    {
        $validatedItems = [];
        $notifications = [];

        foreach ($items as $rawItem) {
            $validatedItem = $this->validateItem($rawItem, $notifications);
            if ($validatedItem !== null) {
                $validatedItems[] = $validatedItem;
            }
        }

        return [
            'items' => $validatedItems,
            'notifications' => $notifications,
        ];
    }

    /**
     * Validate a single cart item.
     */
    private function validateItem(array $item, array &$notifications): ?array
    {
        $productId = $item['product_id'] ?? $item['id'] ?? null;
        $variantId = $item['variant_id'] ?? null;
        $qty = max(1, (int) ($item['quantity'] ?? $item['qty'] ?? 1));
        $clientPrice = (float) ($item['price'] ?? 0);

        $product = $this->findActiveProduct($productId);
        if (!$product) {
            $notifications[] = [
                'type' => 'item_removed',
                'message' => 'Sản phẩm không còn tồn tại hoặc đã bị ẩn.',
                'product_id' => $productId,
            ];
            return null;
        }

        $policy = app(\App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface::class);

        if ($product->isCombo() && !$policy->isComboEnabled()) {
            $notifications[] = [
                'type' => 'item_removed',
                'message' => "Sản phẩm combo {$product->name} hiện đang tạm ngừng hỗ trợ.",
                'product_id' => $productId,
            ];
            return null;
        }

        $variant = null;
        if ($product->isVariable()) {
            $variant = $this->resolveActiveVariant($product, $variantId, $notifications);
            if (!$variant) {
                return null;
            }
        }

        $itemName = $this->buildItemDisplayName($product, $variant);
        $effectivePrice = (float) ($variant ? $variant->effective_price : $product->effective_price);

        if ($policy->isInventoryTrackingEnabled()) {
            $availableStock = $this->resolveAvailableStock($product, $variant);
            $finalQty = $this->adjustQtyForStock($itemName, $qty, $availableStock, $productId, $variantId, $notifications);
            if ($finalQty <= 0) {
                return null;
            }
        } else {
            $finalQty = $qty;
        }

        $this->notifyPriceChangeIfDifferent($itemName, $clientPrice, $effectivePrice, $productId, $variantId, $notifications);

        return $this->formatValidatedItem($product, $variant, $itemName, $effectivePrice, $finalQty);
    }

    /**
     * Find active visible product with relations.
     */
    private function findActiveProduct(?int $productId): ?Product
    {
        if (!$productId) {
            return null;
        }

        return Product::with(['comboComponents.product', 'comboComponents.variant', 'variants'])
            ->where('is_visible', true)
            ->find($productId);
    }

    /**
     * Resolve and validate active variant for variable product.
     */
    private function resolveActiveVariant(Product $product, ?int $variantId, array &$notifications): ?ProductVariant
    {
        if (!$variantId) {
            $notifications[] = [
                'type' => 'item_removed',
                'message' => "Vui lòng chọn phiên bản cho sản phẩm {$product->name}.",
                'product_id' => $product->id,
            ];
            return null;
        }

        $variant = $product->variants->firstWhere('id', $variantId) ?: ProductVariant::find($variantId);
        if (!$variant || $variant->product_id != $product->id || !$variant->is_active) {
            $notifications[] = [
                'type' => 'item_removed',
                'message' => 'Phiên bản sản phẩm đã chọn không hợp lệ hoặc đã ngừng bán.',
                'product_id' => $product->id,
                'variant_id' => $variantId,
            ];
            return null;
        }

        return $variant;
    }

    /**
     * Build display name with variant attributes if present.
     */
    private function buildItemDisplayName(Product $product, ?ProductVariant $variant): string
    {
        if ($variant && !empty($variant->attributes) && is_array($variant->attributes)) {
            $variantDesc = implode(', ', $variant->attributes);
            return "{$product->name} ({$variantDesc})";
        }

        return $product->name;
    }

    /**
     * Calculate available stock according to product type.
     */
    private function resolveAvailableStock(Product $product, ?ProductVariant $variant): int
    {
        if ($variant) {
            return (int) $variant->available_stock;
        }

        if ($product->isCombo()) {
            return (int) $product->calculateComboStock();
        }

        return (int) $product->available_stock;
    }

    /**
     * Validate stock and adjust quantity if necessary.
     */
    private function adjustQtyForStock(string $itemName, int $requestedQty, int $availableStock, ?int $productId, ?int $variantId, array &$notifications): int
    {
        if ($availableStock >= $requestedQty) {
            return $requestedQty;
        }

        if ($availableStock <= 0) {
            $notifications[] = [
                'type' => 'out_of_stock',
                'message' => "Sản phẩm {$itemName} đã hết hàng.",
                'product_id' => $productId,
                'variant_id' => $variantId,
            ];
            return 0;
        }

        $notifications[] = [
            'type' => 'stock_adjusted',
            'message' => "Số lượng của {$itemName} được điều chỉnh về {$availableStock} do giới hạn tồn kho.",
            'product_id' => $productId,
            'variant_id' => $variantId,
        ];

        return $availableStock;
    }

    /**
     * Notify if client-submitted price differs from authoritative database price.
     */
    private function notifyPriceChangeIfDifferent(string $itemName, float $clientPrice, float $effectivePrice, ?int $productId, ?int $variantId, array &$notifications): void
    {
        if ($clientPrice > 0 && round($clientPrice, 2) != round($effectivePrice, 2)) {
            $notifications[] = [
                'type' => 'price_change',
                'message' => "Giá của {$itemName} đã thay đổi.",
                'product_id' => $productId,
                'variant_id' => $variantId,
            ];
        }
    }

    /**
     * Format validated item structure for output.
     */
    private function formatValidatedItem(Product $product, ?ProductVariant $variant, string $itemName, float $price, int $quantity): array
    {
        return [
            'id' => $product->id,
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'name' => $itemName,
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => $price * $quantity,
            'image' => $product->featuredImage ? Storage::url($product->featuredImage->path) : null,
            'attributes' => $variant?->attributes ?? null,
        ];
    }
}
