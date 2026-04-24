<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MaintenanceSettings extends Settings
{
    public ?string $allowed_ips = null;
    public ?string $mode = null;

    protected static array $fillable = [
        'allowed_ips',
        'mode',
    ];

    public static function group(): string
    {
        return 'maintenance';
    }
}
