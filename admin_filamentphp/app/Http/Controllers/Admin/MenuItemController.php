<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class MenuItemController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return MenuItem::class;
    }

    protected function title(): string
    {
        return 'Menu Items';
    }

    protected function routePrefix(): string
    {
        return 'admin.menu-items';
    }

    protected function searchable(): array
    {
        return array_values(array_filter(['label', 'name', 'url', 'target', 'type', 'route'], fn (string $column) => Schema::hasColumn('menu_items', $column)));
    }

    protected function fields(): array
    {
        $fields = [
            'menu_id' => [
                'label' => 'Menu',
                'type' => 'select',
                'rules' => ['required', 'integer', 'exists:menus,id'],
                'options' => Menu::orderBy('name')->pluck('name', 'id')->toArray(),
            ],
        ];

        if (Schema::hasColumn('menu_items', 'parent_id')) {
            $fields['parent_id'] = [
                'label' => 'Parent',
                'type' => 'select',
                'rules' => ['nullable', 'integer', 'exists:menu_items,id'],
                'options' => ['' => '-- Root --'] + MenuItem::with('menu')->orderBy('id')->get()->mapWithKeys(function (MenuItem $item) {
                    $menuName = $item->menu?->name ?: 'Menu';
                    return [$item->id => "[{$menuName}] {$item->label}"];
                })->toArray(),
            ];
        }

        if (Schema::hasColumn('menu_items', 'label')) {
            $fields['label'] = ['label' => 'Label', 'rules' => ['required', 'string', 'max:255']];
        }

        if (Schema::hasColumn('menu_items', 'name')) {
            $fields['name'] = ['label' => 'Name', 'rules' => ['required', 'string', 'max:255']];
        }

        $fields['type'] = ['label' => 'Type', 'rules' => ['required', 'string', 'max:50']];
        $fields['url'] = ['label' => 'URL', 'rules' => ['nullable', 'string', 'max:255']];

        if (Schema::hasColumn('menu_items', 'route')) {
            $fields['route'] = ['label' => 'Route', 'rules' => ['nullable', 'string', 'max:255']];
        }

        if (Schema::hasColumn('menu_items', 'target')) {
            $fields['target'] = [
                'label' => 'Target',
                'type' => 'select',
                'rules' => ['required', 'string', 'max:20'],
                'options' => ['_self' => '_self', '_blank' => '_blank'],
            ];
        }

        if (Schema::hasColumn('menu_items', 'order')) {
            $fields['order'] = ['label' => 'Order', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']];
        }

        return $fields;
    }

    protected function rules(?int $id = null): array
    {
        $rules = [
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
        ];

        if (Schema::hasColumn('menu_items', 'parent_id')) {
            $rules['parent_id'] = [
                'nullable',
                'integer',
                Rule::exists('menu_items', 'id')->where(function ($query) {
                    $query->where('menu_id', request()->input('menu_id'));
                }),
            ];
        }

        if (Schema::hasColumn('menu_items', 'label')) {
            $rules['label'] = ['required', 'string', 'max:255'];
        }

        if (Schema::hasColumn('menu_items', 'name')) {
            $rules['name'] = ['required', 'string', 'max:255'];
        }

        $rules += [
            'type' => ['required', 'string', 'max:50'],
            'url' => ['nullable', 'string', 'max:255'],
        ];

        if (Schema::hasColumn('menu_items', 'route')) {
            $rules['route'] = ['nullable', 'string', 'max:255'];
        }

        if (Schema::hasColumn('menu_items', 'target')) {
            $rules['target'] = ['required', 'string', 'max:20'];
        }

        if (Schema::hasColumn('menu_items', 'order')) {
            $rules['order'] = ['nullable', 'integer', 'min:0'];
        }

        return $rules;
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        if (array_key_exists('parent_id', $data) && empty($data['parent_id'])) {
            $data['parent_id'] = null;
        }

        foreach (array_keys($data) as $key) {
            if (! Schema::hasColumn('menu_items', $key)) {
                unset($data[$key]);
            }
        }

        return $data;
    }
}
