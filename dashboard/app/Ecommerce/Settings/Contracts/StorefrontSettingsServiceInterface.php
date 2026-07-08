<?php

namespace App\Ecommerce\Settings\Contracts;

interface StorefrontSettingsServiceInterface
{
    /**
     * Get all settings required for the storefront initialization.
     *
     * @return array<string, mixed>
     */
    public function getStorefrontSettings(): array;
}
