<?php

namespace App\Ecommerce\CrossSell\Contracts;

use App\Ecommerce\Core\Contracts\BaseRepositoryInterface;
use Illuminate\Support\Collection;

interface CrossSellRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get active cross-sell products for a product.
     *
     * @param int $productId
     * @return Collection
     */
    public function getCrossSellsForProduct(int $productId): Collection;
}
