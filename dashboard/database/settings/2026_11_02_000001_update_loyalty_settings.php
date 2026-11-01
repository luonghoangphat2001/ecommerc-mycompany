<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Spatie\LaravelSettings\Exceptions\SettingAlreadyExists;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $settings = [
            'loyalty.spending_amount_per_point' => 10000,
            'loyalty.minimum_points_to_redeem' => 10,
            'loyalty.max_redeem_percentage' => 50,
            'loyalty.exclude_sale_items' => false,
            'loyalty.excluded_category_ids' => [],
            'loyalty.excluded_product_ids' => [],
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
