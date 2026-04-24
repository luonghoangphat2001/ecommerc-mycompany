<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\PageRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use Z3d0X\FilamentFabricator\Models\Page;

class EloquentPageRepository extends BaseRepository implements PageRepositoryInterface
{
    /**
     * EloquentPageRepository constructor.
     *
     * @param Page $model
     */
    public function __construct(Page $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function findBySlug(string $slug)
    {
        return $this->model->where('slug', $slug)->first();
    }
}
