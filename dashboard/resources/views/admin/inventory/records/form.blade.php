@extends('admin.layouts.app', ['title' => $title])

@section('content')
@php
    $products = $formData['products'] ?? [];
    $warehouses = $formData['warehouses'] ?? [];
    $initialItems = $formData['initialItems'] ?? [];
    $currentType = $formData['currentType'] ?? 'IN';
    $currentStatus = $formData['currentStatus'] ?? 'DRAFT';
@endphp

<div x-data="inventoryRecordManager({
    initialType: '{{ $currentType }}',
    initialItems: {{ json_encode($initialItems) }},
    defaultProductId: {{ count($products) > 0 ? $products->first()->id : 'null' }},
    defaultWarehouseId: {{ count($warehouses) > 0 ? $warehouses->first()->id : 'null' }}
})">

    {{-- PAGE HEADER --}}
    <x-admin.page-header
        :title="$record ? 'Chỉnh sửa Phiếu kho: #' . $record->id . ' (' . ($record->type instanceof \BackedEnum ? $record->type->labelVi() : $record->type) . ')' : 'Tạo mới Phiếu xuất nhập kho'"
        :description="'Quản lý phiếu nhập kho, xuất kho và điều chuyển nội bộ giữa các chi nhánh.'"
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
            @if ($record && ($record->status instanceof \BackedEnum ? $record->status->value : $record->status) === 'DRAFT')
                <button type="submit" form="process-record-form" class="btn btn-secondary" style="color: #15803d; border-color: #bbf7d0; background: #f0fdf4;" onclick="return confirm('Bạn có chắc chắn muốn xử lý phiếu kho này? Tồn kho thực tế sẽ được cập nhật ngay lập tức.');">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Xử lý & Cập nhật kho
                </button>
            @endif
            <button class="btn btn-primary" type="submit" form="inventory-record-form">
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
                :active="$activeTab ?? 'inventory-records'"
                :tabs="$tabs"
            />
        </div>
    @endif

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

    <form id="inventory-record-form" method="POST" action="{{ $record ? route($routePrefix . '.update', $record->id) : route($routePrefix . '.store') }}">
        @csrf
        @if ($record)
            @method('PUT')
        @endif

        {{-- Serialized JSON synced with Alpine --}}
        <input type="hidden" name="items_json" :value="serializeItems()">

        <div class="edit-layout">
            
            {{-- MAIN COLUMN --}}
            <div class="show-main" style="display: flex; flex-direction: column; gap: 20px;">

                {{-- CARD 1: THÔNG TIN PHIẾU KHO --}}
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--fi-primary);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            <h3 class="card-title">Thông tin phiếu kho</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            
                            {{-- LOẠI PHIẾU KHO --}}
                            <div class="form-row">
                                <label for="type" style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                    Loại phiếu kho <span style="color: #ef4444;">*</span>
                                </label>
                                <select id="type" name="type" x-model="recordType" class="form-control" style="font-weight: 600;">
                                    <option value="IN">Nhập kho (Stock In)</option>
                                    <option value="OUT">Xuất kho (Stock Out)</option>
                                    <option value="TRANSFER">Điều chuyển kho (Transfer)</option>
                                </select>
                            </div>

                            {{-- GHI CHÚ PHIẾU --}}
                            <div class="form-row" style="grid-column: 1 / -1;">
                                <label for="notes" style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                    Ghi chú / Lý do xuất nhập
                                </label>
                                <textarea id="notes" name="notes" rows="2" class="form-control" placeholder="Ví dụ: Nhập hàng từ nhà cung cấp, xuất kho chuyển chi nhánh...">{{ old('notes', $record->notes ?? '') }}</textarea>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- CARD 2: CHI TIẾT SẢN PHẨM (TRỰC QUAN - KHÔNG DÙNG JSON) --}}
                <div class="card">
                    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--fi-primary);"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                            <h3 class="card-title">
                                Danh sách mặt hàng (<span x-text="items.length"></span>)
                            </h3>
                        </div>

                        <button type="button" @click="addItem()" class="btn btn-secondary" style="font-size: 12.5px; padding: 4px 12px;">
                            + Thêm sản phẩm
                        </button>
                    </div>

                    <div class="card-body" style="padding: 0;">
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                <thead>
                                    <tr style="background: #f8fafc; border-bottom: 1px solid var(--fi-border-subtle, #e2e8f0); text-align: left;">
                                        <th style="padding: 10px 14px; font-weight: 600; width: 40px; text-align: center;">#</th>
                                        <th style="padding: 10px 14px; font-weight: 600; min-width: 220px;">Sản phẩm</th>
                                        <th style="padding: 10px 14px; font-weight: 600; min-width: 180px;">
                                            <span x-text="recordType === 'IN' ? 'Kho nhập' : (recordType === 'TRANSFER' ? 'Kho xuất (Nguồn)' : 'Kho xuất')"></span>
                                        </th>
                                        <th x-show="recordType === 'TRANSFER'" style="padding: 10px 14px; font-weight: 600; min-width: 180px;">
                                            Kho đích
                                        </th>
                                        <th style="padding: 10px 14px; font-weight: 600; width: 110px;">Số lượng</th>
                                        <th style="padding: 10px 14px; font-weight: 600; width: 60px; text-align: center;">Xóa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr style="border-bottom: 1px solid var(--fi-border-subtle, #f1f5f9);">
                                            {{-- STT --}}
                                            <td style="padding: 10px 14px; text-align: center; color: var(--fi-muted); font-size: 12px;" x-text="index + 1"></td>

                                            {{-- SẢN PHẨM --}}
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

                                            {{-- KHO NGUỒN / KHO ÁP DỤNG --}}
                                            <td style="padding: 10px 14px;">
                                                <select x-model.number="item.warehouse_id" class="form-control" style="font-size: 12.5px; height: 34px;">
                                                    @foreach ($warehouses as $w)
                                                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            {{-- KHO ĐÍCH (KHI TRANSFER) --}}
                                            <td x-show="recordType === 'TRANSFER'" style="padding: 10px 14px;">
                                                <select x-model.number="item.target_warehouse_id" class="form-control" style="font-size: 12.5px; height: 34px;">
                                                    <option value="">-- Chọn kho nhận --</option>
                                                    @foreach ($warehouses as $w)
                                                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            {{-- SỐ LƯỢNG --}}
                                            <td style="padding: 10px 14px;">
                                                <input type="number" x-model.number="item.quantity" min="1" class="form-control" style="height: 34px; font-size: 13px; font-weight: 600; text-align: right;">
                                            </td>

                                            {{-- NÚT XÓA --}}
                                            <td style="padding: 10px 14px; text-align: center;">
                                                <button type="button" @click="removeItem(index)" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 4px;" title="Xóa dòng này">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>

                                    {{-- EMPTY STATE --}}
                                    <tr x-show="items.length === 0">
                                        <td colspan="6" style="padding: 24px; text-align: center; color: var(--fi-muted); font-style: italic;">
                                            Chưa có mặt hàng nào. Nhấn <strong>+ Thêm sản phẩm</strong> ở trên để thêm dòng mới.
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
                        <h3 class="card-title">Trạng thái phiếu</h3>
                    </div>
                    <div class="card-body">
                        <div style="margin-bottom: 16px;">
                            <label for="status" style="margin-bottom: 6px; font-weight: 600; font-size: 12.5px; display: block;">
                                Tình trạng xử lý
                            </label>
                            <select id="status" name="status" class="form-control" style="font-size: 13px; font-weight: 500;">
                                <option value="DRAFT" @selected(old('status', $currentStatus) === 'DRAFT')>Bản nháp (DRAFT)</option>
                                <option value="COMPLETED" @selected(old('status', $currentStatus) === 'COMPLETED')>Hoàn thành (COMPLETED)</option>
                            </select>
                        </div>

                        <button class="btn btn-primary" type="submit" form="inventory-record-form" style="width: 100%; height: 38px; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 13.5px; font-weight: 600;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                            Lưu phiếu kho
                        </button>
                    </div>
                </div>

                {{-- CARD: HƯỚNG DẪN --}}
                <div class="card" style="background: #f8fafc;">
                    <div class="card-header">
                        <h3 class="card-title" style="font-size: 12.5px;">Cơ chế cập nhật kho</h3>
                    </div>
                    <div class="card-body" style="padding-top: 10px;">
                        <ul style="margin: 0; padding-left: 16px; font-size: 12px; color: var(--fi-muted, #64748b); line-height: 1.6;">
                            <li><strong>Nhập kho (IN):</strong> Cộng tăng số lượng tồn vào kho được chọn.</li>
                            <li><strong>Xuất kho (OUT):</strong> Trừ giảm số lượng tồn kho tương ứng.</li>
                            <li><strong>Điều chuyển (TRANSFER):</strong> Giảm tồn kho nguồn và cộng tồn kho đích.</li>
                            <li>Số lượng thực tế chỉ thay đổi khi phiếu chuyển sang trạng thái <strong>Hoàn thành (COMPLETED)</strong>.</li>
                        </ul>
                    </div>
                </div>

            </div>

        </div>
    </form>

    {{-- PROCESS FORM (FOR ONE-CLICK PROCESS) --}}
    @if ($record)
        <form id="process-record-form" method="POST" action="{{ route('admin.inventory-records.process', $record->id) }}" style="display: none;">
            @csrf
        </form>
    @endif
</div>

<script>
function inventoryRecordManager(config) {
    return {
        recordType: config.initialType || 'IN',
        items: Array.isArray(config.initialItems) && config.initialItems.length > 0 ? config.initialItems : [
            {
                shop_product_id: config.defaultProductId,
                warehouse_id: config.defaultWarehouseId,
                target_warehouse_id: null,
                quantity: 1
            }
        ],

        addItem() {
            this.items.push({
                shop_product_id: config.defaultProductId,
                warehouse_id: config.defaultWarehouseId,
                target_warehouse_id: null,
                quantity: 1
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

