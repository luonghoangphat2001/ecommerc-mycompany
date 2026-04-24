<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Core Orders Table (Lean & Main totals)
        Schema::create('shop_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('number', 32)->unique();
            $table->bigInteger('subtotal')->default(0);
            $table->bigInteger('tax_amount')->default(0); 
            $table->bigInteger('total')->default(0);
            $table->string('currency')->default('VND');
            $table->decimal('exchange_rate', 10, 4)->default(1.0);
            $table->string('status')->default('new');
            $table->string('type')->default('shop');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Order Shipping Details (Separated)
        Schema::create('shop_order_shippings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('shop_orders')->cascadeOnDelete();
            $table->foreignId('shop_shipping_method_id')->nullable()->constrained('shop_shipping_methods')->nullOnDelete();
            $table->string('method')->nullable();
            $table->bigInteger('amount')->default(0);
            $table->bigInteger('tax_amount')->default(0);
            $table->string('tracking_number')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 3. Order Metadata (EAV Structure: Key-Value pairs)
        Schema::create('shop_order_metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('shop_orders')->cascadeOnDelete();
            $table->string('key')->index();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // 4. Order Items
        Schema::create('shop_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('shop_orders')->cascadeOnDelete();
            $table->foreignId('shop_product_id')->nullable()->constrained('shop_products')->nullOnDelete();
            $table->string('type')->default('product'); 
            $table->string('name')->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->bigInteger('unit_price')->default(0);
            $table->bigInteger('total')->default(0);
            $table->json('metadata')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        // 5. Order Taxes (Separated for Multiple Tax support)
        Schema::create('shop_order_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('shop_orders')->cascadeOnDelete();
            $table->foreignId('shop_order_item_id')->nullable()->constrained('shop_order_items')->cascadeOnDelete();
            $table->boolean('is_shipping')->default(false);
            $table->foreignId('shop_tax_rate_id')->nullable()->constrained('shop_tax_rates')->nullOnDelete();
            $table->string('name')->nullable();
            $table->bigInteger('amount')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 6. Order Refunds
        Schema::create('shop_order_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('shop_orders')->cascadeOnDelete();
            $table->bigInteger('amount');
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 7. Order Addresses
        Schema::create('shop_order_addresses', function (Blueprint $table) {
            $table->id();
            $table->morphs('addressable');
            $table->string('type')->default('shipping'); 
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('country_code')->nullable();
            $table->string('address_detail')->nullable();
            $table->string('city_id')->nullable();
            $table->string('state_id')->nullable();
            $table->string('ward_id')->nullable();
            $table->string('postal_code')->nullable();
            $table->timestamps();
        });

        // 8. Payments
        Schema::create('shop_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('shop_orders')->cascadeOnDelete();
            $table->string('method');
            $table->string('status')->default('pending');
            $table->string('currency')->default('VND');
            $table->bigInteger('amount');
            $table->string('provider')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_payments');
        Schema::dropIfExists('shop_order_addresses');
        Schema::dropIfExists('shop_order_items');
        Schema::dropIfExists('shop_order_refunds');
        Schema::dropIfExists('shop_order_taxes');
        Schema::dropIfExists('shop_order_metas');
        Schema::dropIfExists('shop_order_shippings');
        Schema::dropIfExists('shop_orders');
    }
};
