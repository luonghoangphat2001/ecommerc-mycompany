<?php

namespace App\Ecommerce\Post\Repositories;

use App\Ecommerce\Post\Contracts\PostCategoryRepositoryInterface;
use App\Models\PostCategory;
use App\Ecommerce\Core\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class EloquentPostCategoryRepository extends BaseRepository implements PostCategoryRepositoryInterface
{
    /**
     * EloquentPostCategoryRepository constructor.
     *
     * @param PostCategory $model
     */
    public function __construct(PostCategory $model)
    {
        parent::__construct($model);
    }

    public function getTreeSortedIds(): array
    {
        return $this->model::getTreeSortedIds();
    }

    /**
     * @inheritDoc
     */
    public function applyTreeSorting(Builder $query): Builder
    {
        if (empty($query->getQuery()->orders)) {
            $ids = $this->getTreeSortedIds();
            if (!empty($ids)) {
                $cases = [];
                foreach ($ids as $index => $id) {
                    $cases[] = "WHEN {$id} THEN {$index}";
                }
                $orderSql = "CASE id " . implode(' ', $cases) . " END";
                $query->orderByRaw($orderSql);
            }
        }
        return $query;
    }
}
