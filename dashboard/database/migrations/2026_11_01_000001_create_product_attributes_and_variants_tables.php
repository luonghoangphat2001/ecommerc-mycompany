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
        // 1. Reusable global attributes (Size, Color, Material, etc.)
        if (!Schema::hasTable('shop_attributes')) {
            Schema::create('shop_attributes', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('type')->default('select'); // select, button, color
                $table->json('values')->nullable(); // Predefined terms/values
                $table->timestamps();
            });
        }

        // 2. Product-specific attribute assignment
        if (!Schema::hasTable('shop_product_attributes')) {
            Schema::create('shop_product_attributes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('shop_products')->cascadeOnDelete();
                $table->foreignId('attribute_id')->nullable()->constrained('shop_attributes')->nullOnDelete();
                $table->string('name');
                $table->json('options'); // e.g. ["S", "M", "L", "XL"]
                $table->boolean('is_variation')->default(true);
                $table->integer('position')->default(0);
                $table->timestamps();

                $table->index(['product_id', 'position']);
            });
        }

        // 3. Product variants
        if (!Schema::hasTable('shop_product_variants')) {
            Schema::create('shop_product_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('shop_products')->cascadeOnDelete();
                $table->string('sku')->nullable()->index();
                $table->bigInteger('price')->default(0);
                $table->bigInteger('sale_price')->nullable();
                $table->bigInteger('cost')->nullable();
                $table->integer('qty')->default(0);
                $table->json('attributes'); // e.g. {"Size": "M", "Color": "Black"}
                $table->unsignedBigInteger('image')->nullable();
                $table->decimal('weight', 8, 2)->nullable();
                $table->json('dimensions')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('tax_class_id')->nullable()->constrained('shop_tax_classes')->nullOnDelete();
                $table->timestamps();

                $table->index(['product_id', 'is_active']);
            });
        }

        // 4. Link shop_combo_products to parent product
        if (Schema::hasTable('shop_combo_products') && !Schema::hasColumn('shop_combo_products', 'product_id')) {
            Schema::table('shop_combo_products', function (Blueprint $table) {
                $table->foreignId('product_id')->nullable()->after('id')->constrained('shop_products')->nullOnDelete();
            });
        }

        // 5. Enhance combo product items with parent product and variant support
        if (Schema::hasTable('shop_combo_product_items')) {
            Schema::table('shop_combo_product_items', function (Blueprint $table) {
                if (!Schema::hasColumn('shop_combo_product_items', 'parent_product_id')) {
                    $table->foreignId('parent_product_id')->nullable()->after('combo_product_id')->constrained('shop_products')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('shop_combo_product_items', 'product_variant_id')) {
                    $table->foreignId('product_variant_id')->nullable()->after('shop_product_id')->constrained('shop_product_variants')->nullOnDelete();
                }
            });
        }

        // 6. Support variant in order items
        if (Schema::hasTable('shop_order_items') && !Schema::hasColumn('shop_order_items', 'variant_id')) {
            Schema::table('shop_order_items', function (Blueprint $table) {
                $table->foreignId('variant_id')->nullable()->after('shop_product_id')->constrained('shop_product_variants')->nullOnDelete();
            });
        }

        // 7. Support variant in warehouse stocks
        if (Schema::hasTable('shop_product_inventory_stocks') && !Schema::hasColumn('shop_product_inventory_stocks', 'variant_id')) {
            Schema::table('shop_product_inventory_stocks', function (Blueprint $table) {
                $table->foreignId('variant_id')->nullable()->after('shop_product_id')->constrained('shop_product_variants')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('shop_product_inventory_stocks') && Schema::hasColumn('shop_product_inventory_stocks', 'variant_id')) {
            Schema::table('shop_product_inventory_stocks', function (Blueprint $table) {
                $table->dropConstrainedForeignId('variant_id');
            });
        }

        if (Schema::hasTable('shop_order_items') && Schema::hasColumn('shop_order_items', 'variant_id')) {
            Schema::table('shop_order_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('variant_id');
            });
        }

        if (Schema::hasTable('shop_combo_product_items')) {
            Schema::table('shop_combo_product_items', function (Blueprint $table) {
                if (Schema::hasColumn('shop_combo_product_items', 'product_variant_id')) {
                    $table->dropConstrainedForeignId('product_variant_id');
                }
                if (Schema::hasColumn('shop_combo_product_items', 'parent_product_id')) {
                    $table->dropConstrainedForeignId('parent_product_id');
                }
            });
        }

        if (Schema::hasTable('shop_combo_products') && Schema::hasColumn('shop_combo_products', 'product_id')) {
            Schema::table('shop_combo_products', function (Blueprint $table) {
                $table->dropConstrainedForeignId('product_id');
            });
        }

        Schema::dropIfExists('shop_product_variants');
        Schema::dropIfExists('shop_product_attributes');
        Schema::dropIfExists('shop_attributes');
    }
};
