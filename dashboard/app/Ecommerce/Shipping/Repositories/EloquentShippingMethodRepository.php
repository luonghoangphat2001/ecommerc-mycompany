<?php

namespace App\Ecommerce\Shipping\Repositories;

use App\Ecommerce\Shipping\Contracts\ShippingMethodRepositoryInterface;
use App\Models\ShippingMethod;
use App\Ecommerce\Core\Repositories\BaseRepository;

class EloquentShippingMethodRepository extends BaseRepository implements ShippingMethodRepositoryInterface
{
    /**
     * EloquentShippingMethodRepository constructor.
     *
     * @param ShippingMethod $model
     */
    public function __construct(ShippingMethod $model)
    {
        parent::__construct($model);
    }
}
