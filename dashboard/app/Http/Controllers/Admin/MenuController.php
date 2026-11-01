<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MenuController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return Menu::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.menus';
    }

    protected function routePrefix(): string
    {
        return 'admin.menus';
    }

    protected function searchable(): array
    {
        return array_values(array_filter(['name', 'handle', 'slug'], fn (string $column) => Schema::hasColumn('menus', $column)));
    }

    protected function fields(): array
    {
        $fields = [
            'name' => ['label' => 'Name', 'rules' => ['required', 'string', 'max:255']],
        ];

        if (Schema::hasColumn('menus', 'handle')) {
            $fields['handle'] = ['label' => 'Handle', 'rules' => ['required', 'string', 'max:255']];
        }

        if (Schema::hasColumn('menus', 'slug')) {
            $fields['slug'] = ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255']];
        }

        return $fields;
    }

    public function index(Request $request): View
    {
        $menus = Menu::withCount('items')->orderBy('id')->get();

        $activeMenuId = $request->query('menu_id');
        $activeMenu = null;

        if ($activeMenuId) {
            $activeMenu = $menus->firstWhere('id', (int) $activeMenuId);
        }

        if (! $activeMenu && $menus->isNotEmpty()) {
            $activeMenu = $menus->first();
        }

        $menuItems = collect();
        $flatItems = collect();

        if ($activeMenu) {
            $orderCol = Schema::hasColumn('menu_items', 'order')
                ? 'order'
                : (Schema::hasColumn('menu_items', 'order_column') ? 'order_column' : 'id');

            $allItems = MenuItem::where('menu_id', $activeMenu->id)->orderBy($orderCol)->get();
            $flatItems = $allItems;
            $menuItems = $this->buildItemTree($allItems, null);
        }

        $pages = Page::select('id', 'title', 'slug')->get();
        $categories = ProductCategory::select('id', 'name', 'slug')->get();

        return view('admin.menus.index', [
            'title' => __($this->title()),
            'menus' => $menus,
            'activeMenu' => $activeMenu,
            'menuItems' => $menuItems,
            'flatItems' => $flatItems,
            'pages' => $pages,
            'categories' => $categories,
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function show(int $id): View
    {
        request()->merge(['menu_id' => $id]);

        return $this->index(request());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'handle' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        if (empty($data['handle']) && ! empty($data['name'])) {
            $data['handle'] = Str::slug($data['name']);
        }

        if (Schema::hasColumn('menus', 'slug')) {
            if (empty($data['slug'])) {
                $data['slug'] = $data['handle'] ?: Str::slug($data['name']);
            }
        } else {
            unset($data['slug']);
        }

        if (! Schema::hasColumn('menus', 'handle')) {
            unset($data['handle']);
        }

        $menu = Menu::create($data);
        Cache::forget('all_menus');
        $this->flushStorefrontCaches();

        return redirect()->route('admin.menus.index', ['menu_id' => $menu->id])
            ->with('status', __('admin.messages.created'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $menu = Menu::findOrFail($id);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'handle' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        if (empty($data['handle']) && ! empty($data['name'])) {
            $data['handle'] = Str::slug($data['name']);
        }

        if (Schema::hasColumn('menus', 'slug')) {
            if (empty($data['slug'])) {
                $data['slug'] = $data['handle'] ?: Str::slug($data['name']);
            }
        } else {
            unset($data['slug']);
        }

        if (! Schema::hasColumn('menus', 'handle')) {
            unset($data['handle']);
        }

        $menu->update($data);
        Cache::forget('all_menus');
        $this->flushStorefrontCaches();

        return redirect()->route('admin.menus.index', ['menu_id' => $menu->id])
            ->with('status', __('admin.messages.updated'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();
        Cache::forget('all_menus');
        $this->flushStorefrontCaches();

        return redirect()->route('admin.menus.index')
            ->with('status', __('admin.messages.deleted'));
    }

    protected function buildItemTree($items, $parentId = null)
    {
        $branch = collect();
        foreach ($items as $item) {
            if ($item->parent_id == $parentId) {
                $children = $this->buildItemTree($items, $item->id);
                $item->setRelation('children', $children);
                $branch->push($item);
            }
        }

        return $branch;
    }
}
