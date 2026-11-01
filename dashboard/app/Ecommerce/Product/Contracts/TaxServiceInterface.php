<?php

namespace App\Ecommerce\Product\Contracts;

use App\Models\Product;
use App\Models\TaxClass;

interface TaxServiceInterface
{
    /**
     * Calculate tax for a given amount and tax class.
     * 
     * @param TaxClass|int|string|null $taxClass
     * @param int $amount
     * @param string|null $country
     * @param string|null $state
     * @param string|null $city
     * @return array{amount: int, rate_name: string, rate_percent: float, tax_rate_id: ?int}
     */
    public function calculate($taxClass, int $amount, ?string $country = null, ?string $state = null, ?string $city = null): array;

    /**
     * Authoritatively calculate tax for a product (Simple, Variable, or Combo).
     *
     * @param Product|int $product
     * @param int|null $variantId
     * @param int $amount
     * @param string|null $country
     * @param string|null $state
     * @param string|null $city
     * @return array{amount: int, rate_name: string, rate_percent: float, tax_rate_id: ?int, breakdown: array}
     */
    public function calculateProductTax(Product|int $product, ?int $variantId, int $amount, ?string $country = null, ?string $state = null, ?string $city = null): array;

    /**
     * Get query for TaxClass table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getTaxClassTableQuery(): \Illuminate\Database\Eloquent\Builder;
}
