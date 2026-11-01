<?php

namespace App\Models;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;
use App\Traits\HasWebhooks;
use Awcodes\Curator\Models\Media;
use App\Models\Inventory;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;





/**
 *     schema="Product",
 *     title="Product",
 *     description="Product model schema",
 *     type="object",
 *     required={"id", "name", "description", "featured", "is_visible", "backorder", "requires_shipping", "published_at", "created_at"},
 * )
 */
class Product extends Model implements HasMedia
{
    use HasTranslations;
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;
    use HasWebhooks;
    use \Spatie\Tags\HasTags;


    /**
     * @var string
     */
    public $translatable = [
        'name',
        'description',
    ];

    protected $table = 'shop_products';

    protected static function booted(): void
    {
        static::updating(function (Product $product) {
            if ($product->isDirty('price') || $product->isDirty('sale_price')) {
                try {
                    app(\App\Ecommerce\Audit\Contracts\FinancialAuditServiceInterface::class)->logProductPriceChange(
                        $product,
                        (float) ($product->getOriginal('price') ?? 0),
                        (float) ($product->price ?? 0),
                        $product->getOriginal('sale_price') !== null ? (float) $product->getOriginal('sale_price') : null,
                        $product->sale_price !== null ? (float) $product->sale_price : null
                    );
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        });
    }

    protected $fillable = [
        'shop_brand_id',
        'author_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'description',
        'qty',
        'security_stock',
        'featured',
        'is_visible',
        'old_price',
        'price',
        'cost',
        'tax_class_id',
        'shipping_class_id',
        'type',
        'backorder',
        'requires_shipping',
        'published_at',
        'seo_title',
        'seo_description',
        'product_images',
        'image',
        'sale_price',
        'sale_start_date',
        'sale_end_date',
        'apply_tax',
    ];

    public function taxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class, 'tax_class_id');
    }

    public function meta()
    {
        return $this->hasMany(ProductMeta::class, 'shop_product_id');
    }

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'type' => \App\Enums\ProductType::class,
        'featured' => 'boolean',
        'is_visible' => 'boolean',
        'backorder' => 'boolean',
        'requires_shipping' => 'boolean',
        'published_at' => 'date',
        'sale_start_date' => 'datetime',
        'sale_end_date' => 'datetime',
        'product_images' => 'array',
        'apply_tax' => 'boolean',
    ];

    public function featuredImage()
    {
        return $this->belongsTo(Media::class, 'product_images');
    }

    public function getImageAttribute()
    {
        return $this->product_images; // Returns the Media ID so show_field can render it
    }

    public function setImageAttribute($value)
    {
        if (is_numeric($value)) {
            $this->attributes['product_images'] = $value;
        } elseif (is_string($value)) {
            // It's a file path, create Media record
            $media = \Awcodes\Curator\Models\Media::create([
                'disk' => 'public',
                'directory' => 'uploads',
                'name' => basename($value),
                'path' => $value,
                'size' => \Illuminate\Support\Facades\Storage::disk('public')->exists($value) ? \Illuminate\Support\Facades\Storage::disk('public')->size($value) : 0,
                'visibility' => 'public',
            ]);
            $this->attributes['product_images'] = $media->id;
        }
    }

    /**
     * Relate warehouses for localized stocks.
     *
     * @return BelongsToMany
     */
    public function inventories(): BelongsToMany
    {
        return $this->belongsToMany(Inventory::class, 'shop_product_inventory_stocks', 'shop_product_id', 'warehouse_id')
            ->withPivot('stock_quantity')
            ->withTimestamps();
    }

    /** @return BelongsTo<Brand,self> */
    public function brand(): BelongsTo

    {
        return $this->belongsTo(Brand::class, 'shop_brand_id');
    }

    /** @return BelongsToMany<Category> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'shop_category_product', 'shop_product_id', 'shop_category_id')->withTimestamps();
    }

    /** @return MorphMany<Comment> */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function productAttributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class, 'product_id')->orderBy('position');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id')->where('is_active', true);
    }

    public function comboComponents(): HasMany
    {
        return $this->hasMany(ComboProductItem::class, 'parent_product_id')->orderBy('sort_order');
    }

    public function comboProduct(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ComboProduct::class, 'product_id');
    }

    public function isSimple(): bool
    {
        return $this->type === null || $this->type->isSimple();
    }

    public function isVariable(): bool
    {
        return $this->type !== null && $this->type->isVariable();
    }

    public function isCombo(): bool
    {
        return $this->type !== null && $this->type->isCombo();
    }

    public function calculateComboStock(array $visitedIds = []): int
    {
        if (in_array($this->id, $visitedIds)) {
            return 0; // Prevent circular reference
        }

        $components = $this->relationLoaded('comboComponents')
            ? $this->comboComponents
            : $this->comboComponents()->with(['product', 'variant'])->get();

        if ($components->isEmpty()) {
            return (int) ($this->qty ?? 0);
        }

        $visitedIds[] = $this->id;
        $maxCombos = PHP_INT_MAX;

        foreach ($components as $item) {
            $requiredPerCombo = max(1, (int) $item->quantity);
            $componentStock = 0;

            if ($item->product_variant_id) {
                $variant = $item->variant ?: ProductVariant::find($item->product_variant_id);
                $componentStock = $variant ? (int) $variant->available_stock : 0;
            } elseif ($item->shop_product_id) {
                $product = $item->product ?: Product::find($item->shop_product_id);
                if ($product) {
                    if ($product->isCombo()) {
                        $componentStock = $product->calculateComboStock($visitedIds);
                    } else {
                        $componentStock = (int) $product->available_stock;
                    }
                }
            }

            $availableCombos = intdiv($componentStock, $requiredPerCombo);
            if ($availableCombos < $maxCombos) {
                $maxCombos = $availableCombos;
            }
        }

        return $maxCombos === PHP_INT_MAX ? 0 : max(0, $maxCombos);
    }

    public function getAvailableStockAttribute(): int
    {
        if ($this->isVariable()) {
            if ($this->relationLoaded('activeVariants')) {
                return (int) $this->activeVariants->sum('qty');
            }
            return (int) $this->variants()->where('is_active', true)->sum('qty');
        }

        if ($this->isCombo()) {
            return $this->calculateComboStock();
        }

        if ($this->relationLoaded('inventories')) {
            $sum = (int) $this->inventories->sum('pivot.stock_quantity');
            if ($sum > 0 || $this->inventories->isNotEmpty()) {
                return $sum;
            }
        } else {
            $stockSum = DB::table('shop_product_inventory_stocks')
                ->where('shop_product_id', $this->id)
                ->sum('stock_quantity');

            if ($stockSum !== null && (int) $stockSum > 0) {
                return (int) $stockSum;
            }
        }

        if ((int) $this->qty > 0) {
            return (int) $this->qty;
        }

        return (int) ($this->total_stock ?? 0);
    }

    public function registerMediaConversions(?SpatieMedia $media = null): void

    {
        $this->addMediaConversion('thumb')
              ->width(150)
              ->height(150)
              ->nonQueued();

        $this->addMediaConversion('large')
              ->width(800)
              ->height(800)
              ->nonQueued();
    }

    /** @return HasMany<UpsellProduct> */
    public function upsells(): HasMany
    {
        return $this->hasMany(UpsellProduct::class, 'shop_product_id');
    }

    /** @return HasMany<CrossSellProduct> */
    public function crossSells(): HasMany
    {
        return $this->hasMany(CrossSellProduct::class, 'shop_product_id');
    }

    /** @return HasMany<ComboProductItem> */
    public function comboItems(): HasMany
    {
        return $this->hasMany(ComboProductItem::class, 'shop_product_id');
    }

    public function getEffectivePriceAttribute(): int
    {
        if ($this->isVariable() && ((int)$this->price <= 0)) {
            $minPrice = $this->variants()->where('is_active', true)->min('price');
            if ($minPrice !== null && (int)$minPrice > 0) {
                return (int) $minPrice;
            }
        }

        if (
            is_null($this->sale_price) || 
            $this->sale_price < 0 || 
            $this->sale_price >= $this->price
        ) {
            return (int) ($this->price ?? 0);
        }

        $now = now();

        if ($this->sale_start_date && $this->sale_end_date) {
            if ($now->between($this->sale_start_date, $this->sale_end_date)) {
                return (int) $this->sale_price;
            }
        } elseif ($this->sale_start_date) {
            if ($now->greaterThanOrEqualTo($this->sale_start_date)) {
                return (int) $this->sale_price;
            }
        } elseif ($this->sale_end_date) {
            if ($now->lessThanOrEqualTo($this->sale_end_date)) {
                return (int) $this->sale_price;
            }
        } else {
            // No dates set, but sale_price is valid
            return (int) $this->sale_price;
        }

        return (int) $this->price;
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->effective_price < $this->price;
    }
}
