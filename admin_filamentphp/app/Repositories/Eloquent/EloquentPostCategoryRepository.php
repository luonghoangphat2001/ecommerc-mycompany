<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\PostCategoryRepositoryInterface;
use App\Models\PostCategory;
use App\Repositories\Eloquent\BaseRepository;

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
    public function applyTreeSorting(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
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
