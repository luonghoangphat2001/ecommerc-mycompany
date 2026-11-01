<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Attribute extends Model
{
    use HasFactory;

    protected $table = 'shop_attributes';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'values',
        'is_enabled',
    ];

    protected $casts = [
        'values' => 'array',
        'is_enabled' => 'boolean',
    ];

    public function productAttributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class, 'attribute_id');
    }

    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(Product::class, ProductAttribute::class, 'attribute_id', 'id', 'id', 'product_id');
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Merge new values into the attribute's existing values safely without duplicates.
     */
    public function mergeValues(array $newValues): bool
    {
        $existing = is_array($this->values) ? $this->values : [];
        $cleaned = array_values(array_filter(array_map('trim', $newValues), fn ($v) => $v !== ''));

        $merged = array_values(array_unique(array_merge($existing, $cleaned)));

        if ($merged !== $existing) {
            $this->values = $merged;
            return $this->save();
        }

        return false;
    }
}
