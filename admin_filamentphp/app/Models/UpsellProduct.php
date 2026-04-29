<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpsellProduct extends Model
{
    protected $table = 'shop_upsell_products';

    protected $fillable = [
        'shop_product_id',
        'upsell_product_id',
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

    public function upsellProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'upsell_product_id');
    }
}
