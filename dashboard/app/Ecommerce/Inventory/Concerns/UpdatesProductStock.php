<?php

namespace App\Ecommerce\Inventory\Concerns;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

trait UpdatesProductStock
{
    protected function updateProductStockTotal(int $productId, int $quantityDelta): void
    {
        $product = Product::find($productId);

        if (! $product) {
            return;
        }

        $updated = DB::table('shop_products')
            ->where('id', $productId)
            ->where('version', $product->version)
            ->update([
                'total_stock' => $product->total_stock + $quantityDelta,
                'version' => $product->version + 1,
            ]);

        if ($updated === 0) {
            throw ValidationException::withMessages([
                'order' => [__('messages.concurrency_collision')],
            ]);
        }
    }
}
