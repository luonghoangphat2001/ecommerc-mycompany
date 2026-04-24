<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('custom.custom_js', '');
        $this->migrator->add('custom.custom_css', '');
    }
    public function down(): void
    {
        $this->migrator->delete('custom.custom_js');
        $this->migrator->delete('custom.custom_css');
    }
};
