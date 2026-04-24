<?php

namespace App\Services\Address\Drivers;

use App\Services\Address\Contracts\AddressProviderInterface;

class FallbackAddressDriver implements AddressProviderInterface
{
    public function getStates(): array
    {
        return [];
    }

    public function getRegions(string $stateId): array
    {
        return [];
    }

    public function getSubRegions(string $stateId, string $regionId): array
    {
        return [];
    }
}
