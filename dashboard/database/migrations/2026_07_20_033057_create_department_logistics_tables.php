<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('po_number')->unique();
            $table->string('supplier_name');
            $table->enum('status', ['shipping', 'partial', 'completed', 'defective_return'])->default('shipping');
            $table->decimal('total_amount', 15, 2);
            $table->date('expected_delivery_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_purchase_orders');
    }
};
