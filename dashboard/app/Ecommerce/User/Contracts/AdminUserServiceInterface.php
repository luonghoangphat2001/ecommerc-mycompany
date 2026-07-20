<?php

namespace App\Ecommerce\User\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

interface AdminUserServiceInterface
{
    public function paginatedIndex(Request $request): LengthAwarePaginator;

    public function applyIndexFilters(Builder $query, Request $request): Builder;

    public function formFields(): array;

    public function validationRules(?int $id = null): array;

    public function mutateFormData(array $data): array;

    public function syncRoles(User $user, array $roles): void;

    public function findForAdminView(int $id): User;

    public function updateFromAdmin(User $user, array $data): User;
}
