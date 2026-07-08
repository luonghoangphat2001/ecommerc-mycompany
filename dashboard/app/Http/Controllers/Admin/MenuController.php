<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Menu;
use Illuminate\Support\Facades\Schema;

class MenuController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return Menu::class;
    }

    protected function title(): string
    {
        return 'Menus';
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
}
