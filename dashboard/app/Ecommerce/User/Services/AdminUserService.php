<?php

namespace App\Ecommerce\User\Services;

use App\Ecommerce\Address\Contracts\AddressBookServiceInterface;
use App\Ecommerce\Customer\Contracts\CustomerServiceInterface;
use App\Ecommerce\User\Contracts\AdminUserServiceInterface;
use App\Models\Department;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class AdminUserService implements AdminUserServiceInterface
{
    public function __construct(
        private readonly CustomerServiceInterface $customerService,
        private readonly AddressBookServiceInterface $addressService
    ) {
    }

    public function paginatedIndex(Request $request): LengthAwarePaginator
    {
        $query = $this->applyIndexFilters(User::query(), $request);
        $this->applySearch($query, trim((string) $request->query('q', '')));

        return $query->latest('id')->paginate(15)->withQueryString();
    }

    public function applyIndexFilters(Builder $query, Request $request): Builder
    {
        if ($role = $request->query('role')) {
            $query->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->where('name', $role));
        }

        if ($date = $request->query('date')) {
            $query->whereDate('created_at', $date);
        }

        if ($departmentId = $request->query('department_id')) {
            $query->where('department_id', $departmentId);
        }

        return $query;
    }

    public function formFields(): array
    {
        return [
            'name' => ['label' => 'Tên', 'rules' => ['required', 'string', 'max:255']],
            'email' => ['label' => 'Email', 'type' => 'email'],
            'phone' => ['label' => 'SĐT', 'rules' => ['nullable', 'string', 'max:50']],
            'password' => ['label' => 'Mật khẩu', 'type' => 'password', 'rules' => ['nullable', 'string', 'min:6'], 'formOnly' => true],
            'department_id' => [
                'label' => 'Phòng ban',
                'type' => 'select',
                'rules' => ['nullable', 'exists:departments,id'],
                'options' => ['' => '-- Không có --'] + Department::pluck('name', 'id')->toArray(),
            ],
            'roles' => [
                'label' => 'Vai trò',
                'type' => 'multiselect',
                'rules' => ['nullable', 'array'],
                'options' => Role::orderBy('name')->pluck('name', 'name')->toArray(),
            ],
        ];
    }

    public function validationRules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:6'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'addresses' => ['nullable', 'array'],
        ];
    }

    public function mutateFormData(array $data): array
    {
        unset($data['roles'], $data['addresses']);

        if (empty($data['password'])) {
            unset($data['password']);

            return $data;
        }

        $data['password'] = Hash::make($data['password']);

        return $data;
    }

    public function syncRoles(User $user, array $roles): void
    {
        $user->syncRoles($roles);
    }

    public function findForAdminView(int $id): User
    {
        return User::with(['addresses', 'payments', 'roles', 'orders'])->findOrFail($id);
    }

    public function updateFromAdmin(User $user, array $data): User
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'department_id' => $data['department_id'] ?? null,
        ];

        if (! empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        $updatedUser = $this->customerService->updateCustomer($user, $userData);
        $this->syncRoles($updatedUser, (array) ($data['roles'] ?? []));
        $this->syncAddresses($updatedUser, (array) ($data['addresses'] ?? []));

        return $updatedUser->fresh(['addresses', 'payments', 'roles', 'orders']);
    }

    private function applySearch(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $subQuery) use ($search): void {
            $subQuery->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('phone', 'like', '%' . $search . '%');
        });
    }

    private function syncAddresses(User $user, array $addresses): void
    {
        if ($addresses === []) {
            return;
        }

        $existingAddressIds = $user->addresses()->pluck('id')->toArray();

        foreach ($addresses as $addressData) {
            if (empty($addressData['first_name']) && empty($addressData['address_detail'])) {
                continue;
            }

            $payload = $this->addressPayload($addressData);
            $addressId = ! empty($addressData['id']) ? (int) $addressData['id'] : null;

            if ($addressId && in_array($addressId, $existingAddressIds, true)) {
                $this->addressService->updateAddress($user->id, $addressId, $payload);
                $this->updateDefaultAddress($user, $addressId, $addressData);
                continue;
            }

            $newAddress = $this->addressService->addAddress($user->id, $payload);
            $this->updateDefaultAddress($user, $newAddress->id, $addressData);
        }
    }

    private function addressPayload(array $addressData): array
    {
        return [
            'first_name' => $addressData['first_name'] ?? null,
            'last_name' => $addressData['last_name'] ?? null,
            'phone' => $addressData['phone'] ?? null,
            'email' => $addressData['email'] ?? null,
            'address_detail' => $addressData['address_detail'] ?? null,
            'address_line_2' => $addressData['address_line_2'] ?? null,
            'city' => $addressData['city'] ?? null,
            'state' => $addressData['state'] ?? null,
            'country' => $addressData['country'] ?? null,
            'postal_code' => $addressData['postal_code'] ?? null,
            'type' => $addressData['type'] ?? 'shipping',
        ];
    }

    private function updateDefaultAddress(User $user, int $addressId, array $addressData): void
    {
        if ((int) ($addressData['is_default'] ?? 0) !== 1) {
            return;
        }

        $column = ($addressData['type'] ?? 'shipping') === 'billing'
            ? 'default_billing_address_id'
            : 'default_shipping_address_id';

        $user->update([$column => $addressId]);
    }
}
