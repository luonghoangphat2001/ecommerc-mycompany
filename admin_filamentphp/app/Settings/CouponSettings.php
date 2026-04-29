<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CouponSettings extends Settings
{
    public bool $enable_coupons;
    public bool $allow_multiple_coupons;
    public bool $calculate_tax_after_coupon;

    public static function group(): string
    {
        return 'coupon';
    }
}
