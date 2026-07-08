<?php

namespace App\Ecommerce\CrossSell\Repositories;

use App\Ecommerce\CrossSell\Contracts\CrossSellRepositoryInterface;
use App\Ecommerce\Core\Repositories\BaseRepository;
use App\Models\CrossSellProduct;
use Illuminate\Support\Collection;

class EloquentCrossSellRepository extends BaseRepository implements CrossSellRepositoryInterface
{
    /**
     * EloquentCrossSellRepository constructor.
     *
     * @param mixed $model
     */
    public function __construct($model = null)
    {
        // CrossSell doesn't have a single model, so we skip parent constructor
    }

    /**
     * @inheritDoc
     */
    public function getCrossSellsForProduct(int $productId): Collection
    {
        return collect(CrossSellProduct::with('crossSellProduct')
            ->where('shop_product_id', $productId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get());
    }
}
