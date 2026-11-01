<?php

namespace Database\Seeders\Demo;

use App\Enums\InventoryRecordStatus;
use App\Enums\InventoryRecordType;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\InventoryRecord;
use App\Models\InventoryRecordItem;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    /**
     * Run the inventory demo seeder.
     */
    public function run(): void
    {
        // 1. Seed Warehouses
        $warehouses = $this->seedWarehouses();

        // 2. Allocate stocks across warehouses for all products
        $this->seedProductStocks($warehouses);

        // 3. Seed Inventory Records and Movements
        $this->seedRecordsAndMovements($warehouses);

        $this->command?->info("InventorySeeder completed: Warehouses, stock levels, records, and movements synchronized.");
    }

    protected function seedWarehouses(): \Illuminate\Support\Collection
    {
        $warehousesData = [
            [
                'name' => 'Kho Tổng Nova Central (Quận 7)',
                'slug' => 'kho-tong-nova-central',
                'location' => 'Đường số 7, Khu Chế Xuất Tân Thuận, Quận 7, TP.HCM',
                'is_active' => true,
            ],
            [
                'name' => 'Kho Cửa Hàng Sân Vận Động Nova Arena',
                'slug' => 'kho-cua-hang-nova-arena',
                'location' => '123 Pasteur, Phường Bến Nghé, Quận 1, TP.HCM',
                'is_active' => true,
            ],
            [
                'name' => 'Kho Phụ Kiện & Thiết Bị Thể Thao Thủ Đức',
                'slug' => 'kho-thiet-bi-the-thao-thu-duc',
                'location' => 'Khu Công Nghệ Cao, TP. Thủ Đức, TP.HCM',
                'is_active' => true,
            ],
        ];

        $warehouses = collect();
        foreach ($warehousesData as $data) {
            $wh = Inventory::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
            $warehouses->push($wh);
        }

        return $warehouses;
    }

    protected function seedProductStocks(\Illuminate\Support\Collection $warehouses): void
    {
        $products = Product::all();
        if ($products->isEmpty()) {
            return;
        }

        $mainWh = $warehouses->where('slug', 'kho-tong-nova-central')->first() ?? $warehouses->first();
        $barWh = $warehouses->where('slug', 'kho-cua-hang-nova-arena')->first() ?? $warehouses->skip(1)->first() ?? $mainWh;
        $coldWh = $warehouses->where('slug', 'kho-thiet-bi-the-thao-thu-duc')->first() ?? $warehouses->last() ?? $mainWh;

        foreach ($products as $idx => $product) {
            if ($product->isVariable() && $product->variants()->exists()) {
                // Distribute stock across variants
                $totalVarStock = 0;
                foreach ($product->variants as $variant) {
                    $qtyMain = rand(15, 35);
                    $qtyBar = rand(10, 20);
                    $qtyCold = rand(5, 15);
                    $vStock = $qtyMain + $qtyBar + $qtyCold;
                    $totalVarStock += $vStock;

                    $variant->updateQuietly(['qty' => $vStock]);

                    DB::table('shop_product_inventory_stocks')->updateOrInsert(
                        ['shop_product_id' => $product->id, 'variant_id' => $variant->id, 'warehouse_id' => $mainWh->id],
                        ['stock_quantity' => $qtyMain, 'updated_at' => now(), 'created_at' => now()]
                    );

                    DB::table('shop_product_inventory_stocks')->updateOrInsert(
                        ['shop_product_id' => $product->id, 'variant_id' => $variant->id, 'warehouse_id' => $barWh->id],
                        ['stock_quantity' => $qtyBar, 'updated_at' => now(), 'created_at' => now()]
                    );

                    DB::table('shop_product_inventory_stocks')->updateOrInsert(
                        ['shop_product_id' => $product->id, 'variant_id' => $variant->id, 'warehouse_id' => $coldWh->id],
                        ['stock_quantity' => $qtyCold, 'updated_at' => now(), 'created_at' => now()]
                    );
                }

                $product->updateQuietly([
                    'qty' => $totalVarStock,
                    'total_stock' => $totalVarStock,
                ]);
            } elseif ($product->isCombo()) {
                // Combo available stock is calculated dynamically from component stocks
                $comboStock = $product->calculateComboStock();
                $product->updateQuietly([
                    'qty' => $comboStock,
                    'total_stock' => $comboStock,
                ]);
            } else {
                // Simple product stock allocation
                if ($product->sku === 'CLUB-SPIR-003' || $product->sku === 'CLUB-WINE-002') {
                    // Low stock luxury items (to trigger dashboard low-stock warning)
                    $qtyMain = 4;
                    $qtyBar = 2;
                    $qtyCold = 2;
                } elseif ($idx === 5) {
                    // Out of stock item
                    $qtyMain = 0;
                    $qtyBar = 0;
                    $qtyCold = 0;
                } else {
                    // Standard healthy stock
                    $qtyMain = rand(30, 80);
                    $qtyBar = rand(15, 40);
                    $qtyCold = rand(10, 30);
                }

                $totalStock = $qtyMain + $qtyBar + $qtyCold;

                // Sync stocks into shop_product_inventory_stocks
                DB::table('shop_product_inventory_stocks')->updateOrInsert(
                    ['shop_product_id' => $product->id, 'variant_id' => null, 'warehouse_id' => $mainWh->id],
                    ['stock_quantity' => $qtyMain, 'updated_at' => now(), 'created_at' => now()]
                );

                DB::table('shop_product_inventory_stocks')->updateOrInsert(
                    ['shop_product_id' => $product->id, 'variant_id' => null, 'warehouse_id' => $barWh->id],
                    ['stock_quantity' => $qtyBar, 'updated_at' => now(), 'created_at' => now()]
                );

                DB::table('shop_product_inventory_stocks')->updateOrInsert(
                    ['shop_product_id' => $product->id, 'variant_id' => null, 'warehouse_id' => $coldWh->id],
                    ['stock_quantity' => $qtyCold, 'updated_at' => now(), 'created_at' => now()]
                );

                // Update master product model with exact sum
                $product->updateQuietly([
                    'qty' => $totalStock,
                    'total_stock' => $totalStock,
                ]);
            }
        }
    }

    protected function seedRecordsAndMovements(\Illuminate\Support\Collection $warehouses): void
    {
        $mainWh = $warehouses->where('slug', 'kho-tong-nova-central')->first() ?? $warehouses->first();
        $barWh = $warehouses->where('slug', 'kho-cua-hang-nova-arena')->first() ?? $warehouses->skip(1)->first() ?? $mainWh;
        $products = Product::take(6)->get();

        if ($products->isEmpty()) {
            return;
        }

        // 1. Initial Inbound Record (IN / COMPLETED)
        $inboundRecord = InventoryRecord::updateOrCreate(
            ['notes' => 'Nhập kho định kỳ nguyên liệu đồ uống và rượu ngoại từ nhà phân phối.'],
            [
                'type' => InventoryRecordType::In,
                'status' => InventoryRecordStatus::Completed,
                'created_at' => now()->subDays(30),
                'updated_at' => now()->subDays(30),
            ]
        );

        foreach ($products as $p) {
            InventoryRecordItem::updateOrCreate(
                [
                    'warehouse_record_id' => $inboundRecord->id,
                    'shop_product_id' => $p->id,
                    'warehouse_id' => $mainWh->id,
                ],
                [
                    'target_warehouse_id' => null,
                    'quantity' => 100,
                    'created_at' => now()->subDays(30),
                    'updated_at' => now()->subDays(30),
                ]
            );

            // Movement for Inbound
            InventoryMovement::create([
                'shop_product_id' => $p->id,
                'warehouse_id' => $mainWh->id,
                'reference_type' => 'inbound_shipment',
                'reference_id' => $inboundRecord->id,
                'prev_stock' => 0,
                'quantity_changed' => 100,
                'new_stock' => 100,
                'created_at' => now()->subDays(30),
                'updated_at' => now()->subDays(30),
            ]);
        }

        // 2. Transfer Record from Kho Tổng to Kho Cửa Hàng (TRANSFER / COMPLETED)
        $transferRecord = InventoryRecord::updateOrCreate(
            ['notes' => 'Điều chuyển trang phục thi đấu, bóng và phụ kiện thể thao phục vụ sự kiện Nova Arena cuối tuần.'],
            [
                'type' => InventoryRecordType::Transfer,
                'status' => InventoryRecordStatus::Completed,
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ]
        );

        foreach ($products->take(4) as $p) {
            InventoryRecordItem::updateOrCreate(
                [
                    'warehouse_record_id' => $transferRecord->id,
                    'shop_product_id' => $p->id,
                    'warehouse_id' => $mainWh->id,
                ],
                [
                    'target_warehouse_id' => $barWh->id,
                    'quantity' => 20,
                    'created_at' => now()->subDays(10),
                    'updated_at' => now()->subDays(10),
                ]
            );

            // Movement out of main
            InventoryMovement::create([
                'shop_product_id' => $p->id,
                'warehouse_id' => $mainWh->id,
                'reference_type' => 'transfer_out',
                'reference_id' => $transferRecord->id,
                'prev_stock' => 100,
                'quantity_changed' => -20,
                'new_stock' => 80,
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ]);

            // Movement into bar
            InventoryMovement::create([
                'shop_product_id' => $p->id,
                'warehouse_id' => $barWh->id,
                'reference_type' => 'transfer_in',
                'reference_id' => $transferRecord->id,
                'prev_stock' => 10,
                'quantity_changed' => 20,
                'new_stock' => 30,
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ]);
        }

        // 3. Draft Inbound Record for Upcoming Season
        InventoryRecord::updateOrCreate(
            ['notes' => 'Lô hàng dự kiến nhập khẩu đợt lễ hội Halloween và Giáng sinh.'],
            [
                'type' => InventoryRecordType::In,
                'status' => InventoryRecordStatus::Draft,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
