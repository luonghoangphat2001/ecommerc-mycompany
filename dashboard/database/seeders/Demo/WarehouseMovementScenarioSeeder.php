<?php

namespace Database\Seeders\Demo;

use App\Ecommerce\Inventory\Actions\DeductStockAction;
use App\Models\OrderItem;
use App\Settings\InventorySettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarehouseMovementScenarioSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('Warehouse movement scenario skipped outside local/testing.');

            return;
        }

        $settings = app(InventorySettings::class);
        $settings->multi_warehouse_enabled = true;
        $settings->save();

        $this->seedScenarioForItem($this->findItem(false));
        $this->seedScenarioForItem($this->findItem(true));
    }

    private function findItem(bool $variant): ?OrderItem
    {
        return OrderItem::query()
            ->whereNotNull('shop_product_id')
            ->when($variant, fn ($query) => $query->whereNotNull('variant_id'), fn ($query) => $query->whereNull('variant_id'))
            ->whereDoesntHave('product', fn ($query) => $query->where('type', 'combo'))
            ->orderBy('id')
            ->get()
            ->first(function (OrderItem $item) use ($variant): bool {
                return DB::table('shop_product_inventory_stocks')
                    ->where('shop_product_id', $item->shop_product_id)
                    ->where('warehouse_id', DB::table('shop_inventories')->where('slug', 'wh-main')->value('id'))
                    ->when($variant, fn ($query) => $query->where('variant_id', $item->variant_id), fn ($query) => $query->whereNull('variant_id'))
                    ->where('stock_quantity', '>=', 1)
                    ->exists();
            });
    }

    private function seedScenarioForItem(?OrderItem $item): void
    {
        if (! $item) {
            return;
        }

        $alreadyRecorded = DB::table('shop_inventory_movements')
            ->join('shop_inventory_movement_details as movement_details', 'movement_details.inventory_movement_id', '=', 'shop_inventory_movements.id')
            ->where('shop_inventory_movements.movement_type', 'demo_order_stock_deduction')
            ->where('movement_details.source_id', $item->id)
            ->exists();
        if ($alreadyRecorded) {
            return;
        }

        $warehouseId = (int) DB::table('shop_inventories')->where('slug', 'wh-main')->value('id');
        $before = DB::table('shop_product_inventory_stocks')
            ->where('shop_product_id', $item->shop_product_id)
            ->where('warehouse_id', $warehouseId)
            ->when($item->variant_id, fn ($query) => $query->where('variant_id', $item->variant_id), fn ($query) => $query->whereNull('variant_id'))
            ->value('stock_quantity');

        DB::transaction(function () use ($item, $warehouseId, $before): void {
            app(DeductStockAction::class)->execute((int) $item->shop_product_id, $warehouseId, 1, $item->variant_id);
            $after = DB::table('shop_product_inventory_stocks')
                ->where('shop_product_id', $item->shop_product_id)
                ->where('warehouse_id', $warehouseId)
                ->when($item->variant_id, fn ($query) => $query->where('variant_id', $item->variant_id), fn ($query) => $query->whereNull('variant_id'))
                ->value('stock_quantity');

            $movementId = DB::table('shop_inventory_movements')->insertGetId([
                'movement_type' => 'demo_order_stock_deduction',
                'prev_stock' => $before,
                'quantity_changed' => -1,
                'new_stock' => $after,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('shop_inventory_movement_details')->insert([
                'inventory_movement_id' => $movementId,
                'shop_product_id' => $item->shop_product_id,
                'warehouse_id' => $warehouseId,
                'inventory_id' => $warehouseId,
                'variant_id' => $item->variant_id,
                'deducted_quantity' => 1,
                'source_type' => OrderItem::class,
                'source_id' => $item->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $metadata = $item->metadata ?? [];
            $metadata['stock_source'] = [
                'inventory_id' => $warehouseId,
                'warehouse_id' => $warehouseId,
                'variant_id' => $item->variant_id,
                'quantity' => 1,
                'movement_reference' => "demo_order_item:{$item->id}:inventory:{$warehouseId}",
            ];
            $item->update(['metadata' => $metadata]);
        }, 3);
    }
}
