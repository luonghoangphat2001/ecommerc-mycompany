<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $table = 'shop_shipping_methods';

    protected $fillable = [
        'shipping_zone_id',
        'type',
        'name',
        'settings',
        'is_enabled',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_enabled' => 'boolean',
    ];

    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }
}
