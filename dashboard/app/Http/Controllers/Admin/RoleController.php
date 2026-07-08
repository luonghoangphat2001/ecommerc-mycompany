<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return Role::class;
    }

    protected function title(): string
    {
        return 'Roles';
    }

    protected function routePrefix(): string
    {
        return 'admin.roles';
    }

    protected function searchable(): array
    {
        return ['name', 'guard_name'];
    }

    protected function fields(): array
    {
        return [
            'name' => ['label' => 'Role', 'rules' => ['required', 'string', 'max:255']],
            'guard_name' => ['label' => 'Guard', 'rules' => ['required', 'string', 'max:50']],
            'permissions' => [
                'label' => 'Permissions',
                'type' => 'checkboxgroup',
                'rules' => ['nullable', 'array'],
                'formOnly' => true,
                'options' => Permission::orderBy('name')->pluck('name', 'name')->toArray(),
            ],
        ];
    }

    public function create(): View
    {
        return view('admin.roles.form', [
            'title' => 'Tạo mới - Roles',
            'record' => null,
            'routePrefix' => $this->routePrefix(),
            'selectedPermissions' => [],
            'permissionMatrix' => $this->permissionMatrix(),
        ]);
    }

    public function edit(int $id): View
    {
        $record = Role::findOrFail($id);

        return view('admin.roles.form', [
            'title' => 'Chỉnh sửa - Roles',
            'record' => $record,
            'routePrefix' => $this->routePrefix(),
            'selectedPermissions' => $record->permissions()->pluck('name')->toArray(),
            'permissionMatrix' => $this->permissionMatrix(),
        ]);
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        unset($data['permissions']);

        return $data;
    }

    protected function afterSave(Model $record, Request $request): void
    {
        $record->syncPermissions((array) $request->input('permissions', []));
    }

    protected function rules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'guard_name' => ['required', 'string', 'max:50'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }

    protected function formData(?Model $record = null): array
    {
        if (! $record) {
            return [];
        }

        return [
            'permissions' => $record->permissions()->pluck('name')->toArray(),
        ];
    }

    private function permissionMatrix(): array
    {
        $actions = ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'restore', 'force_delete', 'replicate', 'reorder', 'export', 'import'];
        $matrix = [];

        Permission::orderBy('name')->pluck('name')->each(function (string $permission) use (&$matrix, $actions): void {
            [$resource, $action] = $this->splitPermissionName($permission, $actions);
            $matrix[$resource][$action][] = $permission;
        });

        ksort($matrix);

        return $matrix;
    }

    private function splitPermissionName(string $permission, array $actions): array
    {
        $normalized = str_replace(['.', '-'], '_', $permission);

        foreach ($actions as $action) {
            if (str_starts_with($normalized, $action . '_')) {
                return [str($normalized)->after($action . '_')->replace('_', ' ')->headline()->toString(), $action];
            }

            if (str_ends_with($normalized, '_' . $action)) {
                return [str($normalized)->beforeLast('_' . $action)->replace('_', ' ')->headline()->toString(), $action];
            }
        }

        $parts = explode('_', $normalized, 2);

        return [str($parts[0] ?: 'Other')->replace('_', ' ')->headline()->toString(), $parts[1] ?? 'other'];
    }
}
