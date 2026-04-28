<?php

namespace App\Ecommerce\Settings\Contracts;

interface SettingServiceInterface
{
    /**
     * Get all settings.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllSettings(): \Illuminate\Database\Eloquent\Collection;

    /**
     * Get a specific setting value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getSetting(string $key, mixed $default = null): mixed;

    /**
     * Update settings in bulk.
     *
     * @param array $settings
     * @return void
     */
    public function updateSettings(array $settings): void;
}
