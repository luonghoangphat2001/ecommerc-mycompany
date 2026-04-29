<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('inventory.multi_warehouse_enabled', false);
        $this->migrator->add('inventory.split_shipping_enabled', false);
        $this->migrator->add('inventory.warehouse_selection_strategy', 'stock_volume');
        $this->migrator->add('inventory.reservation_expiry_minutes', 15);

    }

};
