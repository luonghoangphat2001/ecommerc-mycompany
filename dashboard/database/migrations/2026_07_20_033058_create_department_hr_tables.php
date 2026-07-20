<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('contract_code')->unique();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('position');
            $table->integer('performance_score')->nullable(); // 0 to 100
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_employee_contracts');
    }
};
