<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AdvancedSettings extends Settings
{
    public ?int $cart_page_id;
    public ?int $checkout_page_id;
    public ?int $account_page_id;

    public static function group(): string
    {
        return 'advanced';
    }
}
