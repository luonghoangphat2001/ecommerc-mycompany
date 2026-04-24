<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ProductSettings extends Settings
{
    public string $add_to_cart_behavior; // 'ajax' or 'redirect'
    public bool $enable_reviews;
    public bool $guest_reviews_allowed;
    public bool $review_stars_required;

    public static function group(): string
    {
        return 'products';
    }
}
