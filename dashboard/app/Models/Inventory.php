<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Inventory extends Model
{
    protected $table = 'shop_inventories';

    protected $fillable = [
        'name',
        'slug',
        'location',
        'is_active',
    ];

    /**
     * Relate distributed product stocks.
     *
     * @return BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'shop_product_inventory_stocks', 'warehouse_id', 'shop_product_id')
            ->withPivot('stock_quantity')
            ->withTimestamps();
    }
}
