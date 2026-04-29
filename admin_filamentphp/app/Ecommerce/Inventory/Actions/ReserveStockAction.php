<?php

namespace App\Ecommerce\Inventory\Actions;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReserveStockAction
{
    /**
     * Soft-lock quantities in distributed inventories securely.
     *
     * @param string $sku
     * @param int $quantity
     * @param int $inventoryId
     * @param int|null $orderId
     * @return bool
     */
    public function execute(string $sku, int $quantity, int $inventoryId, ?int $orderId = null): bool
    {
        $expiry = Carbon::now()->addMinutes(15);

        return DB::table('shop_stock_reservations')->insert([
            'order_id' => $orderId,
            'warehouse_id' => $inventoryId,
            'sku' => $sku,
            'quantity' => $quantity,
            'expires_at' => $expiry,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
