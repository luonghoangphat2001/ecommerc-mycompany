<?php
namespace App\Settings;
use Spatie\LaravelSettings\Settings;
class GeneralSettings extends Settings
{
    public string $store_name;
    public ?string $store_email;
    public ?string $store_phone;
    public string $store_country;
    public string $default_currency;
    public string $currency_symbol;
    public string $currency_position;
    public string $thousand_separator;
    public string $decimal_separator;
    public int $decimal_places;
    public string $weight_unit;
    public string $dimension_unit;
    public static function group(): string
    {
        return 'general';
    }
}
