<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\MenuRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use Biostate\FilamentMenuBuilder\Models\Menu;

class EloquentMenuRepository extends BaseRepository implements MenuRepositoryInterface
{
    /**
     * EloquentMenuRepository constructor.
     *
     * @param Menu $model
     */
    public function __construct(Menu $model)
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
