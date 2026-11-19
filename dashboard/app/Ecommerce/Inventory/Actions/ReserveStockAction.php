<?php

namespace App\Ecommerce\Inventory\Actions;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReserveStockAction
{
    /**
     * Soft-lock quantities in distributed inventories securely.
     */
    public function execute(string $sku, int $quantity, int $inventoryId, ?int $orderId = null): bool
    {
        $timestamp = Carbon::now();
        $expiry = $timestamp->copy()->addMinutes(15);

        return DB::table('shop_stock_reservations')->insert([
            'order_id' => $orderId,
            'warehouse_id' => $inventoryId,
            'sku' => $sku,
            'quantity' => $quantity,
            'expires_at' => $expiry,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }
}
