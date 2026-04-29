<?php

namespace App\Ecommerce\Upsell\Contracts;

use App\Ecommerce\Core\Contracts\BaseRepositoryInterface;
use Illuminate\Support\Collection;

interface UpsellRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get active upsell products for a product.
     *
     * @param int $productId
     * @return Collection
     */
    public function getUpsellsForProduct(int $productId): Collection;
}
