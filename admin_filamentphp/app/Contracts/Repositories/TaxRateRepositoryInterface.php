<?php

namespace App\Contracts\Repositories;

use App\Contracts\Repositories\BaseRepositoryInterface;

interface TaxRateRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find the best matching tax rate for a tax class and location.
     *
     * @param int|string $taxClassId
     * @param string|null $country
     * @param string|null $state
     * @param string|null $city
     * @return \App\Models\TaxRate|null
     */
    public function findMatchingRate(int|string $taxClassId, ?string $country = null, ?string $state = null, ?string $city = null);
}
