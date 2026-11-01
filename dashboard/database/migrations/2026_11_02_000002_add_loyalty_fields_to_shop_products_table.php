<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            if (!Schema::hasColumn('shop_products', 'is_loyalty_earnable')) {
                $table->boolean('is_loyalty_earnable')->default(true)->after('apply_tax');
            }
            if (!Schema::hasColumn('shop_products', 'is_loyalty_redeemable')) {
                $table->boolean('is_loyalty_redeemable')->default(true)->after('is_loyalty_earnable');
            }
            if (!Schema::hasColumn('shop_products', 'loyalty_points_reward')) {
                $table->unsignedInteger('loyalty_points_reward')->nullable()->after('is_loyalty_redeemable');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('shop_products', 'is_loyalty_earnable')) {
                $columns[] = 'is_loyalty_earnable';
            }
            if (Schema::hasColumn('shop_products', 'is_loyalty_redeemable')) {
                $columns[] = 'is_loyalty_redeemable';
            }
            if (Schema::hasColumn('shop_products', 'loyalty_points_reward')) {
                $columns[] = 'loyalty_points_reward';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
