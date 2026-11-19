<?php

namespace App\Ecommerce\Inventory\Actions;

use App\Ecommerce\Inventory\Concerns\AdjustsProductStockAtomically;
use Exception;

class DeductStockAction
{
    use AdjustsProductStockAtomically;

    /**
     * Execute atomic stock reductions avoiding concurrency races.
     *
     * @throws Exception
     */
    public function execute(int $productId, int $warehouseId, int $quantity, ?int $variantId = null): bool
    {
        return $this->adjustProductStockAtomically($productId, $warehouseId, -$quantity, true, $variantId);
    }
}
