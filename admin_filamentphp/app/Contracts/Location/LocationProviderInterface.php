<?php

namespace App\Contracts\Location;

interface LocationProviderInterface
{
    /**
     * Get list of countries.
     * 
     * @return array [code => name]
     */
    public function getCountries(): array;

    /**
     * Get states/provinces for a country.
     * 
     * @param string|null $countryCode
     * @return array [id => name]
     */
    public function getStates(?string $countryCode): array;

    /**
     * Get cities/districts for a state.
     * 
     * @param string|null $countryCode
     * @param string|null $stateId
     * @return array [id => name]
     */
    public function getCities(?string $countryCode, ?string $stateId): array;

    /**
     * Get wards/sub-districts for a city.
     * 
     * @param string|null $countryCode
     * @param string|null $stateId
     * @param string|null $cityId
     * @return array [id => name]
     */
    public function getWards(?string $countryCode, ?string $stateId, ?string $cityId): array;
}
