<?php

namespace App\Ecommerce\Address\Services\Contracts;

interface AddressServiceInterface
{
    /**
     * Resolve the appropriate driver for a given country.
     */
    public function driver($countryCode): AddressProviderInterface;

    /**
     * Get global country list.
     */
    public function getCountries(): array;

    /**
     * Get states/provinces by country code.
     */
    public function getStates($countryCode): array;

    /**
     * Get districts/regions by country and state.
     */
    public function getRegions($countryCode, ?string $stateId): array;

    /**
     * Get wards/sub-regions by country, state and region.
     */
    public function getSubRegions($countryCode, ?string $stateId, ?string $regionId): array;
}
