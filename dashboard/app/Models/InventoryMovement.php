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

    public function getProductDisplayAttribute(): string
    {
        if ($this->product) {
            $name = is_array($this->product->name) ? ($this->product->name[app()->getLocale()] ?? reset($this->product->name)) : $this->product->name;
            return $name . ($this->product->sku ? ' (' . $this->product->sku . ')' : '');
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
