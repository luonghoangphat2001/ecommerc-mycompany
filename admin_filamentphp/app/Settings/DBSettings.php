<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class DBSettings extends Settings
{
    public ?string $logo = null;
    public ?string $logo_favicon = null;
    public ?string $name = 'Admin';
    public ?string $about = null;
    public ?string $timezone = 'Asia/Ho_Chi_Minh';
    public ?string $default_language = 'Vietnamese';
    public ?string $currency = 'VND';
    public ?string $currency_symbol = 'đ';
    public ?string $new_user_role = null;
    public ?bool $send_welcome_email = null;
    public array $exchange_rates = ['VND' => 1, 'USD' => 25000];

    protected static array $fillable = [
        'logo',
        'logo_favicon',
        'name',
        'about',
        'timezone',
        'default_language',
        'currency',
        'currency_symbol',
        'new_user_role',
        'send_welcome_email',
        'exchange_rates',
    ];

    public static function group(): string
    {
        return 'settings';
    }
}
