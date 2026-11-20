<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_payment_data', function (Blueprint $table): void {
            $table->id();
            $table->morphs('paymentable');
            $table->string('gateway', 50)->index();
            $table->string('key', 100);
            $table->json('value')->nullable();
            $table->timestamps();
            $table->unique(['paymentable_type', 'paymentable_id', 'gateway', 'key'], 'shop_payment_data_owner_gateway_key_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_payment_data');
    }
};
