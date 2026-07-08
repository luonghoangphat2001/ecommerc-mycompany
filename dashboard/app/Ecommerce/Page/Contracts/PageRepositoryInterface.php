<?php

namespace App\Ecommerce\Page\Contracts;

use App\Ecommerce\Core\Repositories\BaseRepository;

use App\Ecommerce\Core\Contracts\BaseRepositoryInterface;

interface PageRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find page by slug.
     *
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findBySlug(string $slug);
}
