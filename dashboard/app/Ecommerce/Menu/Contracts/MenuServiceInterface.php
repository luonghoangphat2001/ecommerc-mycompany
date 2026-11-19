<?php

namespace App\Ecommerce\Menu\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface MenuServiceInterface
{
    /**
     * Get all menus.
     *
     * @return Collection
     */
    public function getAllMenus();

    /**
     * Get menu by slug.
     *
     * @return Model|null
     */
    public function getMenuBySlug(string $slug);

    public function createMenu(array $data);

    public function updateMenu(int | string $id, array $data);

    public function deleteMenu(int | string $id): bool;
}
