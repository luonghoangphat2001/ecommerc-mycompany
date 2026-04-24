<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('api.idempotency_ttl', 86400);
        $this->migrator->add('api.hmac_secret', '');
        $this->migrator->add('api.enabled', true);
        $this->migrator->add('api.allowed_roles', []);
    }
};
