<?php

namespace App\Services;

use App\Contracts\Repositories\MenuRepositoryInterface;

class MenuService
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
        return $this->menuRepository->all();
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
}
