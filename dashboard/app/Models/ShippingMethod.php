<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ShippingMethod extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $table = 'shop_shipping_methods';

    public array $translatable = ['name'];

    protected $fillable = [
        'shipping_zone_id',
        'type',
        'name',
        'settings',
        'is_enabled',
    ];

    protected $casts = [
        'type' => \App\Enums\ShippingMethodType::class,
        'settings' => 'array',
        'is_enabled' => 'boolean',
    ];

    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    public function getDescriptionAttribute(): ?string
    {
        $desc = $this->settings['description'] ?? null;
        if (is_array($desc)) {
            $locale = app()->getLocale();
            return $desc[$locale] ?? $desc['vi'] ?? $desc['en'] ?? reset($desc) ?: null;
        }

        return is_string($desc) ? $desc : null;
    }
}
