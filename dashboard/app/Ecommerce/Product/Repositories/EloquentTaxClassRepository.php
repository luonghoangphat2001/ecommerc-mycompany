<?php

namespace App\Ecommerce\Product\Repositories;

use App\Ecommerce\Product\Contracts\TaxClassRepositoryInterface;
use App\Models\TaxClass;
use App\Ecommerce\Core\Repositories\BaseRepository;

class EloquentTaxClassRepository extends BaseRepository implements TaxClassRepositoryInterface
{
    /**
     * EloquentTaxClassRepository constructor.
     *
     * @param TaxClass $model
     */
    public function __construct(TaxClass $model)
    {
        parent::__construct($model);
    }
}
