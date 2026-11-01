<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ShippingZone extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $table = 'shop_shipping_zones';

    public array $translatable = ['name'];

    protected $fillable = ['name', 'code', 'sort', 'locations', 'is_active'];
    
    protected $casts = [
        'locations' => 'array',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    public function methods()
    {
        return $this->hasMany(ShippingMethod::class, 'shipping_zone_id');
    }

    public function getFormattedLocationsAttribute(): string
    {
        $locations = $this->locations ?? [];
        if (empty($locations) || !is_array($locations)) {
            return '—';
        }

        $parts = [];
        foreach ($locations as $loc) {
            $country = strtoupper(trim($loc['country'] ?? ''));
            $countries = $loc['countries'] ?? [];
            $provinces = $loc['provinces'] ?? [];

            if ($country === '*' || $country === 'ALL') {
                $parts[] = 'Toàn cầu (Mọi quốc gia còn lại)';
            } elseif ($country === 'VN') {
                if (empty($provinces)) {
                    $parts[] = 'Việt Nam (Toàn quốc Fallback)';
                } else {
                    $count = count($provinces);
                    $firstFew = array_slice($provinces, 0, 3);
                    $more = $count > 3 ? ' và ' . ($count - 3) . ' tỉnh khác' : '';
                    $parts[] = "Việt Nam ({$count} tỉnh: " . implode(', ', $firstFew) . "{$more})";
                }
            } elseif (!empty($countries)) {
                $count = count($countries);
                $firstFew = array_slice($countries, 0, 4);
                $more = $count > 4 ? ' và ' . ($count - 4) . ' nước khác' : '';
                $parts[] = "Quốc tế ({$count} quốc gia: " . implode(', ', $firstFew) . "{$more})";
            } elseif ($country) {
                $parts[] = "Quốc gia: {$country}";
            }
        }

        return !empty($parts) ? implode('; ', $parts) : '—';
    }
}
