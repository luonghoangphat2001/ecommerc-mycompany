<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('shop_product_inventory_stocks')) {
            Schema::table('shop_product_inventory_stocks', function (Blueprint $table) {
                $table->dropUnique('product_inventory_unique');
                $table->index(['shop_product_id', 'warehouse_id'], 'shop_prod_inv_prod_wh_idx');
                $table->index(['variant_id', 'warehouse_id'], 'shop_prod_inv_var_wh_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('shop_product_inventory_stocks')) {
            Schema::table('shop_product_inventory_stocks', function (Blueprint $table) {
                $table->dropIndex('shop_prod_inv_prod_wh_idx');
                $table->dropIndex('shop_prod_inv_var_wh_idx');
                $table->unique(['shop_product_id', 'warehouse_id'], 'product_inventory_unique');
            });
        }
    }
};

