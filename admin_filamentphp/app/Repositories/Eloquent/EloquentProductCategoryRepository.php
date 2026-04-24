<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ProductCategoryRepositoryInterface;
use App\Models\ProductCategory;
use App\Repositories\Eloquent\BaseRepository;

class EloquentProductCategoryRepository extends BaseRepository implements ProductCategoryRepositoryInterface
{
    /**
     * EloquentProductCategoryRepository constructor.
     *
     * @param ProductCategory $model
     */
    public function __construct(ProductCategory $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function getTreeSortedIds(): array
    {
        $categories = $this->model->all();
        
        $buildTree = function ($parentId = null) use (&$buildTree, $categories) {
            $ids = [];
            foreach ($categories->where('parent_id', $parentId)->sortBy('sort') as $category) {
                $ids[] = $category->id;
                $ids = array_merge($ids, $buildTree($category->id));
            }
            return $ids;
        };
        
        return $buildTree(null);
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
