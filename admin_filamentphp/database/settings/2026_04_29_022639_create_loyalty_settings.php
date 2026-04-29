<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('loyalty.enabled', false);
        $this->migrator->add('loyalty.points_per_currency', 1);
        $this->migrator->add('loyalty.point_conversion_rate', 1000);
    }

};
