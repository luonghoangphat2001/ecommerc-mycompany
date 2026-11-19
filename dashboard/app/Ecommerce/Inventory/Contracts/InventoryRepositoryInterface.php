<?php

namespace App\Ecommerce\Inventory\Contracts;

use App\Ecommerce\Core\Contracts\BaseRepositoryInterface;

interface InventoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get current stock for a product in a warehouse.
     */
    public function getStock(int $productId, int $warehouseId): int;

    /**
     * Add stock to a product in a warehouse.
     */
    public function addStock(int $productId, int $warehouseId, int $quantity): int;

    /**
     * Deduct stock from a product in a warehouse.
     */
    public function deductStock(int $productId, int $warehouseId, int $quantity): int;

    /**
     * Record inventory movement.
     */
    public function recordMovement(array $data): int;
}
