<?php

namespace App\Ecommerce\Inventory\Repositories;

use App\Ecommerce\Inventory\Contracts\InventoryRepositoryInterface;
use App\Ecommerce\Core\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EloquentInventoryRepository extends BaseRepository implements InventoryRepositoryInterface
{
    /**
     * EloquentInventoryRepository constructor.
     *
     * @param mixed $model
     */
    public function __construct($model = null)
    {
        // Inventory doesn't have a single model, so we skip parent constructor
        // This repository handles multiple tables: shop_product_inventory_stocks, shop_inventory_movements
    }

    /**
     * @inheritDoc
     */
    public function getStock(int $productId, int $warehouseId): int
    {
        return (int) DB::table('shop_product_inventory_stocks')
            ->where('shop_product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->value('stock_quantity') ?? 0;
    }

    /**
     * @inheritDoc
     */
    public function addStock(int $productId, int $warehouseId, int $quantity): int
    {
        $currentStock = $this->getStock($productId, $warehouseId);

        DB::table('shop_product_inventory_stocks')
            ->updateOrInsert(
                ['shop_product_id' => $productId, 'warehouse_id' => $warehouseId],
                ['stock_quantity' => $currentStock + $quantity]
            );

        return $currentStock + $quantity;
    }

    /**
     * @inheritDoc
     */
    public function deductStock(int $productId, int $warehouseId, int $quantity): int
    {
        $currentStock = $this->getStock($productId, $warehouseId);

        if ($currentStock < $quantity) {
            throw ValidationException::withMessages([
                'order' => [__('messages.insufficient_stock')],
            ]);
        }

        DB::table('shop_product_inventory_stocks')
            ->where('shop_product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->decrement('stock_quantity', $quantity);

        return $currentStock - $quantity;
    }

    /**
     * @inheritDoc
     */
    public function recordMovement(array $data): bool
    {
        return DB::table('shop_inventory_movements')->insert([
            'shop_product_id' => $data['shop_product_id'],
            'warehouse_id' => $data['warehouse_id'],
            'reference_type' => $data['reference_type'],
            'reference_id' => $data['reference_id'],
            'prev_stock' => $data['prev_stock'],
            'quantity_changed' => $data['quantity_changed'],
            'new_stock' => $data['new_stock'],
            'created_at' => $data['created_at'] ?? now(),
            'updated_at' => $data['updated_at'] ?? now(),
        ]);
    }
}
