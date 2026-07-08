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
        Schema::create('shop_inventories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('shop_product_inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('shop_inventories')->cascadeOnDelete();
            $table->integer('stock_quantity')->default(0);
            $table->timestamps();

            // Composite Index for extreme performance
            $table->unique(['shop_product_id', 'warehouse_id'], 'product_inventory_unique');
        });

        Schema::create('shop_stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->foreignId('warehouse_id')->constrained('shop_inventories')->cascadeOnDelete();
            $table->string('sku');
            $table->integer('quantity');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_stock_reservations');
        Schema::dropIfExists('shop_product_inventory_stocks');
        Schema::dropIfExists('shop_inventories');
    }

};
