<?php

namespace App\Ecommerce\Product\Repositories;

use App\Ecommerce\Product\Contracts\ProductCategoryRepositoryInterface;
use App\Models\ProductCategory;
use App\Ecommerce\Core\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

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
        return Cache::remember('product_category_tree_ids', 3600, function() {
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
        });
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
