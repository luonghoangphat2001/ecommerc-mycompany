<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Menu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

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

    protected function indexQuery(Builder $query, \Illuminate\Http\Request $request): Builder
    {
        return $query->withCount('items');
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

        $fields['items_count'] = [
            'label' => 'Menu Items',
            'tableOnly' => true,
            'hideOnForm' => true,
            'hideOnImport' => true,
            'hideOnExport' => true,
        ];

        return $fields;
    }

    protected function headerActions(): array
    {
        return [
            [
                'label' => 'Quản lý Menu Items',
                'url' => route('admin.menu-items.index'),
                'class' => 'btn-secondary',
            ],
        ];
    }

    protected function afterSave(Model $record, Request $request): void
    {
        Cache::forget('all_menus');
    }

    public function destroy(int $id): RedirectResponse
    {
        $response = parent::destroy($id);
        Cache::forget('all_menus');

        return $response;
    }
}
