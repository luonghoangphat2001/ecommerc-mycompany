<?php

namespace App\Ecommerce\Menu\Contracts;

interface MenuServiceInterface
{
    /**
     * Get all menus.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllMenus();

    /**
     * Get menu by slug.
     *
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getMenuBySlug(string $slug);
}
