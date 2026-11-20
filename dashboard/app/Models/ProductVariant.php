<?php

namespace App\Models;

use App\Traits\HasInventoryStock;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    use HasFactory;
    use HasInventoryStock;

    protected $table = 'shop_product_variants';

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'sale_price',
        'sale_start_date',
        'sale_end_date',
        'cost',
        'qty',
        'attributes',
        'image',
        'weight',
        'dimensions',
        'is_active',
        'tax_class_id',
    ];

    protected $casts = [
        'price' => 'integer',
        'sale_price' => 'integer',
        'sale_start_date' => 'datetime',
        'sale_end_date' => 'datetime',
        'cost' => 'integer',
        'qty' => 'integer',
        'attributes' => 'array',
        'dimensions' => 'array',
        'is_active' => 'boolean',
        'weight' => 'float',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function taxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class, 'tax_class_id');
    }

    public function inventories(): BelongsToMany
    {
        return $this->belongsToMany(Inventory::class, 'shop_product_inventory_stocks', 'variant_id', 'warehouse_id')
            ->withPivot('stock_quantity')
            ->withTimestamps();
    }

    public function getEffectivePriceAttribute(): int
    {
        if (
            ! is_null($this->sale_price) &&
            $this->sale_price > 0 &&
            $this->sale_price < $this->price
        ) {
            $now = now();
            if ($this->sale_start_date && $now->lt($this->sale_start_date)) {
                return (int) $this->price;
            }
            if ($this->sale_end_date && $now->gt($this->sale_end_date)) {
                return (int) $this->price;
            }

            return (int) $this->sale_price;
        }

        return (int) $this->price;
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->effective_price < $this->price;
    }

    public function getAvailableStockAttribute(): int
    {
        $stockTotal = $this->inventoryStockTotalIfPresent('variant_id');
        if ($stockTotal !== null) {
            return $stockTotal;
        }

        return (int) $this->qty;
    }

    public function getResolvedTaxClassIdAttribute(): ?int
    {
        return $this->tax_class_id ?? $this->product?->tax_class_id;
    }
}
