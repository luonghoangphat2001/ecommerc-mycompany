<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class LoyaltySettings extends Settings
{
    public bool $enabled;
    public int $points_per_currency;
    public int $point_conversion_rate;

    public static function group(): string
    {
        return 'loyalty';
    }
}
