<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class FooterSettings extends Settings
{
    public ?string $copyright = null;
    public ?array $links = null;
    public ?string $menu_handle = 'footer-menu';

    protected static array $fillable = [
        'copyright',
        'links',
        'menu_handle',
    ];
    public static function group(): string
    {
        return 'footer';
    }
}
