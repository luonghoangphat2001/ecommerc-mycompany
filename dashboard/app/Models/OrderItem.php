<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'shop_order_items';

    protected $fillable = [
        'order_id',
        'shop_product_id',
        'variant_id',
        'name',
        'sku',
        'variant_attributes',
        'qty',
        'unit_price',
        'original_price',
        'sale_price',
        'discount_amount',
        'total',
        'metadata',
        'sort',
    ];

    protected $casts = [
        'metadata' => 'array',
        'variant_attributes' => 'array',
        'unit_price' => 'integer',
        'original_price' => 'integer',
        'sale_price' => 'integer',
        'discount_amount' => 'integer',
        'total' => 'integer',
        'qty' => 'integer',
        'variant_id' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'shop_product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /** @return HasMany<OrderTax> */
    public function taxes(): HasMany
    {
        return $this->hasMany(OrderTax::class, 'shop_order_item_id');
    }

    public function inventoryMovements(): MorphMany
    {
        return $this->morphMany(InventoryMovement::class, 'source');
    }
}
