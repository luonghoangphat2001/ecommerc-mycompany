<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('footer.menu_handle')) {
            $this->migrator->add('footer.menu_handle', 'footer-menu');
        }
    }

    public function down(): void
    {
        if ($this->migrator->exists('footer.menu_handle')) {
            $this->migrator->delete('footer.menu_handle');
        }
    }
};
