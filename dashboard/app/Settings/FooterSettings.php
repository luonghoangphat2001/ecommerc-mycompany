<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class FooterSettings extends Settings
{
    public ?string $copyright = null;
    public ?array $links = null;

    protected static array $fillable = [
        'copyright',
        'links',
    ];
    public static function group(): string
    {
        return 'footer';
    }
}
