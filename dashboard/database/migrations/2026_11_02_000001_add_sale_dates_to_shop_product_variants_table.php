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
        if (Schema::hasTable('shop_product_variants')) {
            Schema::table('shop_product_variants', function (Blueprint $table) {
                if (!Schema::hasColumn('shop_product_variants', 'sale_start_date')) {
                    $table->dateTime('sale_start_date')->nullable()->after('sale_price');
                }
                if (!Schema::hasColumn('shop_product_variants', 'sale_end_date')) {
                    $table->dateTime('sale_end_date')->nullable()->after('sale_start_date');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('shop_product_variants')) {
            Schema::table('shop_product_variants', function (Blueprint $table) {
                $table->dropColumn(['sale_start_date', 'sale_end_date']);
            });
        }
    }
};
