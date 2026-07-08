<?php

namespace App\Ecommerce\Settings\Contracts;

interface ShopSettingServiceInterface
{
    /**
     * Update all shop settings groups.
     *
     * @param array $data
     * @return void
     */
    public function updateAllSettings(array $data): void;

    /**
     * Update general shop settings.
     *
     * @param array $data
     * @return void
     */
    public function updateGeneralSettings(array $data): void;

    /**
     * Get localization information for a country.
     *
     * @param string $countryCode
     * @return array
     */
    public function getLocalizationInfo(string $countryCode): array;
}
