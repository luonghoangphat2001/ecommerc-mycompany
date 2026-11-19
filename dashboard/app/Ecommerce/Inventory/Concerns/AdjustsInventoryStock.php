<?php

namespace App\Ecommerce\Inventory\Concerns;

use Illuminate\Support\Facades\DB;

trait AdjustsInventoryStock
{
    protected function adjustInventoryStock(int $productId, int $warehouseId, int $quantityDelta, ?int $variantId = null): int
    {
        $query = DB::table('shop_product_inventory_stocks')
            ->where('shop_product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->when($variantId === null, fn ($query) => $query->whereNull('variant_id'))
            ->when($variantId !== null, fn ($query) => $query->where('variant_id', $variantId));

        if ($quantityDelta < 0) {
            $query->where('stock_quantity', '>=', abs($quantityDelta));
        }

        return $query->update([
            'stock_quantity' => DB::raw('stock_quantity + ' . $quantityDelta),
            'updated_at' => now(),
        ]);
    }
}
