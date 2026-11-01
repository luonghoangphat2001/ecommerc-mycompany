<?php

namespace App\Ecommerce\Shipping\Services;

use App\Models\Product;
use App\Models\ProductVariant;

class PackageCalculator
{
    public const DEFAULT_ITEM_WEIGHT_GRAMS = 500;
    public const DEFAULT_LENGTH_CM = 20;
    public const DEFAULT_WIDTH_CM = 15;
    public const DEFAULT_HEIGHT_CM = 10;

    /**
     * Calculate package weight and dimensions from cart/order items.
     *
     * @param array $items Array of items with 'product_id', 'qty'/'quantity', and optional 'variant_id'
     * @return array ['weight' => int (grams), 'length' => int, 'width' => int, 'height' => int, 'items_count' => int]
     */
    public function calculatePackage(array $items): array
    {
        $totalWeight = 0;
        $totalQuantity = 0;

        foreach ($items as $item) {
            $productId = $item['product_id'] ?? $item['id'] ?? null;
            $variantId = $item['variant_id'] ?? null;
            $qty = max(1, (int) ($item['quantity'] ?? $item['qty'] ?? 1));
            $totalQuantity += $qty;

            if (!$productId) {
                $totalWeight += self::DEFAULT_ITEM_WEIGHT_GRAMS * $qty;
                continue;
            }

            $product = Product::with(['comboComponents.product', 'comboComponents.variant'])->find($productId);
            if (!$product) {
                $totalWeight += self::DEFAULT_ITEM_WEIGHT_GRAMS * $qty;
                continue;
            }

            if ($product->isCombo()) {
                // Combo product: sum component weights (avoid double counting)
                $comboWeight = 0;
                $components = $product->comboComponents;
                if ($components && $components->isNotEmpty()) {
                    foreach ($components as $component) {
                        $compQty = max(1, (int) ($component->quantity ?? 1));
                        $compVariantWeight = $component->variant?->weight;
                        $compWeight = $compVariantWeight ?? $component->product?->weight ?? self::DEFAULT_ITEM_WEIGHT_GRAMS;
                        // Convert kg to grams if value is small (< 50)
                        if ($compWeight > 0 && $compWeight < 50) {
                            $compWeight = (int) ($compWeight * 1000);
                        }
                        $comboWeight += $compWeight * $compQty;
                    }
                }
                $totalWeight += ($comboWeight > 0 ? $comboWeight : self::DEFAULT_ITEM_WEIGHT_GRAMS) * $qty;
            } elseif ($product->isVariable() && $variantId) {
                $variant = ProductVariant::find($variantId);
                $varWeight = $variant?->weight;
                if ($varWeight !== null && $varWeight > 0) {
                    if ($varWeight < 50) {
                        $varWeight = (int) ($varWeight * 1000);
                    }
                    $totalWeight += $varWeight * $qty;
                } else {
                    $totalWeight += self::DEFAULT_ITEM_WEIGHT_GRAMS * $qty;
                }
            } else {
                $pWeight = $product->weight ?? null;
                if ($pWeight !== null && $pWeight > 0) {
                    if ($pWeight < 50) {
                        $pWeight = (int) ($pWeight * 1000);
                    }
                    $totalWeight += $pWeight * $qty;
                } else {
                    $totalWeight += self::DEFAULT_ITEM_WEIGHT_GRAMS * $qty;
                }
            }
        }

        // Pack dimension scaling roughly based on total quantity
        $packagesCount = max(1, (int) ceil($totalQuantity / 4));
        $height = self::DEFAULT_HEIGHT_CM * $packagesCount;

        return [
            'weight' => max(100, (int) round($totalWeight)), // Minimum 100 grams
            'length' => self::DEFAULT_LENGTH_CM,
            'width' => self::DEFAULT_WIDTH_CM,
            'height' => min(150, $height),
            'items_count' => $totalQuantity,
        ];
    }
}
