<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryMovement extends Model
{
    protected $table = 'shop_inventory_movements';

    protected $fillable = [
        'movement_type',
        'shop_product_id',
        'warehouse_id',
        'source_type',
        'source_id',
        'variant_id',
        'restoration_of_movement_id',
        'prev_stock',
        'quantity_changed',
        'new_stock',
    ];

    protected $casts = [
        'shop_product_id' => 'integer',
        'warehouse_id' => 'integer',
        'variant_id' => 'integer',
        'source_id' => 'integer',
        'restoration_of_movement_id' => 'integer',
        'prev_stock' => 'integer',
        'quantity_changed' => 'integer',
        'new_stock' => 'integer',
    ];

    /** @return BelongsTo<Product, self> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'shop_product_id');
    }

    /** @return BelongsTo<Inventory, self> */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'warehouse_id');
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<self, self> */
    public function restorationOfMovement(): BelongsTo
    {
        return $this->belongsTo(self::class, 'restoration_of_movement_id');
    }

    /** @return HasMany<self> */
    public function restorations(): HasMany
    {
        return $this->hasMany(self::class, 'restoration_of_movement_id');
    }

    public function getProductDisplayAttribute(): string
    {
        $product = $this->product;
        if ($product) {
            $name = is_array($product->name)
                ? \App\Support\Localization::value($product->name)
                : $product->name;

            return $name . ($product->sku ? ' (' . $product->sku . ')' : '');
        }

        return $this->shop_product_id ? '#' . $this->shop_product_id : '—';
    }

    public function getWarehouseDisplayAttribute(): string
    {
        if ($this->warehouse) {
            return $this->warehouse->name;
        }

        return $this->warehouse_id ? '#' . $this->warehouse_id : '—';
    }

    public function getFormattedQuantityAttribute(): string
    {
        $qty = (int) $this->quantity_changed;
        if ($qty > 0) {
            return '+' . number_format($qty);
        }

        return number_format($qty);
    }
}
