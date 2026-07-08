<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('webhook.enabled', false);
        $this->migrator->add('webhook.log_retention_days', 30);
        $this->migrator->add('webhook.allowed_roles', []);
    }
};
