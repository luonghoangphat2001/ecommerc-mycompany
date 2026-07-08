<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use Spatie\Permission\Models\Permission;

class PermissionController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return Permission::class;
    }

    protected function title(): string
    {
        return 'Permissions';
    }

    protected function routePrefix(): string
    {
        return 'admin.permissions';
    }

    protected function searchable(): array
    {
        return ['name', 'guard_name'];
    }

    protected function fields(): array
    {
        return [
            'name' => ['label' => 'Permission', 'rules' => ['required', 'string', 'max:255']],
            'guard_name' => ['label' => 'Guard', 'rules' => ['required', 'string', 'max:50']],
        ];
    }
}
