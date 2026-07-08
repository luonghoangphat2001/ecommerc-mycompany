<?php

namespace App\Ecommerce\Combo\Contracts;

use App\Ecommerce\Core\Contracts\BaseRepositoryInterface;
use Illuminate\Support\Collection;

interface ComboRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get active combo products.
     *
     * @return Collection
     */
    public function getActiveCombos(): Collection;

    /**
     * Get combo product by slug.
     *
     * @param string $slug
     * @return mixed|null
     */
    public function getComboBySlug(string $slug);
}
