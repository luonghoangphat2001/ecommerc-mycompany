<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_cross_sell_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->foreignId('cross_sell_product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['shop_product_id', 'cross_sell_product_id'], 'cross_sell_unique');
            $table->index(
                ['shop_product_id', 'is_active', 'sort_order'],
                'cross_sell_active_sort_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_cross_sell_products');
    }
};
