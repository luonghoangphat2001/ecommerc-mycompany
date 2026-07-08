<?php

namespace App\Ecommerce\Product\Repositories;

use App\Ecommerce\Product\Contracts\BrandRepositoryInterface;
use App\Models\Brand;
use App\Ecommerce\Core\Repositories\BaseRepository;

class EloquentBrandRepository extends BaseRepository implements BrandRepositoryInterface
{
    /**
     * EloquentBrandRepository constructor.
     *
     * @param Brand $model
     */
    public function __construct(Brand $model)
    {
        parent::__construct($model);
    }
}
