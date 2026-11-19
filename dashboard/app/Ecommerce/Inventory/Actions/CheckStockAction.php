<?php

namespace App\Ecommerce\Inventory\Actions;

use App\Ecommerce\Product\Contracts\ProductRepositoryInterface;

class CheckStockAction
{
    public function __construct(protected ProductRepositoryInterface $productRepository) {}

    /**
     * Check stock availability for a given product across inventories.
     */
    public function execute(string $sku, int $quantity): bool
    {
        $product = $this->productRepository->findBySku($sku);

        if (! $product) {
            return false;
        }

        // Prefer real stock from inventories; fall back to product qty when inventory rows are absent.
        if ($product->available_stock < $quantity) {
            return false;
        }

        return true;
    }
}
