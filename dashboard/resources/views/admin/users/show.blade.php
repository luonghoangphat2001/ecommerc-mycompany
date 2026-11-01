@extends('admin.layouts.app', ['title' => $title ?? 'Chi tiết thành viên'])

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $title ?? 'Chi tiết thành viên' }}</h1>
        <p class="page-description">Xem chi tiết thông tin, địa chỉ, lịch sử thanh toán và đơn hàng</p>
    </div>
    <div class="actions">
        <a href="{{ route($routePrefix . '.index') }}" class="btn btn-secondary">&larr; Quay lại</a>
        @if(auth()->user()->hasRole('super_admin') || auth()->user()->hasPermissionTo('update_users'))
            <a href="{{ route($routePrefix . '.edit', $user->id) }}" class="btn btn-primary">Chỉnh sửa</a>
        @endif
    </div>
</div>

<div class="show-layout">
    <!-- Left Column -->
    <div class="show-main">
        <!-- Basic Info Card -->
        <div class="card">
            <div class="card-header" style="justify-content: flex-start; gap: 14px;">
                <div style="width: 44px; height: 44px; border-radius: 999px; background: var(--fi-navy-dark); display: grid; place-items: center; font-size: 16px; font-weight: 800; color: var(--fi-gold); overflow: hidden; flex-shrink: 0;">
                    @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </div>
                <div>
                    <div style="font-size: 15px; font-weight: 700; color: var(--fi-text);">{{ $user->name }}</div>
                    <div class="cell-muted">Tham gia từ: <span class="mono">{{ $user->created_at->format('d/m/Y') }}</span></div>
                </div>
            </div>
            <div class="card-body">
                <div class="field-list field-list-2col">
                    <div class="field-group">
                        <div class="field-label">Email</div>
                        <div class="field-value">{{ $user->email }}</div>
                    </div>
                    <div class="field-group">
                        <div class="field-label">Số điện thoại</div>
                        <div class="field-value mono">{{ $user->phone ?: 'Chưa cập nhật' }}</div>
                    </div>
                    <div class="field-group" style="grid-column: 1 / -1;">
                        <div class="field-label">Phòng ban (Department)</div>
                        <div class="field-value">
                            @if($user->department)
                                <span class="badge bg-blue">{{ $user->department->name }}</span>
                            @else
                                <span class="val-empty">Chưa gán phòng ban</span>
                            @endif
                        </div>
                    </div>
                    <div class="field-group" style="grid-column: 1 / -1;">
                        <div class="field-label">Vai trò (Roles)</div>
                        <div class="field-value" style="display: flex; flex-wrap: wrap; gap: 6px; padding-top: 4px;">
                            @forelse($user->roles as $role)
                                <span class="badge bg-blue">{{ \App\Support\AdminLabel::role($role->name) }}</span>
                            @empty
                                <span class="val-empty">Chưa gán vai trò</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="field-group" style="grid-column: 1 / -1;">
                        <div class="field-label">Quyền hạn (Permissions)</div>
                        <div class="field-value" style="padding-top: 6px;">
                            @php
                                $permissions = $user->getAllPermissions();
                                $groupedPermissions = [];
                                foreach ($permissions as $permission) {
                                    [$action, $resource] = \App\Support\AdminLabel::splitPermission($permission->name);
                                    $group = \App\Support\AdminLabel::resource($resource ?: 'general');
                                    $groupedPermissions[$group][] = $permission->name;
                                }
                                ksort($groupedPermissions);
                            @endphp

                            @if($permissions->isEmpty())
                                <span class="val-empty">Không có quyền hạn</span>
                            @else
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(195px, 1fr)); gap: 12px;">
                                    @foreach($groupedPermissions as $group => $perms)
                                        <div style="background: #f8fafc; border: 1px solid var(--fi-border); border-radius: 8px; padding: 10px 12px;">
                                            <div style="font-size: 11px; font-weight: 700; color: var(--fi-text); text-transform: uppercase; margin-bottom: 6px; border-bottom: 1px solid var(--fi-border); padding-bottom: 4px;">{{ $group }}</div>
                                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                                @foreach($perms as $perm)
                                                    <span style="font-size: 12px; color: var(--fi-text-secondary); display: flex; align-items: center; gap: 6px;">
                                                        <span style="width: 4px; height: 4px; border-radius: 50%; background: var(--fi-primary);"></span>
                                                        {{ \App\Support\AdminLabel::permission($perm) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Addresses Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sổ địa chỉ ({{ $user->addresses->count() }})</h3>
            </div>
            <div class="card-body">
                @forelse($user->addresses as $address)
                    <div style="border: 1px solid var(--fi-border); border-radius: 8px; padding: 14px 16px; margin-bottom: 12px; background-color: #f8fafc;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; border-bottom: 1px dashed var(--fi-border-strong); padding-bottom: 10px;">
                            <div>
                                <span style="font-weight: 700; font-size: 13.5px; color: var(--fi-text);">{{ $address->first_name }} {{ $address->last_name }}</span>
                                @if($user->default_shipping_address_id == $address->id || $user->default_billing_address_id == $address->id)
                                    <span class="badge bg-blue" style="margin-left: 6px;">MẶC ĐỊNH</span>
                                @endif
                            </div>
                            <div>
                                @if($address->type === 'shipping')
                                    <span class="badge bg-green">Giao hàng</span>
                                @else
                                    <span class="badge bg-yellow">Thanh toán</span>
                                @endif
                            </div>
                        </div>

                        <div class="field-list field-list-2col">
                            <div class="field-group" style="padding: 4px 0; border: none;">
                                <div class="field-label">Phone</div>
                                <div class="field-value mono" style="font-size: 12.5px;">{{ $address->phone ?: '-' }}</div>
                            </div>
                            <div class="field-group" style="padding: 4px 0; border: none;">
                                <div class="field-label">Email</div>
                                <div class="field-value" style="font-size: 12.5px;">{{ $address->email ?: '-' }}</div>
                            </div>
                            <div class="field-group" style="grid-column: 1 / -1; padding: 4px 0; border: none;">
                                <div class="field-label">Address Line 1</div>
                                <div class="field-value" style="font-size: 12.5px;">{{ $address->address_detail ?: '-' }}</div>
                            </div>
                            <div class="field-group" style="grid-column: 1 / -1; padding: 4px 0; border: none;">
                                <div class="field-label">Address Line 2</div>
                                <div class="field-value" style="font-size: 12.5px;">{{ $address->address_line_2 ?: '-' }}</div>
                            </div>
                            <div class="field-group" style="padding: 4px 0; border: none;">
                                <div class="field-label">City/Province</div>
                                <div class="field-value" style="font-size: 12.5px;">{{ $address->city ?: '-' }}</div>
                            </div>
                            <div class="field-group" style="padding: 4px 0; border: none;">
                                <div class="field-label">State</div>
                                <div class="field-value" style="font-size: 12.5px;">{{ $address->state ?: '-' }}</div>
                            </div>
                            <div class="field-group" style="padding: 4px 0; border: none;">
                                <div class="field-label">Country</div>
                                <div class="field-value mono" style="font-size: 12.5px;">{{ strtoupper($address->country ?: 'VN') }}</div>
                            </div>
                            <div class="field-group" style="padding: 4px 0; border: none;">
                                <div class="field-label">Postal/Zip Code</div>
                                <div class="field-value mono" style="font-size: 12.5px;">{{ $address->postal_code ?: '-' }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">Thành viên này chưa có địa chỉ nào trong sổ địa chỉ.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="show-sidebar">
        <!-- Stats Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Thống kê mua hàng</h3>
            </div>
            <div class="card-body">
                <div class="field-list tabular-nums">
                    <div class="field-group" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="field-label" style="margin: 0;">Tổng số đơn hàng</div>
                        <div class="field-value font-bold mono">{{ $user->orders->count() }}</div>
                    </div>
                    <div class="field-group" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="field-label" style="margin: 0;">Tổng chi tiêu (Thành công)</div>
                        <div class="field-value font-bold mono" style="color: var(--fi-primary);">{{ number_format($user->orders->whereIn('status', ['completed', 'delivering'])->sum('total'), 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment & Order History Card -->
        <div class="table-panel card">
            <div style="padding: 13px 18px; border-bottom: 1px solid var(--fi-border); background: #fcfcfd;">
                <h3 class="card-title">Lịch sử thanh toán</h3>
            </div>
            <div class="table-wrap" style="max-height: 480px; overflow-y: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Đơn hàng</th>
                            <th>Thông tin Payment</th>
                            <th class="text-center">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($user->payments->sortByDesc('created_at') as $payment)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $payment->order_id) }}" class="mono" style="color: var(--fi-primary); font-weight: 700; display: block;">#{{ $payment->order_id }}</a>
                                    <span class="cell-muted mono" style="font-size: 11px;">{{ $payment->created_at->format('d/m/Y H:i') }}</span>
                                </td>
                                <td>
                                    <div class="mono font-bold">
                                        {{ number_format($payment->amount, 0, ',', '.') }} đ
                                        <span class="badge bg-gray" style="margin-left: 4px;">{{ strtoupper($payment->method) }}</span>
                                    </div>
                                    @if($payment->transaction_id || $payment->reference)
                                        <div class="cell-muted mono" style="font-size: 11px; word-break: break-all;">Ref: {{ $payment->transaction_id ?: $payment->reference }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($payment->status === 'paid' || $payment->status === 'completed')
                                        <span class="badge bg-green">THÀNH CÔNG</span>
                                    @elseif($payment->status === 'refunded')
                                        <span class="badge bg-red">HOÀN TIỀN</span>
                                    @else
                                        <span class="badge bg-gray">{{ strtoupper($payment->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-state">Không có lịch sử giao dịch.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
