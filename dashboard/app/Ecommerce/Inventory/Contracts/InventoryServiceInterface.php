<?php

namespace App\Ecommerce\Inventory\Contracts;

interface InventoryServiceInterface
{
    /**
     * Process Inventory Record and record atomic movements.
     *
     * @throws \Exception
     */
    public function processRecord(int $recordId): bool;
}
