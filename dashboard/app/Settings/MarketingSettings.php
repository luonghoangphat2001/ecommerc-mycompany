<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MarketingSettings extends Settings
{
    public bool $upsell_enabled;
    public bool $cross_sell_enabled;
    public bool $combo_enabled;

    public static function group(): string
    {
        return 'marketing';
    }
}
