<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return User::class;
    }

    protected function title(): string
    {
        return 'Users';
    }

    protected function routePrefix(): string
    {
        return 'admin.users';
    }

    protected function searchable(): array
    {
        return ['name', 'email', 'phone'];
    }

    protected function fields(): array
    {
        return [
            'name' => ['label' => 'Tên', 'rules' => ['required', 'string', 'max:255']],
            'email' => ['label' => 'Email', 'type' => 'email'],
            'phone' => ['label' => 'SĐT', 'rules' => ['nullable', 'string', 'max:50']],
            'password' => ['label' => 'Mật khẩu', 'type' => 'password', 'rules' => ['nullable', 'string', 'min:6'], 'formOnly' => true],
            'roles' => [
                'label' => 'Roles',
                'type' => 'multiselect',
                'rules' => ['nullable', 'array'],
                'options' => Role::orderBy('name')->pluck('name', 'name')->toArray(),
            ],
        ];
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        unset($data['roles']);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }

    protected function afterSave(Model $record, Request $request): void
    {
        $record->syncRoles((array) $request->input('roles', []));
    }

    protected function rules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:6'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ];
    }

    protected function formData(?Model $record = null): array
    {
        if (! $record) {
            return [];
        }

        return [
            'roles' => $record->roles()->pluck('name')->toArray(),
        ];
    }
}
