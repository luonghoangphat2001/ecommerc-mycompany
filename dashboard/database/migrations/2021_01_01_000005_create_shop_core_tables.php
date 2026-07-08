<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Brands
        Schema::create('shop_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('website')->nullable();
            $table->longText('description')->nullable();
            $table->unsignedBigInteger('sort')->default(0);
            $table->boolean('is_visible')->default(false);
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Shop Categories
        Schema::create('shop_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('shop_categories')->cascadeOnDelete();
            $table->longText('description')->nullable();
            $table->unsignedBigInteger('sort')->default(0);
            $table->boolean('is_visible')->default(false);
            $table->string('seo_title', 60)->nullable();
            $table->string('seo_description', 160)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });



        // General Addresses table (Morphable)
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('shipping'); // shipping or billing
            $table->boolean('is_default')->default(false);
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('state_id')->nullable();
            $table->string('city_id')->nullable();
            $table->string('ward_id')->nullable();
            $table->string('postal_code')->nullable();
            $table->text('address_detail')->nullable();
            $table->string('country')->nullable();
            $table->string('street')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('ward')->nullable();
            $table->string('zip')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('addressables', function (Blueprint $table) {
            $table->foreignId('address_id')->constrained()->cascadeOnDelete();
            $table->morphs('addressable');
        });

        // Comments
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->nullableMorphs('commentable');
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        // Tax Classes
        Schema::create('shop_tax_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Tax Rates
        Schema::create('shop_tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_class_id')->constrained('shop_tax_classes')->cascadeOnDelete();
            $table->string('country', 2)->nullable(); // ISO code
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->decimal('rate', 8, 4)->default(0);
            $table->string('name');
            $table->integer('priority')->default(1);
            $table->boolean('is_compound')->default(false);
            $table->boolean('is_shipping')->default(true);
            $table->timestamps();
        });

        // Shipping Zones
        Schema::create('shop_shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('locations')->nullable();
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        // Shipping Methods
        Schema::create('shop_shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained('shop_shipping_zones')->cascadeOnDelete();
            $table->string('type'); // flat_rate, free_shipping, local_pickup
            $table->string('name');
            $table->json('settings')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        // Settings
        Schema::create('shop_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_settings');
        Schema::dropIfExists('shop_shipping_methods');
        Schema::dropIfExists('shop_shipping_zones');
        Schema::dropIfExists('shop_tax_rates');
        Schema::dropIfExists('shop_tax_classes');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('addressables');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('shop_categories');
        Schema::dropIfExists('shop_brands');
    }
};
