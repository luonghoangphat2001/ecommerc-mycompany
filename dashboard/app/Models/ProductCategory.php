<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

/**
 *     schema="CategoryProduct",
 *     title="Category Product",
 *     description="User who created the post",
 *     type="object",
 *     required={"id", "name", "description"},
 *     
 * )
 */


class ProductCategory extends Model implements HasMedia
{
    use HasTranslations;
    use HasFactory;
    use \App\Traits\HasWebhooks;
    use InteractsWithMedia;

    /**
     * @var string
     */

    public $translatable = [
        'name',
        'description',
    ];

    protected $table = 'shop_categories';

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'sort',
        'is_visible',
        'seo_title',
        'seo_description',
        'type',
        'order_column',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_visible' => 'boolean',
    ];

    /** @return HasMany<ProductCategory> */
    public function children(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    /** @return BelongsTo<ProductCategory,self> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;
        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }
        return $depth;
    }


    /** @return BelongsToMany<Product> */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'shop_category_product', 'shop_category_id', 'shop_product_id');
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
}
