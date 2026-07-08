<?php

namespace App\Ecommerce\Post\Contracts;

use App\Ecommerce\Core\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

interface PostCategoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get IDs of categories sorted by tree structure.
     *
     * @return array
     */
    public function getTreeSortedIds(): array;

    /**
     * Apply tree sorting to the query.
     *
     * @param Builder $query
     * @return Builder
     */
    public function applyTreeSorting(Builder $query): Builder;
}
