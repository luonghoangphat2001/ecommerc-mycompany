<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Models\Brand;
use App\Repositories\Eloquent\BaseRepository;

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
