<?php

namespace App\Models;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;


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
    use \App\Traits\HasWebhooks;

    /**
     * @var string
     */
    public $translatable = [
        'name',
        'description',
    ];

    protected $table = 'shop_products';

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
        'featured' => 'boolean',
        'is_visible' => 'boolean',
        'backorder' => 'boolean',
        'requires_shipping' => 'boolean',
        'published_at' => 'date',
        'product_images' => 'array',
    ];

    public function featuredImage()
    {
        return $this->belongsTo(\Awcodes\Curator\Models\Media::class, 'product_images');
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

    public function registerMediaConversions(\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
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
}
