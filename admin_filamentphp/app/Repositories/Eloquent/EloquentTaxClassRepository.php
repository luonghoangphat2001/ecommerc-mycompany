<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\TaxClassRepositoryInterface;
use App\Models\TaxClass;
use App\Repositories\Eloquent\BaseRepository;

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
