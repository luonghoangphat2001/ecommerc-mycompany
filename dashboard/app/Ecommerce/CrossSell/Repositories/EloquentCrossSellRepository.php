<?php

namespace App\Ecommerce\CrossSell\Repositories;

use App\Ecommerce\Core\Repositories\BaseRepository;
use App\Ecommerce\Core\Repositories\Concerns\LoadsRelatedProducts;
use App\Ecommerce\CrossSell\Contracts\CrossSellRepositoryInterface;
use App\Models\CrossSellProduct;
use Illuminate\Support\Collection;

class EloquentCrossSellRepository extends BaseRepository implements CrossSellRepositoryInterface
{
    use LoadsRelatedProducts;

    /**
     * EloquentCrossSellRepository constructor.
     *
     * @param  mixed  $model
     */
    public function __construct($model = null)
    {
        // CrossSell doesn't have a single model, so we skip parent constructor
    }

    /**
     * {@inheritDoc}
     */
    public function getCrossSellsForProduct(int $productId): Collection
    {
        return $this->relatedProducts(CrossSellProduct::class, 'crossSellProduct', $productId);
    }
}
