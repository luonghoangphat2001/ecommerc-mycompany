<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasInventoryStock
{
    protected function inventoryStockSum(string $foreignKey): int
    {
        return (int) DB::table('shop_product_inventory_stocks')
            ->where($foreignKey, $this->getKey())
            ->sum('stock_quantity');
    }
}
