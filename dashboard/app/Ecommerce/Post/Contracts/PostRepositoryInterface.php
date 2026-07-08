<?php

namespace App\Ecommerce\Post\Contracts;

use App\Ecommerce\Core\Repositories\BaseRepository;

use App\Ecommerce\Core\Contracts\BaseRepositoryInterface;
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
