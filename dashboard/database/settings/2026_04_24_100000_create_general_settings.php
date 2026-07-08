<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.store_name', 'My E-commerce Store');
        $this->migrator->add('general.store_email', 'admin@example.com');
        $this->migrator->add('general.store_phone', '0123456789');
        $this->migrator->add('general.store_country', 'VN');
        $this->migrator->add('general.default_currency', 'VND');
        $this->migrator->add('general.currency_symbol', '₫');
        $this->migrator->add('general.currency_position', 'right_space');
        $this->migrator->add('general.thousand_separator', '.');
        $this->migrator->add('general.decimal_separator', ',');
        $this->migrator->add('general.decimal_places', 0);
        $this->migrator->add('general.weight_unit', 'kg');
        $this->migrator->add('general.dimension_unit', 'cm');
        $this->migrator->add('general.logo', null);
        $this->migrator->add('general.favicon', null);
    }
};
