<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_financial_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // người đề xuất
            $table->string('title');
            $table->text('reason');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_urgent')->default(false);
            $table->timestamps();
        });

        Schema::create('department_payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('month'); // e.g. 2026-07
            $table->decimal('base_salary', 15, 2);
            $table->decimal('allowance', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('insurance', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2);
            $table->timestamps();
        });

        Schema::create('department_material_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('material_name');
            $table->decimal('price', 15, 2);
            $table->date('recorded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_material_prices');
        Schema::dropIfExists('department_payrolls');
        Schema::dropIfExists('department_financial_proposals');
    }
};
