<?php

namespace App\Ecommerce\Combo\Contracts;

use Illuminate\Support\Collection;

interface ComboServiceInterface
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

    /**
     * Check if combo marketing is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool;
}
