<?php

namespace App\Ecommerce\Inventory\Services;

use Illuminate\Support\Facades\DB;
use App\Models\InventoryRecord;
use App\Models\InventoryRecordItem;
use App\Models\InventoryMovement;
use App\Models\Product;
use Exception;

class InventoryService
{
    /**
     * Process Inventory Record and record atomic movements.
     *
     * @param int $recordId
     * @return bool
     * @throws Exception
     */
    public function processRecord(int $recordId): bool
    {
        $record = InventoryRecord::findOrFail($recordId);

        if ($record->status === 'COMPLETED') {
            throw new Exception(__('messages.record_already_processed'));
        }

        return DB::transaction(function () use ($record) {
            foreach ($record->items as $item) {
                if ($record->type === 'IN') {
                    $this->addStock($item->shop_product_id, $item->warehouse_id, $item->quantity, 'record', $record->id);
                } elseif ($record->type === 'OUT') {
                    $this->deductStock($item->shop_product_id, $item->warehouse_id, $item->quantity, 'record', $record->id);
                }
            }

            $record->update(['status' => 'COMPLETED']);

            return true;
        }, 3);
    }

    protected function addStock(int $productId, int $warehouseId, int $quantity, string $refType, int $refId): void
    {
        $currentStock = DB::table('shop_product_inventory_stocks')
            ->where('shop_product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->value('stock_quantity') ?? 0;

        DB::table('shop_product_inventory_stocks')
            ->updateOrInsert(
                ['shop_product_id' => $productId, 'warehouse_id' => $warehouseId],
                ['stock_quantity' => $currentStock + $quantity]
            );

        DB::table('shop_inventory_movements')->insert([
            'shop_product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'prev_stock' => $currentStock,
            'quantity_changed' => $quantity,
            'new_stock' => $currentStock + $quantity,
            'created_at' => now(),
            'updated_at' => now(),
        ]);



        $product = Product::find($productId);
        if ($product) {
            $product->increment('total_stock', $quantity);
        }
    }

    protected function deductStock(int $productId, int $warehouseId, int $quantity, string $refType, int $refId): void
    {
        $currentStock = DB::table('shop_product_inventory_stocks')
            ->where('shop_product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->value('stock_quantity') ?? 0;

        if ($currentStock < $quantity) {
            throw new Exception(__('messages.insufficient_stock'));
        }

        DB::table('shop_product_inventory_stocks')
            ->where('shop_product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->decrement('stock_quantity', $quantity);

        DB::table('shop_inventory_movements')->insert([
            'shop_product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'prev_stock' => $currentStock,
            'quantity_changed' => -$quantity,
            'new_stock' => $currentStock - $quantity,
            'created_at' => now(),
            'updated_at' => now(),
        ]);



        $product = Product::find($productId);
        if ($product) {
            $product->decrement('total_stock', $quantity);
        }
    }
}
