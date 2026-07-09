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
        Schema::table('shop_order_addresses', function (Blueprint $table) {
            $table->string('address_line_2')->nullable()->after('address_detail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_order_addresses', function (Blueprint $table) {
            $table->dropColumn('address_line_2');
        });
    }
};
