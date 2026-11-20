<?php

namespace App\Ecommerce\Inventory\Contracts;

use App\Ecommerce\Core\Contracts\BaseRepositoryInterface;
use App\Models\Inventory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InventoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find a warehouse by ID.
     */
    public function findWarehouseOrFail(int $warehouseId): Inventory;

    /**
     * Paginate product stock rows for a warehouse.
     */
    public function paginateWarehouseStocks(int $warehouseId, int $perPage = 25): LengthAwarePaginator;

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
