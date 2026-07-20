<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

use Illuminate\Database\Eloquent\Builder;

class UserController extends BaseCrudController
{
    public function index(Request $request): \Illuminate\View\View
    {
        $modelClass = $this->modelClass();
        $query = $modelClass::query();
        $query = $this->indexQuery($query, $request);
        $this->applySearch($query, $request);

        return view('admin.users.index', [
            'title' => __($this->title()),
            'items' => $query->latest('id')->paginate(15)->withQueryString(),
            'fields' => $this->visibleFields('index'),
            'routePrefix' => $this->routePrefix(),
            'canCreate' => $this->canCreate(),
            'canEdit' => $this->canEdit(),
            'canDelete' => $this->canDelete(),
            'canImportExport' => $this->canImportExport(),
            'headerActions' => $this->headerActions(),
        ]);
    }

    protected function indexQuery(Builder $query, Request $request): Builder
    {
        if ($role = $request->query('role')) {
            $query->whereHas('roles', function($q) use ($role) {
                $q->where('name', $role);
            });
        }

        if ($date = $request->query('date')) {
            $query->whereDate('created_at', $date);
        }

        if ($departmentId = $request->query('department_id')) {
            $query->where('department_id', $departmentId);
        }

        return $query;
    }
    protected function modelClass(): string
    {
        return User::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.users';
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
            'department_id' => [
                'label' => 'Phòng ban',
                'type' => 'select',
                'rules' => ['nullable', 'exists:departments,id'],
                'options' => ['' => '-- Không có --'] + \App\Models\Department::pluck('name', 'id')->toArray(),
            ],
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
            'department_id' => ['nullable', 'exists:departments,id'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'addresses' => ['nullable', 'array'],
        ];
    }

    public function edit(int $id): \Illuminate\View\View
    {
        $user = User::with(['addresses', 'payments', 'roles', 'orders'])->findOrFail($id);
        
        return view('admin.users.edit', [
            'title' => 'Chỉnh sửa thành viên: ' . $user->name,
            'user' => $user,
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function update(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $user = User::findOrFail($id);
        
        $data = $request->validate($this->rules($id));

        $customerService = app(\App\Ecommerce\Customer\Contracts\CustomerServiceInterface::class);
        $addressService = app(\App\Ecommerce\Address\Contracts\AddressBookServiceInterface::class);

        // Extract base user data
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'department_id' => $data['department_id'] ?? null,
        ];

        if (!empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        // Update user base info
        $customerService->updateCustomer($user, $userData);

        // Sync roles
        $user->syncRoles((array) $request->input('roles', []));

        // Update/Create addresses
        if ($request->has('addresses') && is_array($request->input('addresses'))) {
            $existingAddresses = $user->addresses()->pluck('id')->toArray();
            $updatedIds = [];

            foreach ($request->input('addresses') as $index => $addrData) {
                if (empty($addrData['first_name']) && empty($addrData['address_detail'])) {
                    continue; // Skip empty rows
                }

                $addressPayload = [
                    'first_name' => $addrData['first_name'] ?? null,
                    'last_name' => $addrData['last_name'] ?? null,
                    'phone' => $addrData['phone'] ?? null,
                    'email' => $addrData['email'] ?? null,
                    'address_detail' => $addrData['address_detail'] ?? null,
                    'address_line_2' => $addrData['address_line_2'] ?? null,
                    'city' => $addrData['city'] ?? null,
                    'state' => $addrData['state'] ?? null,
                    'country' => $addrData['country'] ?? null,
                    'postal_code' => $addrData['postal_code'] ?? null,
                    'type' => $addrData['type'] ?? 'shipping',
                ];

                if (!empty($addrData['id']) && in_array($addrData['id'], $existingAddresses)) {
                    // Update existing
                    $addressService->updateAddress($user->id, $addrData['id'], $addressPayload);
                    $updatedIds[] = $addrData['id'];

                    if (isset($addrData['is_default']) && $addrData['is_default'] == 1) {
                        $user->update([
                            $addrData['type'] === 'billing' ? 'default_billing_address_id' : 'default_shipping_address_id' => $addrData['id']
                        ]);
                    }
                } else {
                    // Create new
                    $newAddr = $addressService->addAddress($user->id, $addressPayload);
                    $updatedIds[] = $newAddr->id;

                    if (isset($addrData['is_default']) && $addrData['is_default'] == 1) {
                        $user->update([
                            $addrData['type'] === 'billing' ? 'default_billing_address_id' : 'default_shipping_address_id' => $newAddr->id
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.users.edit', $user->id)->with('status', 'Đã cập nhật thông tin thành viên thành công.');
    }

    public function show(int $id): \Illuminate\View\View
    {
        $user = User::with(['addresses', 'payments', 'roles', 'orders'])->findOrFail($id);

        return view('admin.users.show', [
            'title' => 'Chi tiết thành viên: ' . $user->name,
            'user' => $user,
            'routePrefix' => $this->routePrefix(),
        ]);
    }
}
