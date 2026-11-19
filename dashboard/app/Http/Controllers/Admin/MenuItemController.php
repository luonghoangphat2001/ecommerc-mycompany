<?php

namespace App\Http\Controllers\Admin;

use App\Ecommerce\Menu\Services\FooterMenuSyncService;
use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class MenuItemController extends BaseCrudController
{
    public function __construct(
        private readonly FooterMenuSyncService $footerSync
    ) {
        parent::__construct();
    }

    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('admin.menus.index', $request->query());
    }

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
        return array_values(array_filter(['label', 'name', 'url', 'target', 'type', 'route', 'menuable_type'], fn (string $column) => $this->hasMenuItemColumn($column) || in_array($column, ['label', 'name', 'type'], true)));
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

        if ($this->hasMenuItemColumn('parent_id')) {
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

        if ($this->hasMenuItemColumn('title')) {
            $fields['title'] = [
                'label' => __('admin.fields.title'),
                'rules' => ['required', 'string', 'max:255'],
                'translatable' => true,
            ];
        }

        if ($this->hasMenuItemColumn('label')) {
            $fields['label'] = ['label' => __('admin.fields.label'), 'rules' => ['required', 'string', 'max:255']];
        }

        if ($this->hasMenuItemColumn('name')) {
            $fields['name'] = ['label' => __('admin.fields.name'), 'rules' => ['required', 'string', 'max:255']];
        }

        $fields['type'] = ['label' => 'Type', 'rules' => ['required', 'string', 'max:50']];
        $fields['url'] = ['label' => 'URL', 'rules' => ['nullable', 'string', 'max:255']];

        if ($this->hasMenuItemColumn('route')) {
            $fields['route'] = ['label' => 'Route', 'rules' => ['nullable', 'string', 'max:255']];
        }

        if ($this->hasMenuItemColumn('route_parameters')) {
            $fields['route_parameters'] = ['label' => 'Route parameters JSON', 'type' => 'textarea', 'rules' => ['nullable', 'string']];
        }

        if ($this->hasMenuItemColumn('menuable_type')) {
            $fields['menuable_type'] = ['label' => 'Menuable type', 'rules' => ['nullable', 'string', 'max:255']];
        }

        if ($this->hasMenuItemColumn('menuable_id')) {
            $fields['menuable_id'] = ['label' => 'Menuable ID', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']];
        }

        if ($this->hasMenuItemColumn('target')) {
            $fields['target'] = [
                'label' => 'Target',
                'type' => 'select',
                'rules' => ['required', 'string', 'max:20'],
                'options' => ['_self' => '_self', '_blank' => '_blank'],
            ];
        }

        if ($this->hasMenuItemColumn('order')) {
            $fields['order'] = ['label' => 'Order', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']];
        }

        if ($this->hasMenuItemColumn('use_menuable_name')) {
            $fields['use_menuable_name'] = ['label' => 'Use menuable name', 'type' => 'boolean', 'rules' => ['nullable', 'boolean']];
        }

        if ($this->hasMenuItemColumn('link_class')) {
            $fields['link_class'] = ['label' => 'Link class', 'rules' => ['nullable', 'string', 'max:255']];
        }

        if ($this->hasMenuItemColumn('wrapper_class')) {
            $fields['wrapper_class'] = ['label' => 'Wrapper class', 'rules' => ['nullable', 'string', 'max:255']];
        }

        if ($this->hasMenuItemColumn('parameters')) {
            $fields['parameters'] = ['label' => 'Parameters JSON', 'type' => 'textarea', 'rules' => ['nullable', 'string']];
        }

        return $fields;
    }

    protected function rules(?int $id = null): array
    {
        $rules = [
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
        ];

        if ($this->hasMenuItemColumn('parent_id')) {
            $rules['parent_id'] = [
                'nullable',
                'integer',
                Rule::exists('menu_items', 'id')->where(function ($query) {
                    $query->where('menu_id', request()->input('menu_id'));
                }),
            ];
        }

        if ($this->hasMenuItemColumn('title')) {
            if (is_array(request()->input('title'))) {
                $rules['title'] = ['required', 'array'];
                $rules['title.vi'] = ['nullable', 'string', 'max:255'];
                $rules['title.en'] = ['nullable', 'string', 'max:255'];
            } else {
                $rules['title'] = ['required', 'string', 'max:255'];
            }
        }

        if ($this->hasMenuItemColumn('label')) {
            $rules['label'] = ['required', 'string', 'max:255'];
        }

        if ($this->hasMenuItemColumn('name')) {
            $rules['name'] = ['required', 'string', 'max:255'];
        }

        $rules += [
            'type' => ['required', 'string', 'max:50'],
            'url' => ['nullable', 'string', 'max:255'],
        ];

        if ($this->hasMenuItemColumn('route')) {
            $rules['route'] = ['nullable', 'string', 'max:255'];
        }

        if ($this->hasMenuItemColumn('target')) {
            $rules['target'] = ['required', 'string', 'max:20'];
        }

        if ($this->hasMenuItemColumn('order')) {
            $rules['order'] = ['nullable', 'integer', 'min:0'];
        }

        if ($this->hasMenuItemColumn('route_parameters')) {
            $rules['route_parameters'] = ['nullable', 'string'];
        }

        if ($this->hasMenuItemColumn('menuable_type')) {
            $rules['menuable_type'] = ['nullable', 'string', 'max:255'];
        }

        if ($this->hasMenuItemColumn('menuable_id')) {
            $rules['menuable_id'] = ['nullable', 'integer', 'min:0'];
        }

        if ($this->hasMenuItemColumn('use_menuable_name')) {
            $rules['use_menuable_name'] = ['nullable', 'boolean'];
        }

        if ($this->hasMenuItemColumn('link_class')) {
            $rules['link_class'] = ['nullable', 'string', 'max:255'];
        }

        if ($this->hasMenuItemColumn('wrapper_class')) {
            $rules['wrapper_class'] = ['nullable', 'string', 'max:255'];
        }

        if ($this->hasMenuItemColumn('parameters')) {
            $rules['parameters'] = ['nullable', 'string'];
        }

        return $rules;
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $record = $this->persistMenuItem(MenuItem::make(), $data, $request);
        $menuId = $record->menu_id;

        return redirect()->route('admin.menus.index', ['menu_id' => $menuId])
            ->with('status', __('admin.messages.created'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $record = MenuItem::findOrFail($id);
        $data = $request->validate($this->rules($id));
        $record = $this->persistMenuItem($record, $data, $request);
        $menuId = $record->menu_id;

        return redirect()->route('admin.menus.index', ['menu_id' => $menuId])
            ->with('status', __('admin.messages.updated'));
    }

    private function persistMenuItem(MenuItem $record, array $data, Request $request): MenuItem
    {
        $data = $this->mutateData($data, $record->exists ? $record : null);
        $record->exists ? $record->update($data) : $record->fill($data)->save();
        $this->afterSave($record, $request);
        $this->flushStorefrontCaches();
        $this->footerSync->syncByMenuId($record->menu_id);

        return $record;
    }

    public function destroy(int $id): RedirectResponse
    {
        $record = MenuItem::findOrFail($id);
        $menuId = $record->menu_id;
        $record->delete();
        $this->flushStorefrontCaches();
        $this->footerSync->syncByMenuId($menuId);

        return redirect()->route('admin.menus.index', ['menu_id' => $menuId])
            ->with('status', __('admin.messages.deleted'));
    }

    public function reorder(Request $request)
    {
        $tree = $request->input('tree', []);
        if (empty($tree)) {
            return response()->json(['success' => true]);
        }

        $firstId = $tree[0]['id'] ?? null;
        $menuId = $firstId ? MenuItem::find($firstId)?->menu_id : null;

        $this->walkTree($tree, null, function (array $item, int $index, ?int $parentId): void {
            $record = MenuItem::find($item['id'] ?? null);
            if (! $record) {
                return;
            }

            if ($this->hasMenuItemColumn('parent_id')) {
                $record->parent_id = $parentId;
            }
            if ($this->hasMenuItemColumn('order')) {
                $record->order = $index + 1;
            }
            if ($this->hasMenuItemColumn('order_column')) {
                $record->order_column = $index + 1;
            }
            $record->save();
        });
        $this->flushStorefrontCaches();

        if ($menuId) {
            $this->footerSync->syncByMenuId($menuId);
        }

        return response()->json(['success' => true]);
    }

    protected function hasMenuItemColumn(string $column): bool
    {
        return Schema::hasColumn('menu_items', $column);
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        $data = parent::mutateData($data, $record);

        if (array_key_exists('parent_id', $data) && empty($data['parent_id'])) {
            $data['parent_id'] = null;
        }

        if (empty($data['type'])) {
            $data['type'] = 'custom';
        }

        if (empty($data['target'])) {
            $data['target'] = '_self';
        }

        if (isset($data['title']) && is_array($data['title'])) {
            $vi = trim((string) ($data['title']['vi'] ?? ''));
            $en = trim((string) ($data['title']['en'] ?? ''));
            if ($vi === '' && $en !== '') {
                $vi = $en;
            }
            if ($en === '' && $vi !== '') {
                $en = $vi;
            }
            $data['title'] = ['vi' => $vi, 'en' => $en];
        }

        foreach (['route_parameters', 'parameters'] as $jsonField) {
            if (array_key_exists($jsonField, $data) && is_string($data[$jsonField]) && trim($data[$jsonField]) !== '') {
                $decoded = json_decode($data[$jsonField], true);
                $data[$jsonField] = json_last_error() === JSON_ERROR_NONE ? $decoded : $data[$jsonField];
            }
        }

        foreach (array_keys($data) as $key) {
            if (! $this->hasMenuItemColumn($key)) {
                unset($data[$key]);
            }
        }

        return $data;
    }
}
