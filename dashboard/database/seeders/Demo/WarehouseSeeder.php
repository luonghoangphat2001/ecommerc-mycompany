<?php

namespace Database\Seeders\Demo;

use App\Models\Inventory;
use App\Models\Product;
use App\Settings\InventorySettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class WarehouseSeeder extends Seeder
{
    /**
     * Seed development/staging warehouse samples without changing existing stock.
     */
    public function run(): void
    {
        if (App::environment('production')) {
            $this->command?->warn('Warehouse sample seeding skipped in production.');

            return;
        }

        $warehouses = $this->warehouses();
        $this->seedMissingProductStocks($warehouses);
        if (App::environment(['local', 'testing'])) {
            $settings = app(InventorySettings::class);
            $settings->multi_warehouse_enabled = true;
            $settings->save();
        }
        $this->command?->info('Warehouse sample data is ready; existing quantities were preserved.');
    }

    /** @return Collection<string, Inventory> */
    private function warehouses(): Collection
    {
        return collect([
            ['slug' => 'wh-main', 'name' => 'Kho Tổng', 'location' => 'Khu công nghiệp Tân Tạo, TP.HCM'],
            ['slug' => 'wh-hcm', 'name' => 'Kho TP.HCM', 'location' => 'Quận 7, TP.HCM'],
            ['slug' => 'wh-hn', 'name' => 'Kho Hà Nội', 'location' => 'Quận Long Biên, Hà Nội'],
        ])->mapWithKeys(function (array $data): array {
            $warehouse = Inventory::firstOrCreate(
                ['slug' => $data['slug']],
                [...$data, 'is_active' => true]
            );

            return [$data['slug'] => $warehouse];
        });
    }

    private function seedMissingProductStocks(Collection $warehouses): void
    {
        Product::query()->with('variants')->each(function (Product $product) use ($warehouses): void {
            if ($product->isCombo()) {
                return;
            }

            if ($product->isVariable() && $product->variants->isNotEmpty()) {
                foreach ($product->variants as $variant) {
                    foreach ($warehouses as $slug => $warehouse) {
                        $this->insertIfMissing($product->id, $variant->id, $warehouse->id, $this->quantityFor($variant->id, $slug, 12));
                    }
                }

                return;
            }

            foreach ($warehouses as $slug => $warehouse) {
                $this->insertIfMissing($product->id, null, $warehouse->id, $this->quantityFor($product->id, $slug, 35));
            }
        });
    }

    private function insertIfMissing(int $productId, ?int $variantId, int $warehouseId, int $quantity): void
    {
        $query = DB::table('shop_product_inventory_stocks')
            ->where('shop_product_id', $productId)
            ->where('warehouse_id', $warehouseId);

        $variantId === null ? $query->whereNull('variant_id') : $query->where('variant_id', $variantId);

        if (! $query->exists()) {
            DB::table('shop_product_inventory_stocks')->insert([
                'shop_product_id' => $productId,
                'variant_id' => $variantId,
                'warehouse_id' => $warehouseId,
                'stock_quantity' => $quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function quantityFor(int $seed, string $warehouse, int $base): int
    {
        $offset = match ($warehouse) {
            'wh-main' => 18,
            'wh-hcm' => 8,
            default => 4,
        };

        return $base + $offset + ($seed % 7);
    }
}
