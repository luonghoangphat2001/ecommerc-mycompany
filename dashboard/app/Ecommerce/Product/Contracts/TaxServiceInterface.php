<?php

namespace App\Ecommerce\Product\Contracts;

use App\Models\TaxClass;

interface TaxServiceInterface
{
    /**
     * Calculate tax for a given amount and tax class.
     * 
     * @param TaxClass|int|string $taxClass
     * @param int $amount
     * @param string|null $country
     * @return array
     */
    public function calculate($taxClass, int $amount, ?string $country = null): array;

    /**
     * Get query for TaxClass table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getTaxClassTableQuery(): \Illuminate\Database\Eloquent\Builder;
}
