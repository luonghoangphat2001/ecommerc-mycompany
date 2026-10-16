<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Check if an index exists in a driver-agnostic way.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $driver = DB::getDriverName();
            if ($driver === 'sqlite') {
                $indexes = DB::select("SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = ? AND name = ?", [$table, $indexName]);
                return !empty($indexes);
            }

            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return !empty($indexes);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Run the migrations.
     * Thêm các index chuyên biệt tối ưu truy vấn nghiệp vụ theo khuyến nghị Senior Laravel Architect.
     */
    public function up(): void
    {
        // 1. Tối ưu truy vấn đếm và tính tổng doanh thu theo trạng thái đơn hàng (Order::where('status', ...)->sum('total'))
        if (!$this->indexExists('shop_orders', 'shop_orders_status_total_idx')) {
            Schema::table('shop_orders', function (Blueprint $table) {
                $table->index(['status', 'total'], 'shop_orders_status_total_idx');
            });
        }

        // 2. Tối ưu xác nhận thanh toán ($order->payments()->where('status', 'pending')) và thống kê thanh toán đã nhận
        if (!$this->indexExists('shop_payments', 'shop_payments_order_status_idx')) {
            Schema::table('shop_payments', function (Blueprint $table) {
                $table->index(['order_id', 'status'], 'shop_payments_order_status_idx');
            });
        }

        if (!$this->indexExists('shop_payments', 'shop_payments_status_amount_idx')) {
            Schema::table('shop_payments', function (Blueprint $table) {
                $table->index(['status', 'amount'], 'shop_payments_status_amount_idx');
            });
        }

        // 3. Tối ưu eager loading categories từ phía product ($product->categories) trên pivot table
        if (!$this->indexExists('shop_category_product', 'shop_category_product_product_id_idx')) {
            Schema::table('shop_category_product', function (Blueprint $table) {
                $table->index('shop_product_id', 'shop_category_product_product_id_idx');
            });
        }

        // 4. Tối ưu eager loading brands từ phía product ($product->brands) trên pivot table
        if (!$this->indexExists('shop_brand_product', 'shop_brand_product_product_id_idx')) {
            Schema::table('shop_brand_product', function (Blueprint $table) {
                $table->index('shop_product_id', 'shop_brand_product_product_id_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropIndex('shop_orders_status_total_idx');
        });

        Schema::table('shop_payments', function (Blueprint $table) {
            $table->dropIndex('shop_payments_order_status_idx');
            $table->dropIndex('shop_payments_status_amount_idx');
        });

        Schema::table('shop_category_product', function (Blueprint $table) {
            $table->dropIndex('shop_category_product_product_id_idx');
        });

        Schema::table('shop_brand_product', function (Blueprint $table) {
            $table->dropIndex('shop_brand_product_product_id_idx');
        });
    }
};
