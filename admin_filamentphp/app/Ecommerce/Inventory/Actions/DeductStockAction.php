<?php

namespace App\Ecommerce\Inventory\Actions;

use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Exception;

class DeductStockAction
{
    /**
     * Execute atomic stock reductions avoiding concurrency races.
     *
     * @param int $productId
     * @param int $warehouseId
     * @param int $quantity
     * @return bool
     * @throws Exception
     */
    public function execute(int $productId, int $warehouseId, int $quantity): bool
    {
        return DB::transaction(function () use ($productId, $warehouseId, $quantity) {
            // 1. Atomic decrement on inventory stock
            $affected = DB::table('shop_product_inventory_stocks')
                ->where('shop_product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->where('stock_quantity', '>=', $quantity)
                ->decrement('stock_quantity', $quantity);

            if ($affected === 0) {
                throw new Exception(__('messages.insufficient_stock'));
            }

            // 2. Optimistic Lock update on overall product denormalized stocks
            $product = Product::find($productId);
            
            if ($product) {
                $updated = DB::table('shop_products')
                    ->where('id', $productId)
                    ->where('version', $product->version)
                    ->update([
                        'total_stock' => $product->total_stock - $quantity,
                        'version' => $product->version + 1,
                    ]);

                if ($updated === 0) {
                    throw new Exception(__('messages.concurrency_collision'));
                }
            }

            return true;
        }, 3); // Retry deadlocks 3 times automatically
    }
}
