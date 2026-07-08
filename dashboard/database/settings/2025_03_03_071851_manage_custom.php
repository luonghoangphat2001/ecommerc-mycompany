<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('maintenance.allowed_ips', '');
        $this->migrator->add('maintenance.mode', '');
    }
    public function down(): void
    {
        $this->migrator->delete('maintenance.allowed_ips');
        $this->migrator->delete('maintenance.mode');
    }
};
