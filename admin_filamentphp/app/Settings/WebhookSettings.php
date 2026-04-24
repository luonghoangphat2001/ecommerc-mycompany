<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WebhookSettings extends Settings
{
    public bool $enabled;
    public int $log_retention_days;
    public array $allowed_roles;

    public static function group(): string
    {
        return 'webhook';
    }
}
