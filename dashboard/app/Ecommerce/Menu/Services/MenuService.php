<?php

namespace App\Ecommerce\Menu\Services;

use App\Ecommerce\Menu\Contracts\MenuRepositoryInterface;
use App\Ecommerce\Menu\Contracts\MenuServiceInterface;

class MenuService implements MenuServiceInterface
{
    /**
     * @var MenuRepositoryInterface
     */
    protected $menuRepository;

    /**
     * MenuService constructor.
     *
     * @param MenuRepositoryInterface $menuRepository
     */
    public function __construct(MenuRepositoryInterface $menuRepository)
    {
        $this->menuRepository = $menuRepository;
    }

    /**
     * Get all menus.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllMenus()
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('all_menus', function () {
            return $this->menuRepository->all(['*'], ['items.children']);
        });
    }

    /**
     * Get menu by slug.
     *
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getMenuBySlug(string $slug)
    {
        return $this->menuRepository->findBySlug($slug);
    }

    /**
     * Get menu by handle.
     *
     * @param string $handle
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getMenuByHandle(string $handle)
    {
        return \App\Models\Menu::where('handle', $handle)->with('items.children')->first();
    }
}
