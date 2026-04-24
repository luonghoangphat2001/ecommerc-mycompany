<?php

namespace App\Contracts\Repositories;

use App\Contracts\Repositories\BaseRepositoryInterface;

interface MenuRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get menu by slug.
     *
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findBySlug(string $slug);
}
