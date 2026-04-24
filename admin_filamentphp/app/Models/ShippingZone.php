<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    use HasFactory;

    protected $table = 'shop_shipping_zones';

    protected $fillable = ['name', 'sort', 'locations'];
    
    protected $casts = [
        'locations' => 'array',
    ];

    public function methods()
    {
        return $this->hasMany(ShippingMethod::class, 'shipping_zone_id');
    }
}
