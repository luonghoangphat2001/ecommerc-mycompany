@extends('admin.layouts.app', ['title' => $title])

@section('content')
@php
    $zones = $formData['zones'] ?? [];
    $initialSettings = $formData['initialSettings'] ?? [];
    $currentType = $formData['currentType'] ?? 'flat_rate';
    $currentName = old('name', is_array($record->name ?? null) ? ($record->name['vi'] ?? ($record->name['en'] ?? '')) : ($record->name ?? ''));
    $selectedZoneId = old('shipping_zone_id', $record->shipping_zone_id ?? request('shipping_zone_id'));
@endphp

<div x-data="shippingMethodManager({
    initialType: '{{ $currentType }}',
    initialSettings: {{ json_encode($initialSettings) }}
})">

    {{-- PAGE HEADER --}}
    <x-admin.page-header
        :title="$record ? 'Chỉnh sửa Phương thức vận chuyển: ' . ($currentName ?: '#' . $record->id) : 'Tạo mới Phương thức vận chuyển'"
        :description="'Cấu hình phương thức tính cước, hạn mức miễn phí và thời gian giao hàng.'"
        :backUrl="route($routePrefix . '.index')"
    >
        <x-slot:actions>
            <a class="btn btn-secondary" href="{{ route($routePrefix . '.index') }}">
                &larr; {{ __('admin.actions.cancel') }}
            </a>
            @if ($record && \Illuminate\Support\Facades\Route::has($routePrefix . '.show'))
                <a class="btn btn-secondary" href="{{ route($routePrefix . '.show', $record->id) }}">
                    Xem chi tiết
                </a>
            @endif
            <button class="btn btn-primary" type="submit" form="shipping-method-form">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                {{ __('admin.actions.save') }}
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- NAVIGATION TABS --}}
    @if (!empty($tabs))
        <div style="margin-bottom: var(--fi-space-4, 16px);">
            <x-admin.tabs
                mode="link"
                :active="$activeTab ?? 'shipping-methods'"
                :tabs="$tabs"
            />
        </div>
    @endif

    {{-- ERROR ALERT --}}
    @if (isset($errors) && $errors->any())
        <div style="background: var(--fi-danger-bg, #fef2f2); color: #991b1b; padding: 12px 16px; border-radius: var(--fi-radius-md, 8px); margin-bottom: 20px; border: 1px solid var(--fi-danger-border, #fecaca);">
            <div style="font-weight: 700; font-size: 13px; margin-bottom: 4px;">Vui lòng kiểm tra lại dữ liệu:</div>
            <ul style="margin: 0; padding-left: 20px; font-size: 12.5px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="shipping-method-form" method="POST" action="{{ $record ? route($routePrefix . '.update', $record->id) : route($routePrefix . '.store') }}">
        @csrf
        @if ($record)
            @method('PUT')
        @endif

        {{-- Serialized JSON synced with Alpine --}}
        <input type="hidden" name="settings_json" :value="serializeSettings()">

        <div class="edit-layout">
            
            {{-- MAIN COLUMN --}}
            <div class="show-main" style="display: flex; flex-direction: column; gap: 20px;">

                {{-- CARD 1: THÔNG TIN CƠ BẢN --}}
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--fi-primary);"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            <h3 class="card-title">Thông tin cơ bản</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            
                            {{-- TÊN PHƯƠNG THỨC --}}
                            <div class="form-row" style="grid-column: 1 / -1;">
                                <label for="name" style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                    Tên phương thức hiển thị <span style="color: #ef4444;">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    value="{{ $currentName }}" 
                                    placeholder="Ví dụ: Giao hàng tiêu chuẩn, Giao nhanh hỏa tốc..." 
                                    required 
                                    class="form-control"
                                >
                            </div>

                            {{-- KHU VỰC VẬN CHUYỂN --}}
                            <div class="form-row">
                                <label for="shipping_zone_id" style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                    Khu vực vận chuyển (Shipping Zone) <span style="color: #ef4444;">*</span>
                                </label>
                                <select id="shipping_zone_id" name="shipping_zone_id" class="form-control" required style="font-weight: 500;">
                                    @foreach ($zones as $zone)
                                        <option value="{{ $zone->id }}" @selected((string) $selectedZoneId === (string) $zone->id)>
                                            {{ $zone->name }} ({{ $zone->code ?: '#' . $zone->id }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- LOẠI PHƯƠNG THỨC --}}
                            <div class="form-row">
                                <label for="type" style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                    Loại phương thức tính cước <span style="color: #ef4444;">*</span>
                                </label>
                                <select id="type" name="type" x-model="methodType" class="form-control" style="font-weight: 600;">
                                    <option value="flat_rate">Đồng giá (Flat rate)</option>
                                    <option value="free_shipping">Miễn phí vận chuyển (Free shipping)</option>
                                    <option value="weight_based">Theo khối lượng (Weight based)</option>
                                    <option value="carrier">API Hãng vận chuyển (Carrier API)</option>
                                    <option value="local_pickup">Nhận tại cửa hàng (Local pickup)</option>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- CARD 2: CẤU HÌNH CHI TIẾT TRỰC QUAN (KHÔNG DÙNG JSON) --}}
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--fi-primary);"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
                            <h3 class="card-title">Cấu hình mức cước và điều kiện</h3>
                        </div>
                    </div>

                    <div class="card-body">

                        {{-- TYPE 1: ĐỒNG GIÁ (FLAT RATE) --}}
                        <div x-show="methodType === 'flat_rate'" x-transition>
                            <div class="form-grid">
                                <div class="form-row">
                                    <label style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                        Cước phí vận chuyển (VNĐ) <span style="color: #ef4444;">*</span>
                                    </label>
                                    <input 
                                        type="number" 
                                        x-model.number="settings.cost" 
                                        placeholder="Ví dụ: 30000" 
                                        class="form-control" 
                                        min="0"
                                    >
                                    <small style="color: var(--fi-muted); font-size: 11.5px; display: block; margin-top: 4px;">Số tiền cước cố định áp dụng cho đơn hàng.</small>
                                </div>

                                <div class="form-row">
                                    <label style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                        Ngưỡng miễn phí vận chuyển (VNĐ)
                                    </label>
                                    <input 
                                        type="number" 
                                        x-model.number="settings.free_shipping_threshold" 
                                        placeholder="Ví dụ: 500000 (để trống nếu không áp dụng)" 
                                        class="form-control" 
                                        min="0"
                                    >
                                    <small style="color: var(--fi-muted); font-size: 11.5px; display: block; margin-top: 4px;">Đơn hàng đạt giá trị này sẽ tự động được miễn phí ship.</small>
                                </div>

                                <div class="form-row">
                                    <label style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                        Thời gian giao hàng dự kiến (ngày)
                                    </label>
                                    <input 
                                        type="number" 
                                        x-model.number="settings.estimated_days" 
                                        placeholder="Ví dụ: 2" 
                                        class="form-control" 
                                        min="1"
                                    >
                                </div>

                                <div class="form-row">
                                    <label style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                        Mô tả dịch vụ hiển thị cho khách
                                    </label>
                                    <input 
                                        type="text" 
                                        x-model="settings.description" 
                                        placeholder="Giao hàng tiêu chuẩn 1-3 ngày làm việc" 
                                        class="form-control"
                                    >
                                </div>
                            </div>
                        </div>

                        {{-- TYPE 2: MIỄN PHÍ VẬN CHUYỂN (FREE SHIPPING) --}}
                        <div x-show="methodType === 'free_shipping'" x-transition>
                            <div class="form-grid">
                                <div class="form-row">
                                    <label style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                        Giá trị đơn hàng tối thiểu (VNĐ)
                                    </label>
                                    <input 
                                        type="number" 
                                        x-model.number="settings.min_amount" 
                                        placeholder="Ví dụ: 500000 (để trống nếu miễn phí mọi đơn)" 
                                        class="form-control" 
                                        min="0"
                                    >
                                    <small style="color: var(--fi-muted); font-size: 11.5px; display: block; margin-top: 4px;">Chỉ áp dụng miễn phí khi tổng tiền đơn hàng đạt mức này.</small>
                                </div>

                                <div class="form-row">
                                    <label style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                        Thời gian giao hàng dự kiến (ngày)
                                    </label>
                                    <input 
                                        type="number" 
                                        x-model.number="settings.estimated_days" 
                                        placeholder="Ví dụ: 3" 
                                        class="form-control" 
                                        min="1"
                                    >
                                </div>

                                <div class="form-row" style="grid-column: 1 / -1;">
                                    <label style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                        Mô tả dịch vụ hiển thị cho khách
                                    </label>
                                    <input 
                                        type="text" 
                                        x-model="settings.description" 
                                        placeholder="Miễn phí vận chuyển toàn quốc cho đơn hàng đủ điều kiện" 
                                        class="form-control"
                                    >
                                </div>
                            </div>
                        </div>

                        {{-- TYPE 3: THEO KHỐI LƯỢNG (WEIGHT BASED) --}}
                        <div x-show="methodType === 'weight_based'" x-transition>
                            <div class="form-grid" style="margin-bottom: 18px;">
                                <div class="form-row">
                                    <label style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                        Cước phí cơ bản mặc định (VNĐ)
                                    </label>
                                    <input 
                                        type="number" 
                                        x-model.number="settings.cost" 
                                        placeholder="30000" 
                                        class="form-control" 
                                        min="0"
                                    >
                                </div>

                                <div class="form-row">
                                    <label style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                        Phí cộng thêm cho mỗi 1 kg vượt nấc (VNĐ)
                                    </label>
                                    <input 
                                        type="number" 
                                        x-model.number="settings.additional_cost_per_kg" 
                                        placeholder="5000" 
                                        class="form-control" 
                                        min="0"
                                    >
                                </div>

                                <div class="form-row">
                                    <label style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                        Ngưỡng miễn phí vận chuyển (VNĐ, tùy chọn)
                                    </label>
                                    <input 
                                        type="number" 
                                        x-model.number="settings.free_shipping_threshold" 
                                        placeholder="500000" 
                                        class="form-control" 
                                        min="0"
                                    >
                                </div>

                                <div class="form-row">
                                    <label style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                        Thời gian giao hàng dự kiến (ngày)
                                    </label>
                                    <input 
                                        type="number" 
                                        x-model.number="settings.estimated_days" 
                                        placeholder="2" 
                                        class="form-control" 
                                        min="1"
                                    >
                                </div>
                            </div>

                            {{-- BẢNG NẤC KHỐI LƯỢNG --}}
                            <div style="border: 1px solid var(--fi-border-subtle, #e2e8f0); border-radius: var(--fi-radius-md, 8px); padding: 14px; background: #f8fafc;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                    <div>
                                        <strong style="font-size: 13px; color: var(--fi-text, #0f172a);">Bảng nấc khối lượng (Weight Tiers)</strong>
                                        <span style="font-size: 12px; color: var(--fi-muted); display: block;">Cước phí được áp dụng dựa theo tổng khối lượng của giỏ hàng.</span>
                                    </div>
                                    <button type="button" @click="addWeightTier()" class="btn btn-secondary" style="font-size: 12px; padding: 4px 10px;">
                                        + Thêm nấc
                                    </button>
                                </div>

                                <div style="overflow-x: auto;">
                                    <table style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                                        <thead>
                                            <tr style="border-bottom: 1px solid #cbd5e1; text-align: left;">
                                                <th style="padding: 6px 8px; font-weight: 600;">Từ (gram)</th>
                                                <th style="padding: 6px 8px; font-weight: 600;">Đến (gram)</th>
                                                <th style="padding: 6px 8px; font-weight: 600;">Cước phí (VNĐ)</th>
                                                <th style="padding: 6px 8px; text-align: center; width: 60px;">Xóa</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(tier, idx) in settings.rules" :key="idx">
                                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                                    <td style="padding: 6px 8px;">
                                                        <input type="number" x-model.number="tier.min_weight" class="form-control" style="height: 32px; font-size: 12px;" min="0">
                                                    </td>
                                                    <td style="padding: 6px 8px;">
                                                        <input type="number" x-model.number="tier.max_weight" class="form-control" style="height: 32px; font-size: 12px;" min="0">
                                                    </td>
                                                    <td style="padding: 6px 8px;">
                                                        <input type="number" x-model.number="tier.cost" class="form-control" style="height: 32px; font-size: 12px;" min="0">
                                                    </td>
                                                    <td style="padding: 6px 8px; text-align: center;">
                                                        <button type="button" @click="removeWeightTier(idx)" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 4px;" title="Xóa nấc này">
                                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- TYPE 4: HÃNG VẬN CHUYỂN (CARRIER API) --}}
                        <div x-show="methodType === 'carrier'" x-transition>
                            <div class="form-grid">
                                <div class="form-row">
                                    <label style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                        Đơn vị vận chuyển kết nối API <span style="color: #ef4444;">*</span>
                                    </label>
                                    <select x-model="settings.carrier" class="form-control" style="font-weight: 600;">
                                        <option value="ghn">Giao Hàng Nhanh (GHN)</option>
                                        <option value="ghtk">Giao Hàng Tiết Kiệm (GHTK)</option>
                                        <option value="viettel_post">Viettel Post</option>
                                        <option value="vnpost">Vietnam Post (VNPost)</option>
                                        <option value="dhl">DHL Express (Quốc tế)</option>
                                        <option value="fedex">FedEx (Quốc tế)</option>
                                        <option value="ups">UPS (Quốc tế)</option>
                                    </select>
                                </div>

                                <div class="form-row">
                                    <label style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                        Mã / Tên gói dịch vụ hãng
                                    </label>
                                    <input 
                                        type="text" 
                                        x-model="settings.service_id" 
                                        placeholder="Ví dụ: standard, express, road..." 
                                        class="form-control"
                                    >
                                </div>

                                <div class="form-row">
                                    <label style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                        Phí điều chỉnh / Markup (VNĐ)
                                    </label>
                                    <input 
                                        type="number" 
                                        x-model.number="settings.markup" 
                                        placeholder="Ví dụ: 0 (hoặc số âm nếu trợ giá)" 
                                        class="form-control"
                                    >
                                    <small style="color: var(--fi-muted); font-size: 11.5px; display: block; margin-top: 4px;">Số tiền cộng thêm (hoặc trừ bớt) vào giá cước API trả về.</small>
                                </div>

                                <div class="form-row">
                                    <label style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                        Cước phí dự phòng khi API lỗi (VNĐ)
                                    </label>
                                    <input 
                                        type="number" 
                                        x-model.number="settings.fallback_cost" 
                                        placeholder="Ví dụ: 30000" 
                                        class="form-control" 
                                        min="0"
                                    >
                                    <small style="color: var(--fi-muted); font-size: 11.5px; display: block; margin-top: 4px;">Áp dụng khi hệ thống hãng bảo trì hoặc mất kết nối mạng.</small>
                                </div>
                            </div>
                        </div>

                        {{-- TYPE 5: NHẬN TẠI CỬA HÀNG (LOCAL PICKUP) --}}
                        <div x-show="methodType === 'local_pickup'" x-transition>
                            <div class="form-grid">
                                <div class="form-row">
                                    <label style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                        Phí nhận hàng tại chỗ (VNĐ)
                                    </label>
                                    <input 
                                        type="number" 
                                        x-model.number="settings.cost" 
                                        placeholder="0" 
                                        class="form-control" 
                                        min="0"
                                    >
                                    <small style="color: var(--fi-muted); font-size: 11.5px; display: block; margin-top: 4px;">Thường là 0 VNĐ khi khách tự đến nhận.</small>
                                </div>

                                <div class="form-row">
                                    <label style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                        Địa chỉ nhận hàng tại cửa hàng
                                    </label>
                                    <input 
                                        type="text" 
                                        x-model="settings.pickup_address" 
                                        placeholder="Ví dụ: 123 Đường Nguyễn Trãi, Quận Thanh Xuân, Hà Nội" 
                                        class="form-control"
                                    >
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            {{-- SIDEBAR COLUMN --}}
            <div class="show-sidebar" style="display: flex; flex-direction: column; gap: 20px;">
                
                {{-- CARD: TRẠNG THÁI --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Trạng thái</h3>
                    </div>
                    <div class="card-body">
                        <div style="margin-bottom: 16px;">
                            <label for="is_enabled" style="margin-bottom: 6px; font-weight: 600; font-size: 12.5px; display: block;">
                                Kích hoạt phương thức
                            </label>
                            <select id="is_enabled" name="is_enabled" class="form-control" style="font-size: 13px; font-weight: 500;">
                                <option value="1" @selected(old('is_enabled', $record->is_enabled ?? true))>Bật (Kích hoạt)</option>
                                <option value="0" @selected(!old('is_enabled', $record->is_enabled ?? true))>Tắt (Tạm ngừng)</option>
                            </select>
                        </div>

                        <button class="btn btn-primary" type="submit" form="shipping-method-form" style="width: 100%; height: 38px; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 13.5px; font-weight: 600;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                            Lưu phương thức
                        </button>
                    </div>
                </div>

                {{-- CARD: HƯỚNG DẪN --}}
                <div class="card" style="background: #f8fafc;">
                    <div class="card-header">
                        <h3 class="card-title" style="font-size: 12.5px;">Cơ chế áp dụng</h3>
                    </div>
                    <div class="card-body" style="padding-top: 10px;">
                        <ul style="margin: 0; padding-left: 16px; font-size: 12px; color: var(--fi-muted, #64748b); line-height: 1.6;">
                            <li>Phương thức này chỉ xuất hiện khi địa chỉ giao hàng của khách nằm trong Khu vực vận chuyển tương ứng.</li>
                            <li>Nếu khách có giỏ hàng vượt ngưỡng freeship, cước phí sẽ tự động chuyển về 0 đ.</li>
                            <li>Với nấc khối lượng, hệ thống tự động cộng dồn khối lượng tất cả sản phẩm trong giỏ.</li>
                        </ul>
                    </div>
                </div>

            </div>

        </div>
    </form>
</div>

<script>
function shippingMethodManager(config) {
    return {
        methodType: config.initialType || 'flat_rate',
        settings: Object.assign({
            cost: 30000,
            currency: 'VND',
            estimated_days: 2,
            free_shipping_threshold: null,
            description: '',
            min_amount: null,
            additional_cost_per_kg: 5000,
            rules: [
                { min_weight: 0, max_weight: 1000, cost: 25000 },
                { min_weight: 1001, max_weight: 2000, cost: 35000 }
            ],
            carrier: 'ghn',
            service_id: 'standard',
            markup: 0,
            fallback_cost: 30000,
            pickup_address: ''
        }, config.initialSettings || {}),

        addWeightTier() {
            if (!this.settings.rules) {
                this.settings.rules = [];
            }
            const last = this.settings.rules[this.settings.rules.length - 1];
            const nextMin = last ? (Number(last.max_weight) + 1) : 0;
            const nextMax = nextMin + 1000;
            const nextCost = last ? (Number(last.cost) + 10000) : 30000;
            this.settings.rules.push({ min_weight: nextMin, max_weight: nextMax, cost: nextCost });
        },

        removeWeightTier(index) {
            this.settings.rules.splice(index, 1);
        },

        serializeSettings() {
            return JSON.stringify(this.settings);
        }
    };
}
</script>
@endsection
