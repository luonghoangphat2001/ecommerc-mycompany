<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Marketing group additions (Loyalty and Coupon)
        $this->migrator->add('marketing.loyalty_enabled', false);
        $this->migrator->add('marketing.points_per_currency', 1);
        $this->migrator->add('marketing.point_conversion_rate', 1000);
        $this->migrator->add('marketing.enable_coupons', true);
        $this->migrator->add('marketing.allow_multiple_coupons', false);
        $this->migrator->add('marketing.calculate_tax_after_coupon', true);

        // Mail group additions (Emails base color and notifications)
        $this->migrator->add('mail.base_color', '#4f46e5');
        $this->migrator->add('mail.notifications', []);
    }
};
