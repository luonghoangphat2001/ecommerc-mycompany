<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $table = 'shop_inventory_movements';

    protected $fillable = [
        'shop_product_id',
        'warehouse_id',
        'reference_type',
        'reference_id',
        'prev_stock',
        'quantity_changed',
        'new_stock',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'shop_product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'warehouse_id');
    }
}
