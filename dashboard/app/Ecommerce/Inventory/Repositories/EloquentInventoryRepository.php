<?php

namespace App\Ecommerce\Inventory\Repositories;

use App\Ecommerce\Core\Repositories\BaseRepository;
use App\Ecommerce\Inventory\Contracts\InventoryRepositoryInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EloquentInventoryRepository extends BaseRepository implements InventoryRepositoryInterface
{
    /**
     * EloquentInventoryRepository constructor.
     *
     * @param  mixed  $model
     */
    public function __construct($model = null)
    {
        // Inventory doesn't have a single model, so we skip parent constructor
        // This repository handles stock, movement ledger, and movement details.
    }

    /**
     * {@inheritDoc}
     */
    public function getStock(int $productId, int $warehouseId): int
    {
        return (int) $this->stockQuery($productId, $warehouseId)
            ->value('stock_quantity') ?? 0;
    }

    /**
     * {@inheritDoc}
     */
    public function addStock(int $productId, int $warehouseId, int $quantity): int
    {
        return $this->adjustStock($productId, $warehouseId, $quantity, false);
    }

    /**
     * {@inheritDoc}
     */
    public function deductStock(int $productId, int $warehouseId, int $quantity): int
    {
        return $this->adjustStock($productId, $warehouseId, -$quantity, true);
    }

    /**
     * {@inheritDoc}
     */
    public function recordMovement(array $data): int
    {
        return DB::transaction(function () use ($data): int {
            $movementId = DB::table('shop_inventory_movements')->insertGetId(array_filter([
                'movement_type' => $data['movement_type'],
                'prev_stock' => $data['prev_stock'],
                'quantity_changed' => $data['quantity_changed'],
                'new_stock' => $data['new_stock'],
                'created_at' => $data['created_at'] ?? now(),
                'updated_at' => $data['updated_at'] ?? now(),
            ], static fn ($value) => $value !== null));

            DB::table('shop_inventory_movement_details')->insert(array_filter([
                'inventory_movement_id' => $movementId,
                'shop_product_id' => $data['shop_product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'source_type' => $data['source_type'] ?? null,
                'source_id' => $data['source_id'] ?? null,
                'variant_id' => $data['variant_id'] ?? null,
                'inventory_id' => $data['inventory_id'] ?? $data['warehouse_id'] ?? null,
                'deducted_quantity' => $data['deducted_quantity'] ?? null,
                'restoration_of_movement_id' => $data['restoration_of_movement_id'] ?? null,
                'created_at' => $data['created_at'] ?? now(),
                'updated_at' => $data['updated_at'] ?? now(),
            ], static fn ($value) => $value !== null));

            return (int) $movementId;
        });
    }

    protected function stockQuery(int $productId, int $warehouseId): Builder
    {
        return DB::table('shop_product_inventory_stocks')
            ->where('shop_product_id', $productId)
            ->where('warehouse_id', $warehouseId);
    }

    protected function adjustStock(
        int $productId,
        int $warehouseId,
        int $quantityDelta,
        bool $preventNegative,
    ): int {
        return DB::transaction(function () use ($productId, $warehouseId, $quantityDelta, $preventNegative): int {
            DB::table('shop_products')
                ->where('id', $productId)
                ->lockForUpdate()
                ->first();

            $stock = $this->stockQuery($productId, $warehouseId)
                ->lockForUpdate()
                ->first();
            $currentStock = (int) ($stock->stock_quantity ?? 0);
            $newStock = $currentStock + $quantityDelta;

            if ($preventNegative && $newStock < 0) {
                throw ValidationException::withMessages([
                    'order' => [__('messages.insufficient_stock')],
                ]);
            }

            if ($stock) {
                $this->stockQuery($productId, $warehouseId)->update([
                    'stock_quantity' => $newStock,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('shop_product_inventory_stocks')->insert([
                    'shop_product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'stock_quantity' => $newStock,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $newStock;
        }, 3);
    }
}
