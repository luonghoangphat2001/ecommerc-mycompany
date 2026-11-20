<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Products
        Schema::create('shop_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_brand_id')->nullable()->constrained('shop_brands')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique()->nullable();
            $table->string('barcode')->unique()->nullable();
            $table->json('description')->nullable();
            $table->unsignedBigInteger('qty')->default(0);
            $table->unsignedBigInteger('security_stock')->default(0);
            $table->boolean('featured')->default(false);
            $table->boolean('is_visible')->default(false);
            $table->bigInteger('old_price')->nullable();
            $table->bigInteger('price')->nullable();
            $table->bigInteger('sale_price')->nullable();
            $table->dateTime('sale_start_date')->nullable();
            $table->dateTime('sale_end_date')->nullable();
            $table->bigInteger('cost')->nullable();
            $table->foreignId('tax_class_id')->nullable()->constrained('shop_tax_classes')->nullOnDelete();
            $table->string('shipping_class_id')->nullable(); // Can be string or foreignId if we add a table later
            $table->string('type')->default('deliverable'); 
            $table->boolean('backorder')->default(false);
            $table->boolean('requires_shipping')->default(false);
            $table->boolean('apply_tax')->default(true);
            $table->boolean('is_loyalty_earnable')->default(true);
            $table->boolean('is_loyalty_redeemable')->default(true);
            $table->unsignedInteger('loyalty_points_reward')->nullable();
            $table->date('published_at')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->unsignedBigInteger('product_images')->nullable();
            $table->unsignedBigInteger('total_stock')->default(0);
            $table->integer('version')->default(1);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['is_visible', 'published_at', 'created_at'], 'shop_products_storefront_browse_idx');
            $table->index(['is_visible', 'featured'], 'shop_products_featured_idx');
            $table->index('type', 'shop_products_type_idx');
        });


        // Junctions
        Schema::create('shop_category_product', function (Blueprint $table) {
            $table->foreignId('shop_category_id')->constrained('shop_categories')->cascadeOnDelete();
            $table->foreignId('shop_product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
            $table->primary(['shop_category_id', 'shop_product_id']);
        });

        Schema::create('shop_brand_product', function (Blueprint $table) {
            $table->foreignId('shop_brand_id')->constrained('shop_brands')->cascadeOnDelete();
            $table->foreignId('shop_product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
            $table->primary(['shop_brand_id', 'shop_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_brand_product');
        Schema::dropIfExists('shop_category_product');
        Schema::dropIfExists('shop_products');
    }
};
