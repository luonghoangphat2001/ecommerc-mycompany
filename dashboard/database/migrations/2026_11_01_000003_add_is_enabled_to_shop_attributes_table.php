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
        if (Schema::hasTable('shop_attributes') && !Schema::hasColumn('shop_attributes', 'is_enabled')) {
            Schema::table('shop_attributes', function (Blueprint $table) {
                $table->boolean('is_enabled')->default(true)->after('values');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('shop_attributes') && Schema::hasColumn('shop_attributes', 'is_enabled')) {
            Schema::table('shop_attributes', function (Blueprint $table) {
                $table->dropColumn('is_enabled');
            });
        }
    }
};
