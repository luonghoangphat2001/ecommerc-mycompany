<?php

namespace App\Ecommerce\Page\Repositories;

use App\Ecommerce\Page\Contracts\PageRepositoryInterface;
use App\Ecommerce\Core\Repositories\BaseRepository;
use App\Models\Page;

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
