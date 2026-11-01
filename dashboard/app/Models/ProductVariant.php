<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class ProductVariant extends Model
{
    use HasFactory;

    protected $table = 'shop_product_variants';

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'sale_price',
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
        'cost' => 'integer',
        'qty' => 'integer',
        'attributes' => 'array',
        'dimensions' => 'array',
        'is_active' => 'boolean',
        'weight' => 'float',
    ];

    protected static function booted(): void
    {
        static::updating(function (ProductVariant $variant) {
            if ($variant->isDirty('price') || $variant->isDirty('sale_price')) {
                try {
                    app(\App\Ecommerce\Audit\Contracts\FinancialAuditServiceInterface::class)->logVariantPriceChange(
                        $variant,
                        (float) ($variant->getOriginal('price') ?? 0),
                        (float) ($variant->price ?? 0),
                        $variant->getOriginal('sale_price') !== null ? (float) $variant->getOriginal('sale_price') : null,
                        $variant->sale_price !== null ? (float) $variant->sale_price : null
                    );
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        });
    }

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
            !is_null($this->sale_price) &&
            $this->sale_price > 0 &&
            $this->sale_price < $this->price
        ) {
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
        $stockSum = DB::table('shop_product_inventory_stocks')
            ->where('variant_id', $this->id)
            ->sum('stock_quantity');

        if ($stockSum !== null && (int) $stockSum > 0) {
            return (int) $stockSum;
        }

        return (int) $this->qty;
    }

    public function getResolvedTaxClassIdAttribute(): ?int
    {
        return $this->tax_class_id ?? $this->product?->tax_class_id;
    }
}
