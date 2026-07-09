@extends('admin.layouts.app', ['title' => $title ?? 'Chi tiết thành viên'])

@section('content')
<style>
    .show-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 24px;
    }
    @media (min-width: 1024px) {
        .show-layout {
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
            align-items: start;
        }
    }
    .card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 24px;
    }
    .card-header {
        padding: 16px 24px;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .card-title {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #0f172a;
    }
    .card-body {
        padding: 16px 24px;
    }
    
    .field-list {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0;
    }
    @media (min-width: 640px) {
        .field-list-2col { grid-template-columns: 1fr 1fr; gap: 16px; }
    }
    .field-group {
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .field-group:last-child {
        border-bottom: none;
    }
    .field-label {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .field-value {
        font-size: 15px;
        color: #1e293b;
        line-height: 1.5;
        word-break: break-word;
    }
    
    .badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; }
    .bg-green { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .bg-red { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .bg-yellow { background: #fef08a; color: #854d0e; border: 1px solid #fde047; }
    .bg-blue { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
    .bg-gray { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
    
    .actions-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    .page-title { margin: 0; font-size: 24px; font-weight: 700; color: #0f172a; }
    .page-description { font-size: 14px; color: #64748b; margin-top: 4px; }

    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { padding: 12px; border-bottom: 2px solid #e2e8f0; font-size: 13px; color: #64748b; font-weight: 600; text-transform: uppercase; }
    td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #1e293b; }
    tr:last-child td { border-bottom: none; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .font-bold { font-weight: 600; }
</style>

<div class="actions-bar">
    <div>
        <h1 class="page-title">{{ $title ?? 'Chi tiết thành viên' }}</h1>
        <p class="page-description">Xem chi tiết thông tin, địa chỉ, lịch sử thanh toán và đơn hàng</p>
    </div>
    <div style="display: flex; gap: 12px;">
        <a href="{{ route($routePrefix . '.index') }}" class="btn btn-secondary">Quay lại</a>
        @if(auth()->user()->hasRole('super_admin') || auth()->user()->hasPermissionTo('update_users'))
        <a href="{{ route($routePrefix . '.edit', $user->id) }}" class="btn btn-primary">Chỉnh sửa</a>
        @endif
    </div>
</div>

<div class="show-layout">
    <!-- Cột trái: Thông tin chính -->
    <div>
        <!-- Basic Info Card -->
        <div class="card">
            <div class="card-header" style="gap: 16px; justify-content: flex-start;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; color: #64748b; overflow: hidden; border: 2px solid #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </div>
                <div>
                    <div style="font-size: 18px; font-weight: 700; color: #0f172a;">{{ $user->name }}</div>
                    <div style="font-size: 13px; color: #64748b; margin-top: 2px;">Tham gia từ: {{ $user->created_at->format('d/m/Y') }}</div>
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
                        <div class="field-value">{{ $user->phone ?: 'Chưa cập nhật' }}</div>
                    </div>
                    <div class="field-group" style="grid-column: span 2;">
                        <div class="field-label">Vai trò (Roles)</div>
                        <div class="field-value" style="display: flex; flex-wrap: wrap; gap: 6px; padding-top: 4px;">
                            @forelse($user->roles as $role)
                                <span class="badge bg-blue">{{ $role->name }}</span>
                            @empty
                                <span style="color: #94a3b8; font-style: italic; font-size: 13px;">Chưa gán vai trò</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="field-group" style="grid-column: span 2;">
                        <div class="field-label">Quyền hạn (Permissions)</div>
                        <div class="field-value" style="display: flex; flex-wrap: wrap; gap: 6px; padding-top: 4px;">
                            @forelse($user->getAllPermissions() as $permission)
                                <span class="badge bg-gray" style="font-size: 11px; padding: 2px 8px;">{{ $permission->name }}</span>
                            @empty
                                <span style="color: #94a3b8; font-style: italic; font-size: 13px;">Không có quyền hạn</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Addresses Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sổ địa chỉ (Address Information)</h3>
            </div>
            <div class="card-body">
                @forelse($user->addresses as $address)
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 16px; background-color: #f8fafc;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; border-bottom: 1px dashed #cbd5e1; padding-bottom: 12px;">
                            <div>
                                <span style="font-weight: 600; font-size: 15px; color: #0f172a;">{{ $address->first_name }} {{ $address->last_name }}</span>
                                @if($user->default_shipping_address_id == $address->id || $user->default_billing_address_id == $address->id)
                                    <span class="badge bg-blue" style="margin-left: 8px; padding: 2px 6px; font-size: 10px;">MẶC ĐỊNH</span>
                                @endif
                            </div>
                            <div>
                                @if($address->type === 'shipping')
                                    <span class="badge bg-green">📦 Giao hàng</span>
                                @else
                                    <span class="badge bg-yellow">💳 Thanh toán</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="field-list field-list-2col">
                            <div class="field-group" style="padding: 6px 0; border: none;">
                                <div class="field-label">Phone</div>
                                <div class="field-value" style="font-size: 13px;">{{ $address->phone ?: '-' }}</div>
                            </div>
                            <div class="field-group" style="padding: 6px 0; border: none;">
                                <div class="field-label">Email</div>
                                <div class="field-value" style="font-size: 13px;">{{ $address->email ?: '-' }}</div>
                            </div>
                            <div class="field-group" style="grid-column: span 2; padding: 6px 0; border: none;">
                                <div class="field-label">Address Line 1</div>
                                <div class="field-value" style="font-size: 13px;">{{ $address->address_detail ?: '-' }}</div>
                            </div>
                            <div class="field-group" style="grid-column: span 2; padding: 6px 0; border: none;">
                                <div class="field-label">Address Line 2</div>
                                <div class="field-value" style="font-size: 13px;">{{ $address->address_line_2 ?: '-' }}</div>
                            </div>
                            <div class="field-group" style="padding: 6px 0; border: none;">
                                <div class="field-label">City/Province</div>
                                <div class="field-value" style="font-size: 13px;">{{ $address->city ?: '-' }}</div>
                            </div>
                            <div class="field-group" style="padding: 6px 0; border: none;">
                                <div class="field-label">State</div>
                                <div class="field-value" style="font-size: 13px;">{{ $address->state ?: '-' }}</div>
                            </div>
                            <div class="field-group" style="padding: 6px 0; border: none;">
                                <div class="field-label">Country</div>
                                <div class="field-value" style="font-size: 13px;">{{ strtoupper($address->country ?: 'VN') }}</div>
                            </div>
                            <div class="field-group" style="padding: 6px 0; border: none;">
                                <div class="field-label">Postal/Zip Code</div>
                                <div class="field-value" style="font-size: 13px;">{{ $address->postal_code ?: '-' }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 24px; color: #94a3b8; font-style: italic;">
                        Thành viên này chưa có địa chỉ nào trong sổ địa chỉ.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Cột phải: Thống kê & Lịch sử -->
    <div>
        
        <!-- Stats Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Thống kê mua hàng</h3>
            </div>
            <div class="card-body">
                <div class="field-list">
                    <div class="field-group" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="field-label" style="margin: 0;">Tổng số đơn hàng</div>
                        <div class="field-value font-bold">{{ $user->orders->count() }}</div>
                    </div>
                    <div class="field-group" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="field-label" style="margin: 0;">Tổng chi tiêu (Thành công)</div>
                        <div class="field-value font-bold" style="color: #2563eb;">{{ number_format($user->orders->whereIn('status', ['completed', 'delivering'])->sum('total'), 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment & Order History Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lịch sử thanh toán</h3>
            </div>
            <div class="card-body" style="padding: 0; max-height: 500px; overflow-y: auto;">
                <div class="table-wrap">
                    <table>
                        <thead style="position: sticky; top: 0; background: #f8fafc; z-index: 1;">
                            <tr>
                                <th>Đơn hàng</th>
                                <th>Thông tin Payment</th>
                                <th class="text-center">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->payments->sortByDesc('created_at') as $payment)
                                <tr onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'" style="transition: background 0.2s;">
                                    <td>
                                        <a href="{{ route('admin.orders.show', $payment->order_id) }}" style="color: #2563eb; font-weight: 600; display: block; margin-bottom: 4px; text-decoration: none;">#{{ $payment->order_id }}</a>
                                        <span style="font-size: 11px; color: #64748b;">{{ $payment->created_at->format('d/m/Y H:i') }}</span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: #0f172a; margin-bottom: 4px;">
                                            {{ number_format($payment->amount, 0, ',', '.') }} đ
                                            <span class="badge bg-gray" style="font-size: 9px; margin-left: 4px;">{{ strtoupper($payment->method) }}</span>
                                        </div>
                                        @if($payment->transaction_id || $payment->reference)
                                            <div style="font-size: 11px; color: #64748b; word-break: break-all;">Ref: {{ $payment->transaction_id ?: $payment->reference }}</div>
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
                                    <td colspan="3" style="padding: 32px; text-align: center; color: #94a3b8; font-style: italic;">Không có lịch sử giao dịch.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </div>
</div>
@endsection
