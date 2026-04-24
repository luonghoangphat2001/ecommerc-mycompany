<?php

namespace App\Contracts\Repositories;

use App\Contracts\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Scope query to rooms.
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopeRooms(Builder $builder): Builder;

    /**
     * Scope query to tours.
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopeTours(Builder $builder): Builder;

    /**
     * Search products by name.
     *
     * @param string $term
     * @param string|null $type
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function searchByName(string $term, ?string $type = null, int $limit = 50): \Illuminate\Support\Collection;

    /**
     * Get count of products with stock below security levels.
     *
     * @return int
     */
    public function getLowStockCount(): int;
}
