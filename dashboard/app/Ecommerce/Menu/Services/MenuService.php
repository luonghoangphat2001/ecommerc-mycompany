<?php

namespace App\Ecommerce\Menu\Services;

use App\Ecommerce\Menu\Contracts\MenuRepositoryInterface;
use App\Ecommerce\Menu\Contracts\MenuServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MenuService implements MenuServiceInterface
{
    /**
     * @var MenuRepositoryInterface
     */
    protected $menuRepository;

    /**
     * MenuService constructor.
     */
    public function __construct(MenuRepositoryInterface $menuRepository)
    {
        $this->menuRepository = $menuRepository;
    }

    /**
     * Get all menus.
     *
     * @return Collection
     */
    public function getAllMenus()
    {
        return Cache::rememberForever('all_menus', function () {
            return $this->menuRepository->all(['*'], ['items.children']);
        });
    }

    /**
     * Get menu by slug.
     *
     * @return Model|null
     */
    public function getMenuBySlug(string $slug)
    {
        return $this->menuRepository->findBySlug($slug);
    }

    public function createMenu(array $data)
    {
        return $this->menuRepository->create($data);
    }

    public function updateMenu(int | string $id, array $data)
    {
        $this->menuRepository->update($id, $data);

        return $this->menuRepository->findOrFail($id);
    }

    public function deleteMenu(int | string $id): bool
    {
        return $this->menuRepository->delete($id);
    }
}
