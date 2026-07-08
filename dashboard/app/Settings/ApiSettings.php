<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ApiSettings extends Settings
{
    public int $idempotency_ttl;
    public ?string $hmac_secret;
    public bool $enabled;
    public array $allowed_roles;

    public static function group(): string
    {
        return 'api';
    }
}
