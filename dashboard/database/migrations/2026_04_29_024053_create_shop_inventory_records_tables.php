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
            $table->string('movement_type')->index();
            $table->integer('prev_stock');
            $table->integer('quantity_changed');
            $table->integer('new_stock');
            $table->timestamps();
        });

        Schema::create('shop_inventory_movement_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_movement_id')
                ->unique();
            $table->foreign('inventory_movement_id', 'movement_details_movement_fk')
                ->references('id')
                ->on('shop_inventory_movements')
                ->cascadeOnDelete();
            $table->foreignId('shop_product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('shop_inventories')->cascadeOnDelete();
            $table->nullableMorphs('source');
            $table->unsignedBigInteger('variant_id')->nullable()->index();
            $table->foreignId('inventory_id')
                ->nullable();
            $table->foreign('inventory_id', 'movement_details_inventory_fk')
                ->references('id')
                ->on('shop_inventories')
                ->nullOnDelete();
            $table->unsignedInteger('deducted_quantity')->nullable();
            $table->foreignId('restoration_of_movement_id')
                ->nullable();
            $table->foreign('restoration_of_movement_id', 'movement_details_restoration_fk')
                ->references('id')
                ->on('shop_inventory_movements')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(
                ['restoration_of_movement_id', 'created_at'],
                'inventory_movement_details_restoration_created_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_inventory_movement_details');
        Schema::dropIfExists('shop_inventory_movements');
        Schema::dropIfExists('shop_inventory_record_items');
        Schema::dropIfExists('shop_inventory_records');
    }

};
