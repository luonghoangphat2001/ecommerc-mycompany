@extends('admin.layouts.app', ['title' => $title])

@section('content')
@php
    $products = $formData['products'] ?? [];
    $initialItems = $formData['initialItems'] ?? [];
    $defaultProductId = count($products) > 0 ? $products->first()->id : 'null';
@endphp

<div x-data="comboProductManager({
    initialItems: {{ json_encode($initialItems) }},
    defaultProductId: {{ $defaultProductId }}
})">

    {{-- PAGE HEADER --}}
    <x-admin.page-header
        :title="$record ? 'Chỉnh sửa Combo: ' . ($record->name ?: '#' . $record->id) : 'Tạo mới Combo sản phẩm'"
        :description="'Cấu hình gói combo gồm nhiều sản phẩm với mức giá ưu đãi đặc biệt.'"
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
            <button class="btn btn-primary" type="submit" form="combo-product-form">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                {{ __('admin.actions.save') }}
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- ERROR ALERT --}}
    @if (isset($errors) && $errors->any())
        <div style="background: var(--fi-danger-bg, #fef2f2); color: #991b1b; padding: 12px 16px; border-radius: var(--fi-radius-md, 8px); margin-bottom: 20px; border: 1px solid var(--fi-danger-border, #fecaca);">
            <div style="font-weight: 700; font-size: 13px; margin-bottom: 4px;">Vui lòng kiểm tra lại thông tin:</div>
            <ul style="margin: 0; padding-left: 20px; font-size: 12.5px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="combo-product-form" method="POST" action="{{ $record ? route($routePrefix . '.update', $record->id) : route($routePrefix . '.store') }}">
        @csrf
        @if ($record)
            @method('PUT')
        @endif

        {{-- Serialized JSON synced with Alpine --}}
        <input type="hidden" name="items_json" :value="serializeItems()">

        <div class="edit-layout">
            
            {{-- MAIN COLUMN --}}
            <div class="show-main" style="display: flex; flex-direction: column; gap: 20px;">

                {{-- CARD 1: THÔNG TIN COMBO --}}
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--fi-primary);"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                            <h3 class="card-title">Thông tin gói Combo</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            
                            {{-- TÊN COMBO --}}
                            <div class="form-row" style="grid-column: 1 / -1;">
                                <label for="name" style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                    Tên gói Combo <span style="color: #ef4444;">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    value="{{ old('name', $record->name ?? '') }}" 
                                    placeholder="Ví dụ: Combo Thể Thao Mùa Hè 3 Món..." 
                                    required 
                                    class="form-control"
                                >
                            </div>

                            {{-- SLUG --}}
                            <div class="form-row">
                                <label for="slug" style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                    Đường dẫn (Slug)
                                </label>
                                <input 
                                    type="text" 
                                    id="slug" 
                                    name="slug" 
                                    value="{{ old('slug', $record->slug ?? '') }}" 
                                    placeholder="combo-the-thao-mua-he" 
                                    class="form-control"
                                >
                            </div>

                            {{-- GIÁ COMBO --}}
                            <div class="form-row">
                                <label for="combo_price" style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                    Giá bán Combo (VNĐ) <span style="color: #ef4444;">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    id="combo_price" 
                                    name="combo_price" 
                                    value="{{ old('combo_price', $record->combo_price ?? 0) }}" 
                                    min="0" 
                                    required 
                                    class="form-control" 
                                    style="font-weight: 700; color: var(--fi-primary);"
                                >
                            </div>

                            {{-- % GIẢM GIÁ HIỂN THỊ --}}
                            <div class="form-row">
                                <label for="discount_percent" style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                    % Giảm giá hiển thị
                                </label>
                                <input 
                                    type="number" 
                                    id="discount_percent" 
                                    name="discount_percent" 
                                    value="{{ old('discount_percent', $record->discount_percent ?? '') }}" 
                                    min="0" 
                                    max="100" 
                                    placeholder="Ví dụ: 15" 
                                    class="form-control"
                                >
                            </div>

                            {{-- THỨ TỰ SẮP XẾP --}}
                            <div class="form-row">
                                <label for="sort_order" style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                    Thứ tự hiển thị
                                </label>
                                <input 
                                    type="number" 
                                    id="sort_order" 
                                    name="sort_order" 
                                    value="{{ old('sort_order', $record->sort_order ?? 0) }}" 
                                    min="0" 
                                    class="form-control"
                                >
                            </div>

                            {{-- MÔ TẢ COMBO --}}
                            <div class="form-row" style="grid-column: 1 / -1;">
                                <label for="description" style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                    Mô tả gói Combo
                                </label>
                                <textarea id="description" name="description" rows="3" class="form-control" placeholder="Mô tả ưu đãi và các sản phẩm bao gồm trong combo...">{{ old('description', $record->description ?? '') }}</textarea>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- CARD 2: CÁC SẢN PHẨM TRONG COMBO (TRỰC QUAN - ZERO JSON) --}}
                <div class="card">
                    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--fi-primary);"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                            <h3 class="card-title">
                                Sản phẩm thành phần (<span x-text="items.length"></span>)
                            </h3>
                        </div>

                        <button type="button" @click="addItem()" class="btn btn-secondary" style="font-size: 12.5px; padding: 4px 12px;">
                            + Thêm sản phẩm vào Combo
                        </button>
                    </div>

                    <div class="card-body" style="padding: 0;">
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                <thead>
                                    <tr style="background: #f8fafc; border-bottom: 1px solid var(--fi-border-subtle, #e2e8f0); text-align: left;">
                                        <th style="padding: 10px 14px; font-weight: 600; width: 40px; text-align: center;">#</th>
                                        <th style="padding: 10px 14px; font-weight: 600;">Sản phẩm</th>
                                        <th style="padding: 10px 14px; font-weight: 600; width: 120px;">Số lượng</th>
                                        <th style="padding: 10px 14px; font-weight: 600; width: 100px;">Thứ tự</th>
                                        <th style="padding: 10px 14px; font-weight: 600; width: 60px; text-align: center;">Xóa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr style="border-bottom: 1px solid var(--fi-border-subtle, #f1f5f9);">
                                            <td style="padding: 10px 14px; text-align: center; color: var(--fi-muted); font-size: 12px;" x-text="index + 1"></td>
                                            
                                            <td style="padding: 10px 14px;">
                                                <select x-model.number="item.shop_product_id" class="form-control" style="font-size: 12.5px; height: 34px;">
                                                    @foreach ($products as $p)
                                                        @php
                                                            $pName = is_array($p->name) ? ($p->name['vi'] ?? reset($p->name)) : $p->name;
                                                        @endphp
                                                        <option value="{{ $p->id }}">{{ $pName }} ({{ $p->sku ?: '#' . $p->id }})</option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td style="padding: 10px 14px;">
                                                <input type="number" x-model.number="item.quantity" min="1" class="form-control" style="height: 34px; font-size: 13px; font-weight: 600; text-align: right;">
                                            </td>

                                            <td style="padding: 10px 14px;">
                                                <input type="number" x-model.number="item.sort_order" min="0" class="form-control" style="height: 34px; font-size: 12px; text-align: center;">
                                            </td>

                                            <td style="padding: 10px 14px; text-align: center;">
                                                <button type="button" @click="removeItem(index)" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 4px;" title="Xóa khỏi combo">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>

                                    <tr x-show="items.length === 0">
                                        <td colspan="5" style="padding: 24px; text-align: center; color: var(--fi-muted); font-style: italic;">
                                            Chưa có sản phẩm nào trong Combo. Nhấn <strong>+ Thêm sản phẩm vào Combo</strong> để chọn món.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            {{-- SIDEBAR COLUMN --}}
            <div class="show-sidebar" style="display: flex; flex-direction: column; gap: 20px;">
                
                {{-- CARD: TRẠNG THÁI --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Kích hoạt</h3>
                    </div>
                    <div class="card-body">
                        <div style="margin-bottom: 16px;">
                            <label for="is_active" style="margin-bottom: 6px; font-weight: 600; font-size: 12.5px; display: block;">
                                Trạng thái mở bán
                            </label>
                            <select id="is_active" name="is_active" class="form-control" style="font-size: 13px; font-weight: 500;">
                                <option value="1" @selected(old('is_active', $record->is_active ?? true))>Đang hoạt động (Bật)</option>
                                <option value="0" @selected(!old('is_active', $record->is_active ?? true))>Tạm ẩn (Tắt)</option>
                            </select>
                        </div>

                        <div style="margin-bottom: 16px;">
                            <label for="start_date" style="margin-bottom: 6px; font-weight: 600; font-size: 12.5px; display: block;">
                                Ngày bắt đầu mở bán
                            </label>
                            <input type="datetime-local" id="start_date" name="start_date" value="{{ old('start_date', $record?->start_date?->format('Y-m-d\TH:i')) }}" class="form-control">
                        </div>

                        <div style="margin-bottom: 16px;">
                            <label for="end_date" style="margin-bottom: 6px; font-weight: 600; font-size: 12.5px; display: block;">
                                Ngày kết thúc mở bán
                            </label>
                            <input type="datetime-local" id="end_date" name="end_date" value="{{ old('end_date', $record?->end_date?->format('Y-m-d\TH:i')) }}" class="form-control">
                        </div>

                        <button class="btn btn-primary" type="submit" form="combo-product-form" style="width: 100%; height: 38px; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 13.5px; font-weight: 600;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                            Lưu gói Combo
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </form>
</div>

<script>
function comboProductManager(config) {
    return {
        items: Array.isArray(config.initialItems) && config.initialItems.length > 0 ? config.initialItems : [
            { shop_product_id: config.defaultProductId, quantity: 1, sort_order: 0 }
        ],

        addItem() {
            this.items.push({
                shop_product_id: config.defaultProductId,
                quantity: 1,
                sort_order: this.items.length
            });
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        serializeItems() {
            return JSON.stringify(this.items);
        }
    };
}
</script>
@endsection

