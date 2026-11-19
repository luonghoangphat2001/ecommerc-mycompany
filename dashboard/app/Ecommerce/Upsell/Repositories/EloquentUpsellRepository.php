<?php

namespace App\Ecommerce\Upsell\Repositories;

use App\Ecommerce\Core\Repositories\BaseRepository;
use App\Ecommerce\Core\Repositories\Concerns\LoadsRelatedProducts;
use App\Ecommerce\Upsell\Contracts\UpsellRepositoryInterface;
use App\Models\UpsellProduct;
use Illuminate\Support\Collection;

class EloquentUpsellRepository extends BaseRepository implements UpsellRepositoryInterface
{
    use LoadsRelatedProducts;

    /**
     * EloquentUpsellRepository constructor.
     *
     * @param  mixed  $model
     */
    public function __construct($model = null)
    {
        // Upsell doesn't have a single model, so we skip parent constructor
    }

    /**
     * {@inheritDoc}
     */
    public function getUpsellsForProduct(int $productId): Collection
    {
        return $this->relatedProducts(UpsellProduct::class, 'upsellProduct', $productId);
    }
}
