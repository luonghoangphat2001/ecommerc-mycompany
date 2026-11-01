<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComboProductItem extends Model
{
    protected $table = 'shop_combo_product_items';

    protected $fillable = [
        'combo_product_id',
        'parent_product_id',
        'shop_product_id',
        'product_variant_id',
        'quantity',
        'sort_order',
    ];

    protected $casts = [
        'combo_product_id' => 'integer',
        'parent_product_id' => 'integer',
        'shop_product_id' => 'integer',
        'product_variant_id' => 'integer',
        'quantity' => 'integer',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (ComboProductItem $item) {
            if (empty($item->combo_product_id) && !empty($item->parent_product_id)) {
                $parent = Product::find($item->parent_product_id);
                $combo = ComboProduct::firstOrCreate(
                    ['product_id' => $item->parent_product_id],
                    [
                        'name' => $parent?->name ?? ('Combo #' . $item->parent_product_id),
                        'slug' => ($parent?->slug ?? ('combo-' . $item->parent_product_id)) . '-' . uniqid(),
                        'combo_price' => (int) ($parent?->price ?? 0),
                        'original_price' => (int) ($parent?->old_price ?? $parent?->price ?? 0),
                        'is_active' => true,
                    ]
                );
                $item->combo_product_id = $combo->id;
            }
        });
    }

    public function combo(): BelongsTo
    {
        return $this->belongsTo(ComboProduct::class, 'combo_product_id');
    }

    public function parentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'parent_product_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'shop_product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getUnitPriceAttribute(): int
    {
        if ($this->variant) {
            return (int) $this->variant->effective_price;
        }

        return (int) ($this->product?->effective_price ?? 0);
    }

    public function getSubtotalAttribute(): int
    {
        return $this->unit_price * max(1, $this->quantity);
    }
}
