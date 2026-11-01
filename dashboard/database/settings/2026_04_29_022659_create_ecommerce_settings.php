<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Spatie\LaravelSettings\Exceptions\SettingAlreadyExists;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $settings = [
            'ecommerce.enabled' => true,
            'ecommerce.catalog_mode' => false,
            'ecommerce.enable_wishlist' => true,
            'ecommerce.enable_compare' => false,
            'ecommerce.order_number_prefix' => 'ORD',
            'ecommerce.low_stock_threshold' => 5,
            'ecommerce.tax_enabled' => true,
            'ecommerce.shipping_enabled' => true,
        ];

        foreach ($settings as $key => $value) {
            try {
                $this->migrator->add($key, $value);
            } catch (SettingAlreadyExists) {
                // already exists
            }
        }
    }
};
