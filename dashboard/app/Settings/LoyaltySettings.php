<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class LoyaltySettings extends Settings
{
    public bool $enabled;
    public int $points_per_currency;
    public int $point_conversion_rate;
    public int $spending_amount_per_point;
    public int $minimum_points_to_redeem;
    public int $max_redeem_percentage;
    public bool $exclude_sale_items;
    public array $excluded_category_ids;
    public array $excluded_product_ids;

    public static function group(): string
    {
        return 'loyalty';
    }
}
