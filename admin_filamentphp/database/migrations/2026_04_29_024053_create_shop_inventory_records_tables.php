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
        Schema::create('shop_inventory_records', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // IN, OUT, TRANSFER
            $table->string('status')->default('DRAFT'); // DRAFT, COMPLETED, CANCELLED
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('shop_inventory_record_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_record_id')->constrained('shop_inventory_records')->cascadeOnDelete();
            $table->foreignId('shop_product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('shop_inventories')->cascadeOnDelete();
            $table->foreignId('target_warehouse_id')->nullable()->constrained('shop_inventories')->nullOnDelete();
            $table->integer('quantity');
            $table->timestamps();
        });

        Schema::create('shop_inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('shop_inventories')->cascadeOnDelete();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->integer('prev_stock');
            $table->integer('quantity_changed');
            $table->integer('new_stock');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_inventory_movements');
        Schema::dropIfExists('shop_inventory_record_items');
        Schema::dropIfExists('shop_inventory_records');
    }

};
