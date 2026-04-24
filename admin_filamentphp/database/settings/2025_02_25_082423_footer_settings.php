<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('footer.copyright', '© 2025 Company Name');
        $this->migrator->add('footer.links', []);
    }
    public function down(): void
    {
        $this->migrator->delete('footer.copyright');
        $this->migrator->delete('footer.links');
    }
};
