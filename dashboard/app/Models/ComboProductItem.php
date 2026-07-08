<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComboProductItem extends Model
{
    protected $table = 'shop_combo_product_items';

    protected $fillable = [
        'combo_product_id',
        'shop_product_id',
        'quantity',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'sort_order' => 'integer',
    ];

    public function combo(): BelongsTo
    {
        return $this->belongsTo(ComboProduct::class, 'combo_product_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'shop_product_id');
    }
}
