<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('marketing.upsell_enabled', false);
        $this->migrator->add('marketing.cross_sell_enabled', false);
        $this->migrator->add('marketing.combo_enabled', false);
    }

};
