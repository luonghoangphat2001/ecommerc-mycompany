<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_combo_product_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_product_id')->constrained('shop_combo_products')->cascadeOnDelete();
            $table->foreignId('shop_product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['combo_product_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_combo_product_items');
    }
};
