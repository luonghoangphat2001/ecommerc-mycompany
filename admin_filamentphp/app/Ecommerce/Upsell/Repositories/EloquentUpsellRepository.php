<?php

namespace App\Ecommerce\Upsell\Repositories;

use App\Ecommerce\Upsell\Contracts\UpsellRepositoryInterface;
use App\Ecommerce\Core\Repositories\BaseRepository;
use App\Models\UpsellProduct;
use Illuminate\Support\Collection;

class EloquentUpsellRepository extends BaseRepository implements UpsellRepositoryInterface
{
    /**
     * EloquentUpsellRepository constructor.
     *
     * @param mixed $model
     */
    public function __construct($model = null)
    {
        // Upsell doesn't have a single model, so we skip parent constructor
    }

    /**
     * @inheritDoc
     */
    public function getUpsellsForProduct(int $productId): Collection
    {
        return collect(UpsellProduct::with('upsellProduct')
            ->where('shop_product_id', $productId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get());
    }
}
