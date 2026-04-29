<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComboProduct extends Model
{
    protected $table = 'shop_combo_products';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'combo_price',
        'original_price',
        'discount_percent',
        'is_active',
        'start_date',
        'end_date',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'combo_price' => 'integer',
        'original_price' => 'integer',
        'discount_percent' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'sort_order' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ComboProductItem::class, 'combo_product_id');
    }

    public function getTotalValueAttribute(): int
    {
        return $this->items->sum(fn($item) => $item->product?->price ?? 0);
    }
}
