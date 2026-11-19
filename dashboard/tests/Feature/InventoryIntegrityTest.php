<?php

namespace Tests\Feature;

use App\Enums\ProductType;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use Database\Seeders\Demo\WarehouseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InventoryIntegrityTest extends TestCase
{
    use DatabaseTransactions;

    public function test_inventory_identity_rejects_duplicate_product_rows_with_null_variant(): void
    {
        [$productId, $warehouseId] = $this->inventoryContext();
        $row = [
            'shop_product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'variant_id' => null,
            'stock_quantity' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('shop_product_inventory_stocks')->insert($row);

        $this->expectException(QueryException::class);
        DB::table('shop_product_inventory_stocks')->insert($row);
    }

    public function test_inventory_identity_allows_distinct_variants_in_same_warehouse(): void
    {
        [$productId, $warehouseId] = $this->inventoryContext();
        $variantA = ProductVariant::create(['product_id' => $productId, 'sku' => 'VAR-A-' . uniqid(), 'qty' => 0, 'attributes' => []]);
        $variantB = ProductVariant::create(['product_id' => $productId, 'sku' => 'VAR-B-' . uniqid(), 'qty' => 0, 'attributes' => []]);
        DB::table('shop_product_inventory_stocks')->insert([
            [
                'shop_product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'variant_id' => $variantA->id,
                'stock_quantity' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'shop_product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'variant_id' => $variantB->id,
                'stock_quantity' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->assertSame(2, DB::table('shop_product_inventory_stocks')->where('shop_product_id', $productId)->count());
    }

    public function test_warehouse_seeder_is_idempotent_and_preserves_existing_quantity(): void
    {
        $product = Product::create([
            'name' => 'Warehouse seeder product',
            'slug' => 'warehouse-seeder-' . uniqid(),
            'sku' => 'WH-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 100,
            'qty' => 0,
            'total_stock' => 0,
        ]);

        app(WarehouseSeeder::class)->run();
        $warehouse = Inventory::where('slug', 'wh-main')->firstOrFail();
        $stock = DB::table('shop_product_inventory_stocks')
            ->where('shop_product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->whereNull('variant_id')
            ->first();

        DB::table('shop_product_inventory_stocks')->where('id', $stock->id)->update(['stock_quantity' => 3]);
        app(WarehouseSeeder::class)->run();

        $this->assertSame(3, (int) DB::table('shop_product_inventory_stocks')->where('id', $stock->id)->value('stock_quantity'));
        $this->assertSame(3, Inventory::whereIn('slug', ['wh-main', 'wh-hcm', 'wh-hn'])->count());
    }

    private function inventoryContext(): array
    {
        $product = Product::create([
            'name' => 'Inventory identity test',
            'slug' => 'inventory-identity-' . uniqid(),
            'sku' => 'INV-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 100,
            'qty' => 0,
            'total_stock' => 0,
        ]);
        $warehouse = Inventory::create([
            'name' => 'Inventory identity warehouse',
            'slug' => 'inventory-identity-warehouse-' . uniqid(),
            'is_active' => true,
        ]);

        return [$product->id, $warehouse->id];
    }
}
