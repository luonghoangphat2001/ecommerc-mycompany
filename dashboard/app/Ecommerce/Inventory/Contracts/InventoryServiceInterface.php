<?php

namespace App\Ecommerce\Inventory\Contracts;

use App\Models\Inventory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InventoryServiceInterface
{
    /**
     * Find a warehouse by ID.
     */
    public function findWarehouseOrFail(int $warehouseId): Inventory;

    /**
     * Get paginated product stock rows for a warehouse.
     */
    public function paginateWarehouseStocks(int $warehouseId, int $perPage = 25): LengthAwarePaginator;

    /**
     * Process Inventory Record and record atomic movements.
     *
     * @throws \Exception
     */
    public function processRecord(int $recordId): bool;
}
