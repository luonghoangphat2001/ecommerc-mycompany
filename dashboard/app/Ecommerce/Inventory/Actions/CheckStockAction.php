<?php

namespace App\Ecommerce\Inventory\Actions;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CheckStockAction
{
    /**
     * Check stock availability for a given product across inventories.
     *
     * @param string $sku
     * @param int $quantity
     * @return bool
     */
    public function execute(string $sku, int $quantity): bool
    {
        $product = Product::where('sku', $sku)->first();

        if (!$product) {
            return false;
        }

        // Total overall available volume
        if ($product->total_stock < $quantity) {
            return false;
        }

        return true;
    }
}
