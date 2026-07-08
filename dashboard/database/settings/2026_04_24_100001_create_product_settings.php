<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('products.add_to_cart_behavior', 'ajax');
        $this->migrator->add('products.enable_reviews', true);
        $this->migrator->add('products.guest_reviews_allowed', false);
        $this->migrator->add('products.review_stars_required', true);
    }
};
