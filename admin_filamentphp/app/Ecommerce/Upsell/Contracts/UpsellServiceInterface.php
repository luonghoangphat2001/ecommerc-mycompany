<?php

namespace App\Ecommerce\Upsell\Contracts;

use Illuminate\Support\Collection;

interface UpsellServiceInterface
{
    /**
     * Get upsell products for a product.
     *
     * @param int $productId
     * @return Collection
     */
    public function getUpsellsForProduct(int $productId): Collection;

    /**
     * Check if upsell marketing is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool;
}
