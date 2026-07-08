<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('coupon.enable_coupons', true);
        $this->migrator->add('coupon.allow_multiple_coupons', false);
        $this->migrator->add('coupon.calculate_tax_after_coupon', true);
    }
};
