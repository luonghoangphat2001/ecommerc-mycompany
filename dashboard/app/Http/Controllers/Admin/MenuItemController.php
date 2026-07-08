<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class MenuItemController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return MenuItem::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.menu_items';
    }

    protected function routePrefix(): string
    {
        return 'admin.menu-items';
    }

    protected function searchable(): array
    {
        return array_values(array_filter(['label', 'name', 'url', 'target', 'type', 'route', 'menuable_type'], fn (string $column) => Schema::hasColumn('menu_items', $column) || in_array($column, ['label', 'name', 'type'], true)));
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

        if (Schema::hasColumn('menu_items', 'route_parameters')) {
            $fields['route_parameters'] = ['label' => 'Route parameters JSON', 'type' => 'textarea', 'rules' => ['nullable', 'string']];
        }

        if (Schema::hasColumn('menu_items', 'menuable_type')) {
            $fields['menuable_type'] = ['label' => 'Menuable type', 'rules' => ['nullable', 'string', 'max:255']];
        }

        if (Schema::hasColumn('menu_items', 'menuable_id')) {
            $fields['menuable_id'] = ['label' => 'Menuable ID', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']];
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

        if (Schema::hasColumn('menu_items', 'use_menuable_name')) {
            $fields['use_menuable_name'] = ['label' => 'Use menuable name', 'type' => 'boolean', 'rules' => ['nullable', 'boolean']];
        }

        if (Schema::hasColumn('menu_items', 'link_class')) {
            $fields['link_class'] = ['label' => 'Link class', 'rules' => ['nullable', 'string', 'max:255']];
        }

        if (Schema::hasColumn('menu_items', 'wrapper_class')) {
            $fields['wrapper_class'] = ['label' => 'Wrapper class', 'rules' => ['nullable', 'string', 'max:255']];
        }

        if (Schema::hasColumn('menu_items', 'parameters')) {
            $fields['parameters'] = ['label' => 'Parameters JSON', 'type' => 'textarea', 'rules' => ['nullable', 'string']];
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

        if (Schema::hasColumn('menu_items', 'route_parameters')) {
            $rules['route_parameters'] = ['nullable', 'string'];
        }

        if (Schema::hasColumn('menu_items', 'menuable_type')) {
            $rules['menuable_type'] = ['nullable', 'string', 'max:255'];
        }

        if (Schema::hasColumn('menu_items', 'menuable_id')) {
            $rules['menuable_id'] = ['nullable', 'integer', 'min:0'];
        }

        if (Schema::hasColumn('menu_items', 'use_menuable_name')) {
            $rules['use_menuable_name'] = ['nullable', 'boolean'];
        }

        if (Schema::hasColumn('menu_items', 'link_class')) {
            $rules['link_class'] = ['nullable', 'string', 'max:255'];
        }

        if (Schema::hasColumn('menu_items', 'wrapper_class')) {
            $rules['wrapper_class'] = ['nullable', 'string', 'max:255'];
        }

        if (Schema::hasColumn('menu_items', 'parameters')) {
            $rules['parameters'] = ['nullable', 'string'];
        }

        return $rules;
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        if (array_key_exists('parent_id', $data) && empty($data['parent_id'])) {
            $data['parent_id'] = null;
        }

        foreach (['route_parameters', 'parameters'] as $jsonField) {
            if (array_key_exists($jsonField, $data) && is_string($data[$jsonField]) && trim($data[$jsonField]) !== '') {
                $decoded = json_decode($data[$jsonField], true);
                $data[$jsonField] = json_last_error() === JSON_ERROR_NONE ? $decoded : $data[$jsonField];
            }
        }

        foreach (array_keys($data) as $key) {
            if (! Schema::hasColumn('menu_items', $key)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    protected function afterSave(Model $record, \Illuminate\Http\Request $request): void
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
