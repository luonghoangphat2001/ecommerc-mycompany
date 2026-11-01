<?php

namespace App\Models;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
    use HasTranslations;
    use HasFactory;
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

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Brand::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Brand::class, 'parent_id');
    }

    public function directProducts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class, 'shop_brand_id');
    }

    public function getDisplayNameAttribute(): string
    {
        $locale = app()->getLocale() ?: 'vi';
        $name = $this->getTranslation('name', $locale) ?: $this->getTranslation('name', 'vi');
        if (empty($name) && is_array($this->name)) {
            $name = $this->name['vi'] ?? $this->name['en'] ?? '';
        }
        return (string) ($name ?: ($this->slug ?: 'Thương hiệu #' . $this->id));
    }

    public function getBreadcrumbsAttribute(): string
    {
        $parts = [];
        $curr = $this->parent;
        while ($curr) {
            array_unshift($parts, $curr->display_name);
            $curr = $curr->parent;
        }
        return empty($parts) ? '—' : implode(' > ', $parts);
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
