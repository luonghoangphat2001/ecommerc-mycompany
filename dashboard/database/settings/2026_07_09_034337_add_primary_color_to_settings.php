<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('settings.primary_color', '#4f46e5');
    }

    public function down(): void
    {
        $this->migrator->delete('settings.primary_color');
    }
};
