<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class EcommerceSettings extends Settings
{
    public bool $enabled = true;
    public bool $catalog_mode = false;
    public bool $enable_wishlist = true;
    public bool $enable_compare = false;
    public string $order_number_prefix = 'ORD';
    public int $low_stock_threshold = 5;
    public bool $tax_enabled = true;
    public bool $shipping_enabled = true;

    public static function group(): string
    {
        return 'ecommerce';
    }
}

