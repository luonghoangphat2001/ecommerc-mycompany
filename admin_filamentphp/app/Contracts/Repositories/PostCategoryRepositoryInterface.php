<?php

namespace App\Contracts\Repositories;

use App\Contracts\Repositories\BaseRepositoryInterface;

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
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applyTreeSorting(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder;
}
