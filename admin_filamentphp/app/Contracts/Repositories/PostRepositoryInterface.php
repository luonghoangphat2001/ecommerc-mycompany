<?php

namespace App\Contracts\Repositories;

use App\Contracts\Repositories\BaseRepositoryInterface;
use App\Models\Post;

interface PostRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get posts by category slug.
     *
     * @param string $categorySlug
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByCategorySlug(string $categorySlug, int $perPage = 10);
}
