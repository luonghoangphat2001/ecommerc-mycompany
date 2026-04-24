<?php

namespace App\Contracts\Repositories;

use App\Contracts\Repositories\BaseRepositoryInterface;

interface ShippingZoneRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find a shipping zone matching the provided geographic location.
     * 
     * @param string|null $country
     * @param string|null $state
     * @param string|null $postcode
     * @param string|null $ward
     * @return \App\Models\ShippingZone|null
     */
    public function findMatchingZone(?string $country, ?string $state = null, ?string $postcode = null, ?string $ward = null): ?\App\Models\ShippingZone;
}
