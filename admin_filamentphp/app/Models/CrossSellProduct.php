<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrossSellProduct extends Model
{
    protected $table = 'shop_cross_sell_products';

    protected $fillable = [
        'shop_product_id',
        'cross_sell_product_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'shop_product_id');
    }

    public function crossSellProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'cross_sell_product_id');
    }
}
