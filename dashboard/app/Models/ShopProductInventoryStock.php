<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopProductInventoryStock extends Pivot
{
    protected $table = 'shop_product_inventory_stocks';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'shop_product_id');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'warehouse_id');
    }

    // Alias for qty since my repository uses quantity
    public function getQuantityAttribute()
    {
        return $this->stock_quantity;
    }
}
