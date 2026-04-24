<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('advanced.cart_page_id', null);
        $this->migrator->add('advanced.checkout_page_id', null);
        $this->migrator->add('advanced.account_page_id', null);
    }
};
