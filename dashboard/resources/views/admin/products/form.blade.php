@extends('admin.layouts.app', ['title' => $title])

@section('content')
@php
    $productType = old('type', $record->type?->value ?? ($formData['type'] ?? 'simple'));
    if ($productType === 'deliverable') {
        $productType = 'simple';
    }
    $allProducts = \App\Models\Product::where('id', '!=', $record->id ?? 0)->where('is_visible', true)->orderBy('name')->get(['id', 'name', 'sku', 'price', 'type', 'qty', 'total_stock']);
    $allAttributes = \App\Models\Attribute::orderBy('name')->get();
    $taxClasses = \App\Models\TaxClass::with('rates')->get();
@endphp

    <x-admin.page-header
        :title="$title"
        :description="$record ? 'Chỉnh sửa và cập nhật sản phẩm #' . $record->id : 'Tạo mới sản phẩm trong hệ thống (Simple, Variable, Combo)'"
        :backUrl="route($routePrefix . '.index')"
    >
        <x-slot:actions>
            <a class="btn btn-secondary" href="{{ route($routePrefix . '.index') }}">
                &larr; {{ __('admin.actions.cancel') }}
            </a>
            @if ($record && \Illuminate\Support\Facades\Route::has($routePrefix . '.show'))
                <a class="btn btn-secondary" href="{{ route($routePrefix . '.show', $record->id) }}">
                    {{ __('admin.actions.view') }}
                </a>
            @endif
            <button class="btn btn-primary" type="submit" form="product-main-form" id="btn-save-product">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                {{ __('admin.actions.save') }}
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    @if (isset($errors) && $errors->any())
        <div style="background: var(--fi-danger-bg, #fef2f2); color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; border: 1px solid var(--fi-danger-border, #fecaca);">
            <div style="font-weight: 700; font-size: 13px; margin-bottom: 4px;">Vui lòng kiểm tra lại dữ liệu đã nhập:</div>
            <ul style="margin: 0; padding-left: 20px; font-size: 12.5px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="product-main-form" method="post" action="{{ $record ? route($routePrefix . '.update', $record->id) : route($routePrefix . '.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($record)
            @method('put')
        @endif

        <input type="hidden" name="attributes_json" id="input_attributes_json" value="{{ old('attributes_json', $formData['attributes_json'] ?? '[]') }}">
        <input type="hidden" name="variants_json" id="input_variants_json" value="{{ old('variants_json', $formData['variants_json'] ?? '[]') }}">
        <input type="hidden" name="combo_items_json" id="input_combo_items_json" value="{{ old('combo_items_json', $formData['combo_items_json'] ?? '[]') }}">

        <!-- 1. Product Type Selector Banner -->
        <div style="background: var(--fi-card-bg, #ffffff); border: 1px solid var(--fi-border-color, #e5e7eb); border-radius: 12px; padding: 18px 24px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div>
                    <label style="display: block; font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">
                        Loại sản phẩm (Product Type) <span style="color: #ef4444;">*</span>
                    </label>
                    <p style="margin: 0; font-size: 12.5px; color: #64748b;">
                        Chọn cấu hình sản phẩm phù hợp. Hệ thống sẽ tự động hiển thị các phần quản lý tương ứng.
                    </p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <label class="type-pill" style="cursor: pointer; display: flex; align-items: center; gap: 8px; padding: 8px 16px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 13px; font-weight: 600;">
                        <input type="radio" name="type" value="simple" {{ in_array($productType, ['simple', 'deliverable']) ? 'checked' : '' }} onchange="switchProductType('simple')">
                        <span>Đơn giản (Simple)</span>
                    </label>
                    <label class="type-pill" style="cursor: pointer; display: flex; align-items: center; gap: 8px; padding: 8px 16px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 13px; font-weight: 600;">
                        <input type="radio" name="type" value="variable" {{ $productType === 'variable' ? 'checked' : '' }} onchange="switchProductType('variable')">
                        <span>Có biến thể (Variable)</span>
                    </label>
                    <label class="type-pill" style="cursor: pointer; display: flex; align-items: center; gap: 8px; padding: 8px 16px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 13px; font-weight: 600;">
                        <input type="radio" name="type" value="combo" {{ $productType === 'combo' ? 'checked' : '' }} onchange="switchProductType('combo')">
                        <span>Combo / Gói (Bundle)</span>
                    </label>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;">
            <!-- Main Column -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <!-- General Info Section -->
                <div class="crud-card" style="background: var(--fi-card-bg, #ffffff); border: 1px solid var(--fi-border-color, #e5e7eb); border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h3 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Thông tin chung (General Information)
                    </h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div style="grid-column: span 2;">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                Tên sản phẩm <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" name="name" id="field_name" value="{{ old('name', is_array($record->name ?? null) ? ($record->name['vi'] ?? '') : ($record->name ?? '')) }}" class="fi-input" required style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 12px; font-size: 13.5px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                Mã SKU <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" name="sku" id="field_sku" value="{{ old('sku', $record->sku ?? '') }}" class="fi-input" required style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 12px; font-size: 13.5px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                Đường dẫn (Slug) <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" name="slug" id="field_slug" value="{{ old('slug', $record->slug ?? '') }}" class="fi-input" required style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 12px; font-size: 13.5px;">
                        </div>
                        <div style="grid-column: span 2;">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                Mô tả chi tiết
                            </label>
                            <textarea name="description" id="field_description" rows="4" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; font-size: 13px;">{{ old('description', is_array($record->description ?? null) ? ($record->description['vi'] ?? '') : ($record->description ?? '')) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Section: Attributes (Variable Products Only) -->
                <div id="section-attributes" class="crud-card" style="display: none; background: var(--fi-card-bg, #ffffff); border: 1px solid var(--fi-border-color, #e5e7eb); border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                        <div>
                            <h3 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 4px 0;">
                                Thuộc tính sản phẩm (Attributes)
                            </h3>
                            <p style="margin: 0; font-size: 12px; color: #64748b;">
                                Thêm các thuộc tính như Kích thước (Size), Màu sắc (Color). Sau đó nhấn "Tạo biến thể".
                            </p>
                        </div>
                        <button type="button" class="btn btn-secondary" onclick="addNewAttributeRow()" style="font-size: 12px; padding: 6px 12px;">
                            + Thêm thuộc tính
                        </button>
                    </div>
                    <div id="attributes-container" style="display: flex; flex-direction: column; gap: 12px;">
                        <!-- Rendered by JS -->
                    </div>
                    <div style="margin-top: 14px; text-align: right;">
                        <button type="button" class="btn btn-primary" onclick="generateVariantsFromAttributes()" style="font-size: 12.5px; padding: 8px 16px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align: middle; margin-right: 4px;"><path d="M12 2v20M2 12h20"/></svg>
                            Tạo tổ hợp biến thể (Generate Variants)
                        </button>
                    </div>
                </div>

                <!-- Section: Variants Matrix (Variable Products Only) -->
                <div id="section-variants" class="crud-card" style="display: none; background: var(--fi-card-bg, #ffffff); border: 1px solid var(--fi-border-color, #e5e7eb); border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <h3 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 4px 0;">
                                Danh sách biến thể (Variants)
                            </h3>
                            <p style="margin: 0; font-size: 12px; color: #64748b;">
                                Quản lý giá, SKU, tồn kho và kích hoạt riêng cho từng biến thể.
                            </p>
                        </div>
                        <!-- Bulk updater -->
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="number" id="bulk_variant_price" placeholder="Giá áp dụng chung" style="height: 32px; width: 130px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 8px;">
                            <button type="button" class="btn btn-secondary" onclick="applyBulkVariantPrice()" style="font-size: 11.5px; padding: 6px 10px;">Áp dụng giá</button>
                            <input type="number" id="bulk_variant_stock" placeholder="Kho áp dụng chung" style="height: 32px; width: 120px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 8px;">
                            <button type="button" class="btn btn-secondary" onclick="applyBulkVariantStock()" style="font-size: 11.5px; padding: 6px 10px;">Áp dụng kho</button>
                        </div>
                    </div>

                    <div style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12.5px; text-align: left;">
                            <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <tr>
                                    <th style="padding: 10px 12px;">Thuộc tính</th>
                                    <th style="padding: 10px 12px;">SKU</th>
                                    <th style="padding: 10px 12px;">Giá (VND)</th>
                                    <th style="padding: 10px 12px;">Giá khuyến mãi</th>
                                    <th style="padding: 10px 12px;">Tồn kho</th>
                                    <th style="padding: 10px 12px; text-align: center;">Kích hoạt</th>
                                    <th style="padding: 10px 12px; text-align: center;">Xóa</th>
                                </tr>
                            </thead>
                            <tbody id="variants-table-body">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section: Combo Components (Combo Products Only) -->
                <div id="section-combo-items" class="crud-card" style="display: none; background: var(--fi-card-bg, #ffffff); border: 1px solid var(--fi-border-color, #e5e7eb); border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                        <div>
                            <h3 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 4px 0;">
                                Thành phần Combo (Combo Components)
                            </h3>
                            <p style="margin: 0; font-size: 12px; color: #64748b;">
                                Chọn các sản phẩm / biến thể cấu thành combo. Tồn kho combo sẽ tự động giới hạn theo thành phần ít nhất.
                            </p>
                        </div>
                        <button type="button" class="btn btn-secondary" onclick="addComboItemRow()" style="font-size: 12px; padding: 6px 12px;">
                            + Thêm sản phẩm vào combo
                        </button>
                    </div>

                    <div style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12.5px; text-align: left;">
                            <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <tr>
                                    <th style="padding: 10px 12px;">Sản phẩm thành phần</th>
                                    <th style="padding: 10px 12px; width: 100px;">Số lượng</th>
                                    <th style="padding: 10px 12px;">Đơn giá gốc</th>
                                    <th style="padding: 10px 12px;">Tồn kho hiện có</th>
                                    <th style="padding: 10px 12px; text-align: center;">Xóa</th>
                                </tr>
                            </thead>
                            <tbody id="combo-items-table-body">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top: 14px; padding: 12px 16px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 13px; font-weight: 600; color: #166534;">
                            Tổng giá trị các món gốc: <strong id="combo_nominal_total">0 VND</strong>
                        </span>
                        <span style="font-size: 13px; font-weight: 600; color: #166534;">
                            Tồn kho combo khả dụng ước tính: <strong id="combo_available_stock_calc">0</strong>
                        </span>
                    </div>
                </div>

                <!-- Section: Pricing -->
                <div id="section-pricing" class="crud-card" style="background: var(--fi-card-bg, #ffffff); border: 1px solid var(--fi-border-color, #e5e7eb); border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h3 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 16px 0;">
                        Giá bán & Khuyến mãi (Pricing)
                    </h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                Giá bán tiêu chuẩn (VND) <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="number" name="price" id="field_price" value="{{ old('price', $record->price ?? 0) }}" class="fi-input" style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 12px; font-size: 13.5px;">
                            <span id="price_hint" style="font-size: 11.5px; color: #64748b; margin-top: 4px; display: block;">Đối với sản phẩm có biến thể, giá này là giá cơ sở (hoặc tối thiểu).</span>
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                Giá khuyến mãi (Sale Price)
                            </label>
                            <input type="number" name="sale_price" id="field_sale_price" value="{{ old('sale_price', $record->sale_price ?? '') }}" class="fi-input" style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 12px; font-size: 13.5px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                Ngày bắt đầu KM
                            </label>
                            <input type="datetime-local" name="sale_start_date" value="{{ old('sale_start_date', $record->sale_start_date ? $record->sale_start_date->format('Y-m-d\TH:i') : '') }}" style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 12px; font-size: 13px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                Ngày kết thúc KM
                            </label>
                            <input type="datetime-local" name="sale_end_date" value="{{ old('sale_end_date', $record->sale_end_date ? $record->sale_end_date->format('Y-m-d\TH:i') : '') }}" style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 12px; font-size: 13px;">
                        </div>
                    </div>
                </div>

                <!-- Section: Inventory (Simple & Variable base) -->
                <div id="section-inventory" class="crud-card" style="background: var(--fi-card-bg, #ffffff); border: 1px solid var(--fi-border-color, #e5e7eb); border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h3 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 16px 0;">
                        Quản lý kho hàng (Inventory)
                    </h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div id="wrapper_qty">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                Số lượng tồn kho (Quantity)
                            </label>
                            <input type="number" name="qty" id="field_qty" value="{{ old('qty', $record->qty ?? 0) }}" class="fi-input" style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 12px; font-size: 13.5px;">
                            <span id="qty_hint" style="font-size: 11.5px; color: #64748b; margin-top: 4px; display: block;">Đối với sản phẩm có biến thể hoặc combo, số lượng sẽ được tính tự động.</span>
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                Ngưỡng tồn an toàn (Security Stock)
                            </label>
                            <input type="number" name="security_stock" value="{{ old('security_stock', $record->security_stock ?? 0) }}" class="fi-input" style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 12px; font-size: 13.5px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                Cho phép đặt hàng trước (Backorder)
                            </label>
                            <select name="backorder" style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 12px; font-size: 13px;">
                                <option value="0" {{ old('backorder', $record->backorder ?? 0) == 0 ? 'selected' : '' }}>Không cho phép</option>
                                <option value="1" {{ old('backorder', $record->backorder ?? 0) == 1 ? 'selected' : '' }}>Cho phép đặt trước</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section: Tax Settings -->
                <div id="section-tax" class="crud-card" style="background: var(--fi-card-bg, #ffffff); border: 1px solid var(--fi-border-color, #e5e7eb); border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h3 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 16px 0;">
                        Cấu hình Thuế (Tax Settings)
                    </h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                Áp dụng thuế (Apply Tax)
                            </label>
                            <select name="apply_tax" style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 12px; font-size: 13px;">
                                <option value="1" {{ old('apply_tax', $record->apply_tax ?? 1) == 1 ? 'selected' : '' }}>Có (Áp dụng thuế)</option>
                                <option value="0" {{ old('apply_tax', $record->apply_tax ?? 1) == 0 ? 'selected' : '' }}>Không (Miễn thuế)</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                Nhóm thuế (Tax Class)
                            </label>
                            <select name="tax_class_id" style="width: 100%; height: 40px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 12px; font-size: 13px;">
                                <option value="">-- Mặc định hệ thống / Không nhóm --</option>
                                @foreach ($taxClasses as $tc)
                                    <option value="{{ $tc->id }}" {{ old('tax_class_id', $record->tax_class_id ?? '') == $tc->id ? 'selected' : '' }}>
                                        {{ $tc->name }} ({{ $tc->rates->first()?->rate ?? 0 }}%)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <p style="margin: 12px 0 0 0; font-size: 12px; color: #64748b; line-height: 1.5;">
                        * <em>Combo Product:</em> Khi các món thành phần có thuế suất khác nhau, hệ thống tự động phân bổ thuế chính xác theo từng món thành phần.
                    </p>
                </div>
            </div>

            <!-- Sidebar Column -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <!-- Visibility & Status Card -->
                <div style="background: var(--fi-card-bg, #ffffff); border: 1px solid var(--fi-border-color, #e5e7eb); border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h4 style="font-size: 13.5px; font-weight: 700; color: #1e293b; margin: 0 0 14px 0;">Hiển thị & Trạng thái</h4>
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <div>
                            <label style="display: block; font-size: 12.5px; font-weight: 600; color: #475569; margin-bottom: 4px;">Công khai trên website</label>
                            <select name="is_visible" style="width: 100%; height: 36px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 13px;">
                                <option value="1" {{ old('is_visible', $record->is_visible ?? 1) == 1 ? 'selected' : '' }}>Hiển thị</option>
                                <option value="0" {{ old('is_visible', $record->is_visible ?? 1) == 0 ? 'selected' : '' }}>Ẩn</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 12.5px; font-weight: 600; color: #475569; margin-bottom: 4px;">Sản phẩm nổi bật (Featured)</label>
                            <select name="featured" style="width: 100%; height: 36px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 13px;">
                                <option value="0" {{ old('featured', $record->featured ?? 0) == 0 ? 'selected' : '' }}>Không</option>
                                <option value="1" {{ old('featured', $record->featured ?? 0) == 1 ? 'selected' : '' }}>Nổi bật</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 12.5px; font-weight: 600; color: #475569; margin-bottom: 4px;">Yêu cầu vận chuyển</label>
                            <select name="requires_shipping" style="width: 100%; height: 36px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 13px;">
                                <option value="1" {{ old('requires_shipping', $record->requires_shipping ?? 1) == 1 ? 'selected' : '' }}>Có vận chuyển</option>
                                <option value="0" {{ old('requires_shipping', $record->requires_shipping ?? 1) == 0 ? 'selected' : '' }}>Không</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Brand & Categories -->
                <div style="background: var(--fi-card-bg, #ffffff); border: 1px solid var(--fi-border-color, #e5e7eb); border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h4 style="font-size: 13.5px; font-weight: 700; color: #1e293b; margin: 0 0 14px 0;">Phân loại & Thương hiệu</h4>
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <div>
                            <label style="display: block; font-size: 12.5px; font-weight: 600; color: #475569; margin-bottom: 4px;">Thương hiệu (Brand)</label>
                            <select name="shop_brand_id" style="width: 100%; height: 36px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 13px;">
                                <option value="">-- Chọn thương hiệu --</option>
                                @foreach (\App\Models\Brand::all() as $b)
                                    <option value="{{ $b->id }}" {{ old('shop_brand_id', $record->shop_brand_id ?? '') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 12.5px; font-weight: 600; color: #475569; margin-bottom: 4px;">Danh mục (Category)</label>
                            <select name="categories[]" multiple style="width: 100%; height: 100px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 10px; font-size: 12.5px;">
                                @php $selectedCats = $formData['categories'] ?? []; @endphp
                                @foreach (\App\Models\ProductCategory::all() as $cat)
                                    <option value="{{ $cat->id }}" {{ in_array($cat->id, $selectedCats) ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Media Image Card -->
                <div style="background: var(--fi-card-bg, #ffffff); border: 1px solid var(--fi-border-color, #e5e7eb); border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h4 style="font-size: 13.5px; font-weight: 700; color: #1e293b; margin: 0 0 14px 0;">Ảnh đại diện sản phẩm</h4>
                    @if ($formData['image'] ?? null)
                        <div style="margin-bottom: 12px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; width: 100%; height: 160px; background: #f8fafc; display: flex; align-items: center; justify-content: center;">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($formData['image']) }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        </div>
                    @endif
                    <input type="file" name="image" accept="image/*" style="font-size: 12.5px; width: 100%;">
                </div>
            </div>
        </div>
    </form>

    <script>
        const catalogProducts = @json($allProducts);
        let currentProductType = "{{ $productType }}";
        let attributesData = {!! old('attributes_json', $formData['attributes_json'] ?? '[]') !!};
        let variantsData = {!! old('variants_json', $formData['variants_json'] ?? '[]') !!};
        let comboItemsData = {!! old('combo_items_json', $formData['combo_items_json'] ?? '[]') !!};

        function switchProductType(type) {
            currentProductType = type;
            const secAttr = document.getElementById('section-attributes');
            const secVar = document.getElementById('section-variants');
            const secCombo = document.getElementById('section-combo-items');
            const qtyWrapper = document.getElementById('wrapper_qty');
            const priceHint = document.getElementById('price_hint');

            if (type === 'variable') {
                secAttr.style.display = 'block';
                secVar.style.display = 'block';
                secCombo.style.display = 'none';
                if (priceHint) priceHint.textContent = 'Giá tối thiểu hoặc cơ sở của biến thể.';
            } else if (type === 'combo') {
                secAttr.style.display = 'none';
                secVar.style.display = 'none';
                secCombo.style.display = 'block';
                if (priceHint) priceHint.textContent = 'Giá bán trọn gói của combo (thường có chiết khấu so với mua lẻ).';
                recalculateComboSummary();
            } else {
                secAttr.style.display = 'none';
                secVar.style.display = 'none';
                secCombo.style.display = 'none';
                if (priceHint) priceHint.textContent = 'Giá bán sản phẩm đơn lẻ.';
            }
        }

        // ==================== ATTRIBUTE & VARIANT MANAGEMENT ====================
        function renderAttributes() {
            const container = document.getElementById('attributes-container');
            container.innerHTML = '';
            if (attributesData.length === 0) {
                container.innerHTML = '<div style="font-size: 12.5px; color: #94a3b8; font-style: italic;">Chưa có thuộc tính nào được thêm. Nhấn "+ Thêm thuộc tính" để bắt đầu.</div>';
                return;
            }

            attributesData.forEach((attr, idx) => {
                const div = document.createElement('div');
                div.style.cssText = 'display: grid; grid-template-columns: 180px 1fr 40px; gap: 10px; align-items: center; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;';
                div.innerHTML = `
                    <input type="text" placeholder="Tên (VD: Size, Màu)" value="${attr.name || ''}" onchange="updateAttributeName(${idx}, this.value)" style="height: 36px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 13px;">
                    <input type="text" placeholder="Các giá trị cách nhau bằng dấu phẩy (VD: S, M, L hoặc Đen, Trắng)" value="${(attr.options || []).join(', ')}" onchange="updateAttributeOptions(${idx}, this.value)" style="height: 36px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 10px; font-size: 13px;">
                    <button type="button" onclick="removeAttributeRow(${idx})" style="color: #ef4444; background: none; border: none; cursor: pointer; font-size: 18px; font-weight: bold;">&times;</button>
                `;
                container.appendChild(div);
            });
            syncAttributesJson();
        }

        function addNewAttributeRow() {
            attributesData.push({ name: '', options: [] });
            renderAttributes();
        }

        function removeAttributeRow(idx) {
            attributesData.splice(idx, 1);
            renderAttributes();
        }

        function updateAttributeName(idx, val) {
            attributesData[idx].name = val.trim();
            syncAttributesJson();
        }

        function updateAttributeOptions(idx, val) {
            attributesData[idx].options = val.split(',').map(s => s.trim()).filter(s => s.length > 0);
            syncAttributesJson();
        }

        function syncAttributesJson() {
            document.getElementById('input_attributes_json').value = JSON.stringify(attributesData);
        }

        function generateVariantsFromAttributes() {
            if (attributesData.length === 0) {
                alert('Vui lòng thêm ít nhất một thuộc tính.');
                return;
            }

            // Cartesian product of options
            const arrays = attributesData.map(a => a.options.map(opt => ({ [a.name]: opt })));
            if (arrays.some(arr => arr.length === 0)) {
                alert('Vui lòng nhập các tùy chọn giá trị cho mỗi thuộc tính.');
                return;
            }

            const combinations = arrays.reduce((acc, curr) => {
                const res = [];
                acc.forEach(a => {
                    curr.forEach(b => {
                        res.push(Object.assign({}, a, b));
                    });
                });
                return res;
            }, [{}]);

            const baseSku = document.getElementById('field_sku').value.trim() || 'PROD';
            const basePrice = parseInt(document.getElementById('field_price').value) || 0;

            variantsData = combinations.map((combo, i) => {
                const comboStr = Object.values(combo).join('-');
                return {
                    id: null,
                    sku: `${baseSku}-${comboStr}`.toUpperCase(),
                    attributes: combo,
                    price: basePrice,
                    sale_price: null,
                    qty: 10,
                    is_active: true
                };
            });

            renderVariantsTable();
        }

        function renderVariantsTable() {
            const tbody = document.getElementById('variants-table-body');
            tbody.innerHTML = '';

            if (variantsData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px; color: #94a3b8; font-style: italic;">Chưa có biến thể nào được tạo.</td></tr>';
                return;
            }

            variantsData.forEach((v, idx) => {
                const attrText = Object.entries(v.attributes || {}).map(([k, val]) => `<strong>${k}:</strong> ${val}`).join(', ');
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid #f1f5f9';
                tr.innerHTML = `
                    <td style="padding: 8px 12px;">${attrText}</td>
                    <td style="padding: 8px 12px;"><input type="text" value="${v.sku || ''}" onchange="updateVariantField(${idx}, 'sku', this.value)" style="height: 32px; width: 130px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; padding: 0 6px;"></td>
                    <td style="padding: 8px 12px;"><input type="number" value="${v.price || 0}" onchange="updateVariantField(${idx}, 'price', this.value)" style="height: 32px; width: 110px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; padding: 0 6px;"></td>
                    <td style="padding: 8px 12px;"><input type="number" value="${v.sale_price || ''}" placeholder="None" onchange="updateVariantField(${idx}, 'sale_price', this.value)" style="height: 32px; width: 110px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; padding: 0 6px;"></td>
                    <td style="padding: 8px 12px;"><input type="number" value="${v.qty || 0}" onchange="updateVariantField(${idx}, 'qty', this.value)" style="height: 32px; width: 80px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; padding: 0 6px;"></td>
                    <td style="padding: 8px 12px; text-align: center;"><input type="checkbox" ${v.is_active ? 'checked' : ''} onchange="updateVariantField(${idx}, 'is_active', this.checked)"></td>
                    <td style="padding: 8px 12px; text-align: center;"><button type="button" onclick="removeVariantRow(${idx})" style="color: #ef4444; background: none; border: none; cursor: pointer;">&times;</button></td>
                `;
                tbody.appendChild(tr);
            });

            syncVariantsJson();
        }

        function updateVariantField(idx, field, val) {
            if (field === 'price' || field === 'qty') {
                variantsData[idx][field] = parseInt(val) || 0;
            } else if (field === 'sale_price') {
                variantsData[idx][field] = val ? parseInt(val) : null;
            } else if (field === 'is_active') {
                variantsData[idx][field] = !!val;
            } else {
                variantsData[idx][field] = val;
            }
            syncVariantsJson();
        }

        function removeVariantRow(idx) {
            variantsData.splice(idx, 1);
            renderVariantsTable();
        }

        function applyBulkVariantPrice() {
            const val = parseInt(document.getElementById('bulk_variant_price').value);
            if (isNaN(val)) return;
            variantsData.forEach(v => v.price = val);
            renderVariantsTable();
        }

        function applyBulkVariantStock() {
            const val = parseInt(document.getElementById('bulk_variant_stock').value);
            if (isNaN(val)) return;
            variantsData.forEach(v => v.qty = val);
            renderVariantsTable();
        }

        function syncVariantsJson() {
            document.getElementById('input_variants_json').value = JSON.stringify(variantsData);
        }

        // ==================== COMBO COMPONENTS MANAGEMENT ====================
        function renderComboItems() {
            const tbody = document.getElementById('combo-items-table-body');
            tbody.innerHTML = '';

            if (comboItemsData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #94a3b8; font-style: italic;">Chưa có món thành phần nào. Nhấn "+ Thêm sản phẩm vào combo" để chọn.</td></tr>';
                recalculateComboSummary();
                return;
            }

            comboItemsData.forEach((item, idx) => {
                const prod = catalogProducts.find(p => p.id == item.shop_product_id);
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid #f1f5f9';
                tr.innerHTML = `
                    <td style="padding: 8px 12px;">
                        <select onchange="updateComboItemProduct(${idx}, this.value)" style="height: 32px; width: 100%; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; padding: 0 8px;">
                            <option value="">-- Chọn sản phẩm --</option>
                            ${catalogProducts.map(p => `<option value="${p.id}" ${p.id == item.shop_product_id ? 'selected' : ''}>${p.name} (SKU: ${p.sku || 'N/A'}) - ${parseInt(p.price).toLocaleString()} VND</option>`).join('')}
                        </select>
                    </td>
                    <td style="padding: 8px 12px;">
                        <input type="number" min="1" value="${item.quantity || 1}" onchange="updateComboItemQty(${idx}, this.value)" style="height: 32px; width: 80px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; padding: 0 6px;">
                    </td>
                    <td style="padding: 8px 12px;">${prod ? (parseInt(prod.price) * (item.quantity || 1)).toLocaleString() + ' VND' : '0 VND'}</td>
                    <td style="padding: 8px 12px;">${prod ? prod.qty || prod.total_stock || 0 : 0}</td>
                    <td style="padding: 8px 12px; text-align: center;">
                        <button type="button" onclick="removeComboItemRow(${idx})" style="color: #ef4444; background: none; border: none; cursor: pointer;">&times;</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            recalculateComboSummary();
            syncComboItemsJson();
        }

        function addComboItemRow() {
            comboItemsData.push({ shop_product_id: catalogProducts[0]?.id || null, quantity: 1 });
            renderComboItems();
        }

        function removeComboItemRow(idx) {
            comboItemsData.splice(idx, 1);
            renderComboItems();
        }

        function updateComboItemProduct(idx, val) {
            comboItemsData[idx].shop_product_id = parseInt(val) || null;
            renderComboItems();
        }

        function updateComboItemQty(idx, val) {
            comboItemsData[idx].quantity = Math.max(1, parseInt(val) || 1);
            renderComboItems();
        }

        function recalculateComboSummary() {
            let totalNominal = 0;
            let minStock = Infinity;

            comboItemsData.forEach(item => {
                const prod = catalogProducts.find(p => p.id == item.shop_product_id);
                if (prod) {
                    const q = Math.max(1, item.quantity || 1);
                    totalNominal += (parseInt(prod.price) || 0) * q;
                    const stock = prod.qty || prod.total_stock || 0;
                    const possible = Math.floor(stock / q);
                    if (possible < minStock) minStock = possible;
                }
            });

            const nominalEl = document.getElementById('combo_nominal_total');
            const stockEl = document.getElementById('combo_available_stock_calc');
            if (nominalEl) nominalEl.textContent = totalNominal.toLocaleString() + ' VND';
            if (stockEl) stockEl.textContent = minStock === Infinity ? '0' : minStock;
        }

        function syncComboItemsJson() {
            document.getElementById('input_combo_items_json').value = JSON.stringify(comboItemsData);
        }

        // Initialize state on page load
        document.addEventListener('DOMContentLoaded', () => {
            switchProductType(currentProductType);
            renderAttributes();
            renderVariantsTable();
            renderComboItems();
        });
    </script>
@endsection
