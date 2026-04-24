<?php

namespace App\Contracts\Services;

interface StorefrontSettingsServiceInterface
{
    /**
     * Get all settings required for the storefront initialization.
     *
     * @return array<string, mixed>
     */
    public function getStorefrontSettings(): array;
}
