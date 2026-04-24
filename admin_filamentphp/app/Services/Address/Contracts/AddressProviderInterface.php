<?php

namespace App\Services\Address\Contracts;

interface AddressProviderInterface
{
    /**
     * Get primary states/provinces for the driver's country.
     */
    public function getStates(): array;

    /**
     * Get secondary regions/districts for a given state.
     */
    public function getRegions(string $stateId): array;

    /**
     * Get tertiary sub-regions/wards for a given state and region.
     */
    public function getSubRegions(string $stateId, string $regionId): array;
}
