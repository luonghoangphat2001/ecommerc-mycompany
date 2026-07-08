<?php

namespace App\Ecommerce\CrossSell\Contracts;

use Illuminate\Support\Collection;

interface CrossSellServiceInterface
{
    /**
     * Get cross-sell products for a product.
     *
     * @param int $productId
     * @return Collection
     */
    public function getCrossSellsForProduct(int $productId): Collection;

    /**
     * Check if cross-sell marketing is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool;
}
