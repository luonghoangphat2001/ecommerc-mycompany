<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryRecordItem extends Model
{
    protected $table = 'shop_inventory_record_items';

    protected $fillable = [
        'warehouse_record_id',
        'shop_product_id',
        'warehouse_id',
        'target_warehouse_id',
        'quantity',
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(InventoryRecord::class, 'warehouse_record_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'shop_product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'warehouse_id');
    }

    public function targetWarehouse(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'target_warehouse_id');
    }
}
