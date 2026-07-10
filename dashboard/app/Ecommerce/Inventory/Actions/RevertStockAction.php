<?php

namespace App\Ecommerce\Inventory\Actions;

use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Exception;
use Illuminate\Validation\ValidationException;

class RevertStockAction
{
    /**
     * Restore cancelled items back into their source facilities securely.
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
            // 1. Atomic increment on inventory stock
            DB::table('shop_product_inventory_stocks')
                ->where('shop_product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->increment('stock_quantity', $quantity);

            // 2. Optimistic Lock update on denormalized overall product stocks
            $product = Product::find($productId);

            if ($product) {
                $updated = DB::table('shop_products')
                    ->where('id', $productId)
                    ->where('version', $product->version)
                    ->update([
                        'total_stock' => $product->total_stock + $quantity,
                        'version' => $product->version + 1,
                    ]);

                if ($updated === 0) {
                    throw ValidationException::withMessages([
                        'order' => [__('messages.concurrency_collision')],
                    ]);
                }
            }

            return true;
        }, 3);
    }
}
