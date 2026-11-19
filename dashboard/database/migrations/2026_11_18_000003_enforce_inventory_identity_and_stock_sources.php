<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('shop_product_inventory_stocks')) {
            $duplicates = DB::table('shop_product_inventory_stocks')
                ->select('shop_product_id', 'warehouse_id', 'variant_id', DB::raw('COUNT(*) as duplicate_count'))
                ->groupBy('shop_product_id', 'warehouse_id', 'variant_id')
                ->having('duplicate_count', '>', 1)
                ->exists();

            if ($duplicates) {
                throw new RuntimeException(
                    'Cannot enforce inventory identity: duplicate product/warehouse/variant rows require reconciliation.'
                );
            }

            if (DB::connection()->getDriverName() === 'sqlite') {
                DB::statement(
                    'CREATE UNIQUE INDEX shop_product_inventory_identity_unique '
                    . 'ON shop_product_inventory_stocks (shop_product_id, warehouse_id, COALESCE(variant_id, 0))'
                );
            } else {
                DB::statement(
                    'CREATE UNIQUE INDEX shop_product_inventory_identity_unique '
                    . 'ON shop_product_inventory_stocks '
                    . '(shop_product_id, warehouse_id, (COALESCE(variant_id, 0)))'
                );
            }
        }

    }

    public function down(): void
    {
        if (Schema::hasTable('shop_product_inventory_stocks')) {
            if (DB::connection()->getDriverName() === 'sqlite') {
                DB::statement('DROP INDEX IF EXISTS shop_product_inventory_identity_unique');
            } else {
                DB::statement('DROP INDEX shop_product_inventory_identity_unique ON shop_product_inventory_stocks');
            }
        }
    }
};
