<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CustomSettings extends Settings
{

    public ?string $custom_js = null;
    public ?string $custom_css = null;

    protected static array $fillable = [
        'custom_js',
        'custom_css',

    ];

    public static function group(): string
    {
        return 'custom';
    }
}
