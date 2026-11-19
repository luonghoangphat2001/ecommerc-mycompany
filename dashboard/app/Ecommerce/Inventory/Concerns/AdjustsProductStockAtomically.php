<?php

namespace App\Ecommerce\Inventory\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

trait AdjustsProductStockAtomically
{
    use AdjustsInventoryStock;
    use UpdatesProductStock;

    protected function adjustProductStockAtomically(
        int $productId,
        int $warehouseId,
        int $quantityDelta,
        bool $requireInventoryRow = false,
        ?int $variantId = null
    ): bool {
        return DB::transaction(function () use ($productId, $warehouseId, $quantityDelta, $requireInventoryRow, $variantId) {
            $affected = $this->adjustInventoryStock($productId, $warehouseId, $quantityDelta, $variantId);

            if ($requireInventoryRow && $affected === 0) {
                throw ValidationException::withMessages([
                    'order' => [__('messages.insufficient_stock')],
                ]);
            }

            $this->updateProductStockTotal($productId, $quantityDelta);

            return true;
        }, 3);
    }
}
