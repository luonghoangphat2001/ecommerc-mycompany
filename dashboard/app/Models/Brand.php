<?php

namespace App\Models;

use App\Traits\HasLocalizedDisplayName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

/**
 *     schema="Brand",
 *     title="Brand",
 *     description="User who created the post",
 *     type="object",
 *     required={"id", "name", "description"},
 *
 * )
 */
class Brand extends Model implements HasMedia
{
    use HasFactory;
    use HasLocalizedDisplayName;
    use HasTranslations;
    use InteractsWithMedia;

    /**
     * @var string
     */
    public $translatable = [
        'name',
        'description',
    ];

    protected $table = 'shop_brands';

    /**
     * @var array<string, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'website',
        'description',
        'is_visible',
        'seo_title',
        'seo_description',
        'sort',
        'parent_id',
        'order_column',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Brand::class, 'parent_id');
    }

    public function directProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'shop_brand_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->localizedDisplayName('Thương hiệu');
    }

    public function getBreadcrumbsAttribute(): string
    {
        return $this->parentBreadcrumbs();
    }

    /** @return MorphToMany<Address> */
    public function addresses(): MorphToMany
    {
        return $this->morphToMany(Address::class, 'addressable', 'addressables');
    }

    /** @return BelongsToMany<Product> */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'shop_brand_product', 'shop_brand_id', 'shop_product_id');
    }
}
