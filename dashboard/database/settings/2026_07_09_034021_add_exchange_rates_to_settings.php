<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('settings.exchange_rates', ['VND' => 1, 'USD' => 25000]);
    }

    public function down(): void
    {
        $this->migrator->delete('settings.exchange_rates');
    }
};
