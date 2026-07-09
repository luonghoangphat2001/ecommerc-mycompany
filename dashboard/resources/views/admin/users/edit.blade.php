@extends('admin.layouts.app', ['title' => $title ?? 'Chỉnh sửa thành viên'])

@section('content')
<div class="page-header" style="margin-bottom: 24px;">
    <div>
        <h1 class="page-title">{{ $title ?? 'Chỉnh sửa thành viên' }}</h1>
        <p class="page-description">Quản lý thông tin cá nhân, địa chỉ và lịch sử thanh toán</p>
    </div>
</div>

<form method="post" action="{{ route($routePrefix . '.update', $user->id) }}">
    @csrf
    @method('PUT')

    @if ($errors->any())
        <div class="alert alert-error mb-4" style="background:#fee2e2; color:#991b1b; padding:16px; border-radius:8px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <!-- Left Column -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            
            <!-- Basic Info Card -->
            <div class="card" style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div class="card-header" style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; font-weight: 600;">
                    Thông tin cơ bản
                </div>
                <div class="card-body" style="padding: 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 6px;">Tên đầy đủ *</label>
                            <input type="text" name="name" class="input" value="{{ old('name', $user->name) }}" required style="width: 100%;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 6px;">Email *</label>
                            <input type="email" name="email" class="input" value="{{ old('email', $user->email) }}" required style="width: 100%;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 6px;">Số điện thoại</label>
                            <input type="text" name="phone" class="input" value="{{ old('phone', $user->phone) }}" style="width: 100%;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 6px;">Đổi mật khẩu mới</label>
                            <input type="password" name="password" class="input" placeholder="Để trống nếu không đổi" style="width: 100%;">
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-weight: 500; margin-bottom: 6px;">Roles</label>
                        <select name="roles[]" multiple class="input" style="width: 100%; height: 80px;">
                            @php $userRoles = old('roles', $user->roles->pluck('name')->toArray()); @endphp
                            @foreach(\Spatie\Permission\Models\Role::orderBy('name')->pluck('name', 'name') as $role)
                                <option value="{{ $role }}" @selected(in_array($role, $userRoles))>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Addresses Card -->
            <div class="card" style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div class="card-header" style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; font-weight: 600; display: flex; justify-content: space-between; align-items: center;">
                    <span>Sổ địa chỉ</span>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="addAddressBlock()">+ Thêm địa chỉ</button>
                </div>
                <div class="card-body" style="padding: 20px;" id="address-container">
                    @php
                        $addresses = old('addresses', $user->addresses->toArray());
                        if (empty($addresses)) {
                            $addresses = [[]]; // Default empty block
                        }
                    @endphp

                    @foreach($addresses as $index => $address)
                        <div class="address-block" style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 20px; background-color: #f8fafc; position: relative; transition: all 0.2s;">
                            <input type="hidden" name="addresses[{{ $index }}][id]" value="{{ $address['id'] ?? '' }}">
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #cbd5e1;">
                                <div style="display: flex; gap: 16px; align-items: center;">
                                    <label style="font-weight: 600; display: flex; align-items: center; gap: 6px; cursor: pointer; color: #1e293b;">
                                        <input type="radio" name="addresses[{{ $index }}][is_default]" value="1" style="width: 16px; height: 16px; accent-color: #2563eb;"
                                            @checked(($user->default_shipping_address_id == ($address['id'] ?? null)) || ($user->default_billing_address_id == ($address['id'] ?? null)))>
                                        Mặc định
                                    </label>
                                    
                                    <select name="addresses[{{ $index }}][type]" class="input input-sm" style="width: auto; padding: 4px 8px; border-radius: 4px; background: white; font-weight: 500; color: #475569;">
                                        <option value="shipping" @selected(($address['type'] ?? '') == 'shipping')>📦 Shipping</option>
                                        <option value="billing" @selected(($address['type'] ?? '') == 'billing')>💳 Billing</option>
                                    </select>
                                </div>
                                <button type="button" style="background: white; border: 1px solid #f87171; color: #ef4444; padding: 4px 12px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; transition: 0.2s;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='white'" onclick="this.closest('.address-block').remove()">Xóa</button>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div>
                                    <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">First Name</label>
                                    <input type="text" name="addresses[{{ $index }}][first_name]" value="{{ $address['first_name'] ?? '' }}" class="input input-sm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: white;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">Last Name</label>
                                    <input type="text" name="addresses[{{ $index }}][last_name]" value="{{ $address['last_name'] ?? '' }}" class="input input-sm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: white;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">Phone</label>
                                    <input type="text" name="addresses[{{ $index }}][phone]" value="{{ $address['phone'] ?? '' }}" class="input input-sm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: white;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">Email</label>
                                    <input type="email" name="addresses[{{ $index }}][email]" value="{{ $address['email'] ?? '' }}" class="input input-sm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: white;">
                                </div>
                                <div style="grid-column: span 2;">
                                    <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">Address Line 1</label>
                                    <input type="text" name="addresses[{{ $index }}][address_detail]" value="{{ $address['address_detail'] ?? '' }}" class="input input-sm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: white;">
                                </div>
                                <div style="grid-column: span 2;">
                                    <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">Address Line 2 (Optional)</label>
                                    <input type="text" name="addresses[{{ $index }}][address_line_2]" value="{{ $address['address_line_2'] ?? '' }}" class="input input-sm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: white;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">City</label>
                                    <input type="text" name="addresses[{{ $index }}][city]" value="{{ $address['city'] ?? '' }}" class="input input-sm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: white;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">State/Province</label>
                                    <input type="text" name="addresses[{{ $index }}][state]" value="{{ $address['state'] ?? '' }}" class="input input-sm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: white;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">Country Code</label>
                                    <input type="text" name="addresses[{{ $index }}][country]" value="{{ $address['country'] ?? '' }}" placeholder="VN" class="input input-sm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: white;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">Postal Code</label>
                                    <input type="text" name="addresses[{{ $index }}][postal_code]" value="{{ $address['postal_code'] ?? '' }}" class="input input-sm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: white;">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="{{ route($routePrefix . '.index') }}" class="btn btn-secondary">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            </div>
        </div>

        <!-- Right Column -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Payment History Card -->
            <div class="card" style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div class="card-header" style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; font-weight: 600;">
                    Lịch sử thanh toán
                </div>
                <div class="card-body" style="padding: 0;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <th style="padding: 12px 16px; text-align: left;">Đơn hàng</th>
                                <th style="padding: 12px 16px; text-align: left;">Phương thức</th>
                                <th style="padding: 12px 16px; text-align: left;">Số tiền</th>
                                <th style="padding: 12px 16px; text-align: center;">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->payments->sortByDesc('created_at') as $payment)
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 12px 16px;">
                                        <a href="{{ route('admin.orders.show', $payment->order_id) }}" style="color: #2563eb; font-weight: 500;">#{{ $payment->order_id }}</a>
                                    </td>
                                    <td style="padding: 12px 16px;">
                                        {{ strtoupper($payment->method) }}<br>
                                        <span style="font-size: 11px; color: #64748b;">{{ $payment->transaction_id ?: $payment->reference }}</span>
                                    </td>
                                    <td style="padding: 12px 16px; font-weight: 500;">
                                        {{ number_format($payment->amount, 0, ',', '.') }}
                                    </td>
                                    <td style="padding: 12px 16px; text-align: center;">
                                        @if($payment->status === 'paid' || $payment->status === 'completed')
                                            <span style="display:inline-block; padding:2px 6px; font-size:11px; border-radius:4px; background:#dcfce7; color:#166534;">Thành công</span>
                                        @elseif($payment->status === 'refunded')
                                            <span style="display:inline-block; padding:2px 6px; font-size:11px; border-radius:4px; background:#fee2e2; color:#991b1b;">Hoàn tiền</span>
                                        @else
                                            <span style="display:inline-block; padding:2px 6px; font-size:11px; border-radius:4px; background:#f1f5f9; color:#475569;">{{ ucfirst($payment->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="padding: 24px; text-align: center; color: #64748b;">Không có lịch sử thanh toán.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Stats or other info could go here -->
            <div class="card" style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                    <span style="color: #64748b;">Tổng đơn hàng</span>
                    <span style="font-weight: 600;">{{ $user->orders->count() }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #64748b;">Tổng chi tiêu</span>
                    <span style="font-weight: 600;">{{ number_format($user->orders->whereIn('status', ['completed', 'delivering'])->sum('total'), 0, ',', '.') }}</span>
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
        block.style.cssText = 'border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 20px; background-color: #f8fafc; position: relative; transition: all 0.2s;';
        
        block.innerHTML = `
            <input type="hidden" name="addresses[${addrIndex}][id]" value="">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #cbd5e1;">
                <div style="display: flex; gap: 16px; align-items: center;">
                    <label style="font-weight: 600; display: flex; align-items: center; gap: 6px; cursor: pointer; color: #1e293b;">
                        <input type="radio" name="addresses[${addrIndex}][is_default]" value="1" style="width: 16px; height: 16px; accent-color: #2563eb;">
                        Mặc định
                    </label>
                    <select name="addresses[${addrIndex}][type]" class="input input-sm" style="width: auto; padding: 4px 8px; border-radius: 4px; background: white; font-weight: 500; color: #475569;">
                        <option value="shipping">📦 Shipping</option>
                        <option value="billing">💳 Billing</option>
                    </select>
                </div>
                <button type="button" style="background: white; border: 1px solid #f87171; color: #ef4444; padding: 4px 12px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; transition: 0.2s;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='white'" onclick="this.closest('.address-block').remove()">Xóa</button>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div><label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">First Name</label><input type="text" name="addresses[${addrIndex}][first_name]" class="input input-sm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: white;"></div>
                <div><label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">Last Name</label><input type="text" name="addresses[${addrIndex}][last_name]" class="input input-sm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: white;"></div>
                <div><label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">Phone</label><input type="text" name="addresses[${addrIndex}][phone]" class="input input-sm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: white;"></div>
                <div><label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">Email</label><input type="email" name="addresses[${addrIndex}][email]" class="input input-sm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: white;"></div>
                <div style="grid-column: span 2;"><label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">Address Line 1</label><input type="text" name="addresses[${addrIndex}][address_detail]" class="input input-sm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: white;"></div>
                <div style="grid-column: span 2;"><label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">Address Line 2 (Optional)</label><input type="text" name="addresses[${addrIndex}][address_line_2]" class="input input-sm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: white;"></div>
                <div><label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">City</label><input type="text" name="addresses[${addrIndex}][city]" class="input input-sm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: white;"></div>
                <div><label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">State/Province</label><input type="text" name="addresses[${addrIndex}][state]" class="input input-sm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: white;"></div>
                <div><label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">Country Code</label><input type="text" name="addresses[${addrIndex}][country]" placeholder="VN" class="input input-sm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: white;"></div>
                <div><label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase;">Postal Code</label><input type="text" name="addresses[${addrIndex}][postal_code]" class="input input-sm" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: white;"></div>
            </div>
        `;
        container.appendChild(block);
        addrIndex++;
    }
</script>
@endsection
