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
}
