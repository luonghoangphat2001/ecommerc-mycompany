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
        Schema::create('shop_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type'); // fixed_cart, percentage, fixed_product
            $table->decimal('amount', 12, 2);
            $table->decimal('minimum_order_amount', 12, 2)->nullable();
            $table->integer('limit_usage_to_x_items')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_limit_per_user')->nullable();
            $table->integer('usage_count')->default(0);
            $table->dateTime('expiry_date')->nullable();
            $table->boolean('individual_use')->default(false);
            $table->boolean('exclude_sale_items')->default(false);
            $table->json('product_ids')->nullable();
            $table->json('excluded_product_ids')->nullable();
            $table->json('category_ids')->nullable();
            $table->json('excluded_category_ids')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_coupons');
    }
};
