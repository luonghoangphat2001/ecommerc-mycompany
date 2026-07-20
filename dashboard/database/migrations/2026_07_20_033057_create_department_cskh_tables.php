<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_customer_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->text('content');
            $table->integer('rating')->default(5); // 1 to 5
            $table->enum('sentiment', ['positive', 'neutral', 'negative'])->default('neutral');
            $table->foreignId('coupon_id')->nullable()->constrained('shop_coupons')->nullOnDelete();
            $table->text('reply_content')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_customer_reviews');
    }
};
