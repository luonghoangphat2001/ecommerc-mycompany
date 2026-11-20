<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasInventoryStock
{
    protected function inventoryStockTotalIfPresent(string $foreignKey): ?int
    {
        $stock = DB::table('shop_product_inventory_stocks')
            ->where($foreignKey, $this->getKey())
            ->selectRaw('COUNT(*) as row_count, COALESCE(SUM(stock_quantity), 0) as total')
            ->first();

        return (int) $stock->row_count > 0 ? (int) $stock->total : null;
    }
}
