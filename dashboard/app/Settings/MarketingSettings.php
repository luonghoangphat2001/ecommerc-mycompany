<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MarketingSettings extends Settings
{
    public bool $upsell_enabled;
    public bool $cross_sell_enabled;
    public bool $combo_enabled;
    
    // Loyalty
    public bool $loyalty_enabled;
    public int $points_per_currency;
    public int $point_conversion_rate;
    
    // Coupon
    public bool $enable_coupons;
    public bool $allow_multiple_coupons;
    public bool $calculate_tax_after_coupon;

    public static function group(): string
    {
        return 'marketing';
    }
}
