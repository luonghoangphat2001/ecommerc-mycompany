<?php

namespace App\Ecommerce\Inventory\Services;

use App\Ecommerce\Inventory\Contracts\InventoryRepositoryInterface;
use App\Ecommerce\Inventory\Contracts\InventoryServiceInterface;
use App\Enums\InventoryRecordStatus;
use App\Enums\InventoryRecordType;
use App\Models\InventoryRecord;
use App\Models\InventoryRecordItem;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\DB;

class InventoryService implements InventoryServiceInterface
{
    protected InventoryRepositoryInterface $inventoryRepository;

    public function __construct(InventoryRepositoryInterface $inventoryRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
    }

    /**
     * Process Inventory Record and record atomic movements.
     *
     * @throws Exception
     */
    public function processRecord(int $recordId): bool
    {
        $record = InventoryRecord::findOrFail($recordId);

        if ($record->status === InventoryRecordStatus::Completed || $record->status === 'COMPLETED') {
            throw new Exception(__('messages.record_already_processed'));
        }

        $recordType = $record->type instanceof InventoryRecordType ? $record->type->value : $record->type;

        return DB::transaction(function () use ($record, $recordType) {
            foreach ($record->items as $item) {
                if ($recordType === InventoryRecordType::In->value) {
                    $this->adjustStock($item->shop_product_id, $item->warehouse_id, $item->quantity, 'record', $record->id);
                } elseif ($recordType === InventoryRecordType::Out->value) {
                    $this->adjustStock($item->shop_product_id, $item->warehouse_id, -$item->quantity, 'record', $record->id);
                }
            }

            $record->update(['status' => InventoryRecordStatus::Completed]);

            return true;
        }, 3);
    }

    /**
     * Replace a draft record's lines atomically before it is processed.
     */
    public function replaceRecordItems(InventoryRecord $record, array $items): void
    {
        DB::transaction(function () use ($record, $items): void {
            $record->items()->delete();

            foreach ($items as $item) {
                if (! isset($item['shop_product_id'], $item['warehouse_id'], $item['quantity'])
                    || empty($item['shop_product_id'])) {
                    continue;
                }

                InventoryRecordItem::create([
                    'warehouse_record_id' => $record->id,
                    'shop_product_id' => (int) $item['shop_product_id'],
                    'warehouse_id' => (int) $item['warehouse_id'],
                    'target_warehouse_id' => ! empty($item['target_warehouse_id']) ? (int) $item['target_warehouse_id'] : null,
                    'quantity' => max(1, (int) $item['quantity']),
                ]);
            }
        });
    }

    protected function adjustStock(int $productId, int $warehouseId, int $quantityDelta, string $refType, int $refId): void
    {
        $currentStock = $this->inventoryRepository->getStock($productId, $warehouseId);
        $newStock = $quantityDelta >= 0
            ? $this->inventoryRepository->addStock($productId, $warehouseId, $quantityDelta)
            : $this->inventoryRepository->deductStock($productId, $warehouseId, abs($quantityDelta));

        $this->inventoryRepository->recordMovement([
            'shop_product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'movement_type' => $refType,
            'source_type' => \App\Models\InventoryRecord::class,
            'source_id' => $refId,
            'prev_stock' => $currentStock,
            'quantity_changed' => $quantityDelta,
            'new_stock' => $newStock,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->adjustProductTotalStock($productId, $quantityDelta);
    }

    protected function adjustProductTotalStock(int $productId, int $quantity): void
    {
        $product = Product::find($productId);

        if ($product) {
            $product->increment('total_stock', $quantity);
        }
    }
}
