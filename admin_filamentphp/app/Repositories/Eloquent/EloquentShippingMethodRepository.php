<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ShippingMethodRepositoryInterface;
use App\Models\ShippingMethod;
use App\Repositories\Eloquent\BaseRepository;

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
