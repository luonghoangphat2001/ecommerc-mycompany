<?php

namespace App\Ecommerce\Inventory\Contracts;

use App\Ecommerce\Core\Contracts\BaseRepositoryInterface;

interface InventoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get current stock for a product in a warehouse.
     *
     * @param int $productId
     * @param int $warehouseId
     * @return int
     */
    public function getStock(int $productId, int $warehouseId): int;

    /**
     * Add stock to a product in a warehouse.
     *
     * @param int $productId
     * @param int $warehouseId
     * @param int $quantity
     * @return int
     */
    public function addStock(int $productId, int $warehouseId, int $quantity): int;

    /**
     * Deduct stock from a product in a warehouse.
     *
     * @param int $productId
     * @param int $warehouseId
     * @param int $quantity
     * @return int
     */
    public function deductStock(int $productId, int $warehouseId, int $quantity): int;

    /**
     * Record inventory movement.
     *
     * @param array $data
     * @return bool
     */
    public function recordMovement(array $data): bool;
}
