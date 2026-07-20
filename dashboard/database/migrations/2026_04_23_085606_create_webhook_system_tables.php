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
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('url');
            $table->string('secret');
            $table->boolean('is_active')->default(true);
            $table->json('events');
            $table->timestamps();
        });

        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_agent_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_id')->nullable();
            $table->string('action')->nullable();
            $table->string('event');
            $table->json('payload');
            $table->json('response')->nullable();
            $table->string('status')->default('pending');
            $table->integer('duration')->nullable(); // in milliseconds
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('webhooks');
    }
};
