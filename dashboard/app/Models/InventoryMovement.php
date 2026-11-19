<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InventoryMovement extends Model
{
    protected $table = 'shop_inventory_movements';

    public function detail(): HasOne
    {
        return $this->hasOne(InventoryMovementDetail::class, 'inventory_movement_id');
    }

    public function getProductDisplayAttribute(): string
    {
        $product = $this->detail?->product;
        if ($product) {
            $name = is_array($product->name)
                ? \App\Support\Localization::value($product->name)
                : $product->name;

            return $name . ($product->sku ? ' (' . $product->sku . ')' : '');
        }

        return $this->detail?->shop_product_id ? '#' . $this->detail->shop_product_id : '—';
    }

    public function getWarehouseDisplayAttribute(): string
    {
        if ($this->detail?->warehouse) {
            return $this->detail->warehouse->name;
        }

        return $this->detail?->warehouse_id ? '#' . $this->detail->warehouse_id : '—';
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
