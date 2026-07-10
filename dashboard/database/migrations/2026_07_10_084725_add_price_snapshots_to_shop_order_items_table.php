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
        Schema::table('shop_order_items', function (Blueprint $table) {
            $table->unsignedInteger('original_price')->nullable()->after('unit_price');
            $table->unsignedInteger('sale_price')->nullable()->after('original_price');
            $table->unsignedInteger('discount_amount')->default(0)->after('sale_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_order_items', function (Blueprint $table) {
            $table->dropColumn(['original_price', 'sale_price', 'discount_amount']);
        });
    }
};
