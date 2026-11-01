<?php

namespace App\Ecommerce\Menu\Repositories;

use App\Ecommerce\Menu\Contracts\MenuRepositoryInterface;
use App\Ecommerce\Core\Repositories\BaseRepository;
use App\Models\Menu;

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
        return $this->model->with('items.children')
            ->where(function ($query) use ($slug) {
                $query->where('handle', $slug);
                if (\Illuminate\Support\Facades\Schema::hasColumn('menus', 'slug')) {
                    $query->orWhere('slug', $slug);
                }
            })
            ->first();
    }
}
