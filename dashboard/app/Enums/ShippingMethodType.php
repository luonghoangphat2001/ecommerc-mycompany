<?php

namespace App\Enums;

enum ShippingMethodType: string
{
    case FlatRate = 'flat_rate';
    case FreeShipping = 'free_shipping';
    case LocalPickup = 'local_pickup';

    public function label(): string
    {
        return match ($this) {
            self::FlatRate => 'Flat rate',
            self::FreeShipping => 'Free shipping',
            self::LocalPickup => 'Local pickup',
        };
    }
}
