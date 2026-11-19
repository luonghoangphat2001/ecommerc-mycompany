<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryMovementDetail extends Model
{
    protected $table = 'shop_inventory_movement_details';

    protected $fillable = [
        'inventory_movement_id',
        'shop_product_id',
        'warehouse_id',
        'source_type',
        'source_id',
        'movement_type',
        'variant_id',
        'inventory_id',
        'deducted_quantity',
        'restoration_of_movement_id',
    ];

    protected $casts = [
        'shop_product_id' => 'integer',
        'warehouse_id' => 'integer',
        'variant_id' => 'integer',
        'inventory_id' => 'integer',
        'deducted_quantity' => 'integer',
        'restoration_of_movement_id' => 'integer',
    ];

    public function movement(): BelongsTo
    {
        return $this->belongsTo(InventoryMovement::class, 'inventory_movement_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'shop_product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'warehouse_id');
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    public function restorationOfMovement(): BelongsTo
    {
        return $this->belongsTo(InventoryMovement::class, 'restoration_of_movement_id');
    }
}
