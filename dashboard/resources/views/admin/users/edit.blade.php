@extends('admin.layouts.app', ['title' => $title ?? 'Chỉnh sửa thành viên'])

@section('content')
<x-admin.page-header
    :title="$title ?? 'Chỉnh sửa thành viên'"
    description="Quản lý thông tin cá nhân, địa chỉ và phân quyền thành viên"
    :backUrl="route($routePrefix . '.index')"
>
    <x-slot:actions>
        <a href="{{ route($routePrefix . '.index') }}" class="btn btn-secondary">&larr; Quay lại</a>
        <button type="submit" form="user-edit-form" class="btn btn-primary">Lưu thay đổi</button>
    </x-slot:actions>
</x-admin.page-header>

<form method="post" action="{{ route($routePrefix . '.update', $user->id) }}" id="user-edit-form">
    @csrf
    @method('PUT')

    @if ($errors->any())
        <div style="background: var(--fi-danger-bg); color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; border: 1px solid var(--fi-danger-border);">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="edit-layout">
        <!-- Left Column -->
        <div class="show-main">
            <!-- Basic Info Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thông tin cơ bản</h3>
                </div>
                <div class="card-body">
                    <div class="form-grid" style="margin-bottom: 16px;">
                        <div>
                            <label>Tên đầy đủ *</label>
                            <input type="text" name="name" class="input" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div>
                            <label>Email *</label>
                            <input type="email" name="email" class="input" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div>
                            <label>Số điện thoại</label>
                            <input type="text" name="phone" class="input" value="{{ old('phone', $user->phone) }}">
                        </div>
                        <div>
                            <label>Đổi mật khẩu mới</label>
                            <input type="password" name="password" class="input" placeholder="Để trống nếu không đổi">
                        </div>
                        <div>
                            <label>Phòng ban</label>
                            <select name="department_id" class="input">
                                <option value="">-- Không có --</option>
                                @foreach(\App\Models\Department::orderBy('name')->pluck('name', 'id') as $departmentId => $departmentName)
                                    <option value="{{ $departmentId }}" @selected((string) old('department_id', $user->department_id) === (string) $departmentId)>{{ $departmentName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label>Roles</label>
                        @php $userRoles = old('roles', $user->roles->pluck('name')->toArray()); @endphp
                        <div class="user-role-grid">
                            @foreach(\Spatie\Permission\Models\Role::orderBy('name')->pluck('name', 'name') as $role)
                                <label class="shield-permission-pill">
                                    <input type="checkbox" name="roles[]" value="{{ $role }}" @checked(in_array($role, $userRoles, true))>
                                    <span>{{ \App\Support\AdminLabel::role($role) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Addresses Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Sổ địa chỉ</h3>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="addAddressBlock()">+ Thêm địa chỉ</button>
                </div>
                <div class="card-body" id="address-container">
                    @php
                        $addresses = old('addresses', $user->addresses->toArray());
                        if (empty($addresses)) {
                            $addresses = [[]];
                        }
                    @endphp

                    @foreach($addresses as $index => $address)
                        <div class="address-block" style="border: 1px solid var(--fi-border); border-radius: 8px; padding: 16px; margin-bottom: 14px; background-color: #f8fafc;">
                            <input type="hidden" name="addresses[{{ $index }}][id]" value="{{ $address['id'] ?? '' }}">

                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid var(--fi-border);">
                                <div style="display: flex; gap: 12px; align-items: center;">
                                    <label style="font-weight: 600; display: flex; align-items: center; gap: 6px; cursor: pointer; margin: 0;">
                                        <input type="radio" name="addresses[{{ $index }}][is_default]" value="1"
                                            @checked(($user->default_shipping_address_id == ($address['id'] ?? null)) || ($user->default_billing_address_id == ($address['id'] ?? null)))>
                                        <span>Mặc định</span>
                                    </label>

                                    <select name="addresses[{{ $index }}][type]" class="input input-sm" style="width: auto;">
                                        <option value="shipping" @selected(($address['type'] ?? '') == 'shipping')>Shipping</option>
                                        <option value="billing" @selected(($address['type'] ?? '') == 'billing')>Billing</option>
                                    </select>
                                </div>
                                <button type="button" class="link-action link-danger" onclick="this.closest('.address-block').remove()">Xóa</button>
                            </div>

                            <div class="form-grid">
                                <div>
                                    <label>First Name</label>
                                    <input type="text" name="addresses[{{ $index }}][first_name]" value="{{ $address['first_name'] ?? '' }}" class="input input-sm">
                                </div>
                                <div>
                                    <label>Last Name</label>
                                    <input type="text" name="addresses[{{ $index }}][last_name]" value="{{ $address['last_name'] ?? '' }}" class="input input-sm">
                                </div>
                                <div>
                                    <label>Phone</label>
                                    <input type="text" name="addresses[{{ $index }}][phone]" value="{{ $address['phone'] ?? '' }}" class="input input-sm">
                                </div>
                                <div>
                                    <label>Email</label>
                                    <input type="email" name="addresses[{{ $index }}][email]" value="{{ $address['email'] ?? '' }}" class="input input-sm">
                                </div>
                                <div style="grid-column: 1 / -1;">
                                    <label>Address Line 1</label>
                                    <input type="text" name="addresses[{{ $index }}][address_detail]" value="{{ $address['address_detail'] ?? '' }}" class="input input-sm">
                                </div>
                                <div style="grid-column: 1 / -1;">
                                    <label>Address Line 2 (Optional)</label>
                                    <input type="text" name="addresses[{{ $index }}][address_line_2]" value="{{ $address['address_line_2'] ?? '' }}" class="input input-sm">
                                </div>
                                <div>
                                    <label>City</label>
                                    <input type="text" name="addresses[{{ $index }}][city]" value="{{ $address['city'] ?? '' }}" class="input input-sm">
                                </div>
                                <div>
                                    <label>State/Province</label>
                                    <input type="text" name="addresses[{{ $index }}][state]" value="{{ $address['state'] ?? '' }}" class="input input-sm">
                                </div>
                                <div>
                                    <label>Country Code</label>
                                    <input type="text" name="addresses[{{ $index }}][country]" value="{{ $address['country'] ?? '' }}" placeholder="VN" class="input input-sm">
                                </div>
                                <div>
                                    <label>Postal Code</label>
                                    <input type="text" name="addresses[{{ $index }}][postal_code]" value="{{ $address['postal_code'] ?? '' }}" class="input input-sm">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="form-footer" style="border-top: 0; margin-top: 0; padding-top: 0;">
                <a href="{{ route($routePrefix . '.index') }}" class="btn btn-secondary">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            </div>
        </div>

        <!-- Right Column -->
        <div class="show-sidebar">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thống kê chi tiêu</h3>
                </div>
                <div class="card-body">
                    <div class="field-list tabular-nums">
                        <div class="field-group" style="display: flex; justify-content: space-between;">
                            <span class="field-label" style="margin: 0;">Tổng đơn hàng</span>
                            <span class="font-bold mono">{{ $user->orders->count() }}</span>
                        </div>
                        <div class="field-group" style="display: flex; justify-content: space-between;">
                            <span class="field-label" style="margin: 0;">Tổng chi tiêu</span>
                            <span class="font-bold mono" style="color: var(--fi-primary);">{{ number_format($user->orders->whereIn('status', ['completed', 'delivering'])->sum('total'), 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment History Card -->
            <div class="table-panel card">
                <div style="padding: 13px 18px; border-bottom: 1px solid var(--fi-border); background: #fcfcfd;">
                    <h3 class="card-title">Lịch sử thanh toán</h3>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Đơn hàng</th>
                                <th>Phương thức</th>
                                <th class="text-right">Số tiền</th>
                                <th class="text-center">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->payments->sortByDesc('created_at') as $payment)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $payment->order_id) }}" class="mono" style="color: var(--fi-primary); font-weight: 700;">#{{ $payment->order_id }}</a>
                                    </td>
                                    <td>
                                        <span class="mono" style="font-weight: 600;">{{ strtoupper($payment->method) }}</span><br>
                                        <span class="cell-muted mono" style="font-size: 11px;">{{ $payment->transaction_id ?: $payment->reference }}</span>
                                    </td>
                                    <td class="text-right font-bold mono">
                                        {{ number_format($payment->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        @if($payment->status === 'paid' || $payment->status === 'completed')
                                            <span class="badge bg-green">Thành công</span>
                                        @elseif($payment->status === 'refunded')
                                            <span class="badge bg-red">Hoàn tiền</span>
                                        @else
                                            <span class="badge bg-gray">{{ ucfirst($payment->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty-state">Không có lịch sử thanh toán.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    let addrIndex = {{ count($addresses) }};
    function addAddressBlock() {
        const container = document.getElementById('address-container');
        const block = document.createElement('div');
        block.className = 'address-block';
        block.style.cssText = 'border: 1px solid var(--fi-border); border-radius: 8px; padding: 16px; margin-bottom: 14px; background-color: #f8fafc;';

        block.innerHTML = `
            <input type="hidden" name="addresses[${addrIndex}][id]" value="">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid var(--fi-border);">
                <div style="display: flex; gap: 12px; align-items: center;">
                    <label style="font-weight: 600; display: flex; align-items: center; gap: 6px; cursor: pointer; margin: 0;">
                        <input type="radio" name="addresses[${addrIndex}][is_default]" value="1">
                        <span>Mặc định</span>
                    </label>
                    <select name="addresses[${addrIndex}][type]" class="input input-sm" style="width: auto;">
                        <option value="shipping">Shipping</option>
                        <option value="billing">Billing</option>
                    </select>
                </div>
                <button type="button" class="link-action link-danger" onclick="this.closest('.address-block').remove()">Xóa</button>
            </div>
            <div class="form-grid">
                <div><label>First Name</label><input type="text" name="addresses[${addrIndex}][first_name]" class="input input-sm"></div>
                <div><label>Last Name</label><input type="text" name="addresses[${addrIndex}][last_name]" class="input input-sm"></div>
                <div><label>Phone</label><input type="text" name="addresses[${addrIndex}][phone]" class="input input-sm"></div>
                <div><label>Email</label><input type="email" name="addresses[${addrIndex}][email]" class="input input-sm"></div>
                <div style="grid-column: 1 / -1;"><label>Address Line 1</label><input type="text" name="addresses[${addrIndex}][address_detail]" class="input input-sm"></div>
                <div style="grid-column: 1 / -1;"><label>Address Line 2 (Optional)</label><input type="text" name="addresses[${addrIndex}][address_line_2]" class="input input-sm"></div>
                <div><label>City</label><input type="text" name="addresses[${addrIndex}][city]" class="input input-sm"></div>
                <div><label>State/Province</label><input type="text" name="addresses[${addrIndex}][state]" class="input input-sm"></div>
                <div><label>Country Code</label><input type="text" name="addresses[${addrIndex}][country]" placeholder="VN" class="input input-sm"></div>
                <div><label>Postal Code</label><input type="text" name="addresses[${addrIndex}][postal_code]" class="input input-sm"></div>
            </div>
        `;
        container.appendChild(block);
        addrIndex++;
    }
</script>
@endsection
