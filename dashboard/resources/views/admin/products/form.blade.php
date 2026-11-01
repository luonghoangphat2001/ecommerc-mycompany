@extends('admin.layouts.app', ['title' => $title])

@section('content')
@php
    $productType = old('type', $record->type?->value ?? ($formData['type'] ?? 'simple'));
    if (!in_array($productType, ['simple', 'variable'])) {
        $productType = 'simple';
    }

    // Resolve featured image URL (matching Blog edit form)
    $rawImgPath = old('image', $formData['image'] ?? null);
    $resolvedImgUrl = null;
    if ($rawImgPath) {
        $resolvedImgUrl = \Illuminate\Support\Str::startsWith($rawImgPath, ['http://', 'https://', '/'])
            ? $rawImgPath
            : \Illuminate\Support\Facades\Storage::url($rawImgPath);
    }

    $selectedCats = old('categories', $formData['categories'] ?? []);
    $categoryTree = $formData['categoryTree'] ?? \App\Models\ProductCategory::whereNull('parent_id')->with('children.children')->orderBy('name')->get();

    $selectedBrands = old('brands', $formData['brands'] ?? []);
    $brandTree = $formData['brandTree'] ?? \App\Models\Brand::whereNull('parent_id')->with('children.children')->orderBy('name')->get();

    $taxClasses = \App\Models\TaxClass::with('rates')->get();
    $allTags = \Spatie\Tags\Tag::getWithType('Product');
    $selectedTags = old('tags', $formData['tags'] ?? []);
@endphp

    <style>
        /* ==========================================================================
           IMPECCABLE CRAFT FLOOR: ATTRIBUTES & VARIANTS ARCHITECTURE (Operate Mode)
           ========================================================================== */

        /* Top Type Selector Banner */
        .product-type-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 24px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }

        .product-type-select {
            height: 42px;
            min-width: 260px;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 0 14px;
            font-size: 13.5px;
            font-weight: 600;
            color: #0f172a;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .product-type-select:focus {
            border-color: var(--fi-primary, #2563eb);
            background: #ffffff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        /* Attribute Selector Toolbar */
        .attr-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .attr-toolbar-left {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 300px;
        }

        /* Individual Attribute Card (Shopify Polaris style) */
        .attr-item-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .attr-item-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        .attr-item-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .attr-badge-global {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .attr-badge-new {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        /* Modern Interactive Tag Pill Input Box */
        .attr-tag-box {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            min-height: 42px;
            padding: 5px 8px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            cursor: text;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .attr-tag-box:focus-within {
            border-color: var(--fi-primary, #2563eb);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .attr-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 8px 3px 10px;
            border-radius: 4px;
            background: #f1f5f9;
            color: #0f172a;
            border: 1px solid #e2e8f0;
            font-size: 12.5px;
            font-weight: 600;
            user-select: none;
            transition: background 0.12s ease;
        }

        .attr-pill:hover {
            background: #e2e8f0;
        }

        .attr-pill-remove {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            border-radius: 3px;
            border: none;
            background: transparent;
            color: #64748b;
            font-size: 13px;
            line-height: 1;
            cursor: pointer;
            padding: 0;
            transition: all 0.12s ease;
        }

        .attr-pill-remove:hover {
            background: #fee2e2;
            color: #ef4444;
        }

        .attr-tag-input {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            background: transparent !important;
            padding: 0 6px !important;
            height: 28px !important;
            font-size: 12.5px !important;
            color: #0f172a !important;
            min-width: 150px;
            flex: 1;
        }

        .attr-tag-input::placeholder {
            color: #94a3b8;
            font-size: 12px;
        }

        /* Suggestion Pills Bar */
        .attr-suggestions-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 8px;
            flex-wrap: wrap;
            font-size: 12px;
        }

        .attr-suggestions-list {
            display: flex;
            align-items: center;
            gap: 5px;
            flex-wrap: wrap;
        }

        .attr-suggestion-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px dashed #cbd5e1;
            background: #ffffff;
            color: #475569;
            font-size: 11.5px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.12s ease;
        }

        .attr-suggestion-chip:hover {
            border-color: var(--fi-primary, #2563eb);
            color: var(--fi-primary, #2563eb);
            background: #eff6ff;
            border-style: solid;
        }

        /* Variants Bulk Action Toolbar */
        .variants-bulk-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            flex-wrap: wrap;
        }

        .bulk-inputs-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .bulk-field-group {
            display: inline-flex;
            align-items: center;
        }

        .bulk-field-group input {
            height: 32px;
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            font-size: 12px;
            width: 105px;
        }

        .bulk-field-group button {
            height: 32px;
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            border-left: 0 !important;
            font-size: 11.5px;
            padding: 0 10px;
            white-space: nowrap;
        }

        /* Variants Data Table */
        .variants-matrix-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 12.5px;
            text-align: left;
        }

        .variants-matrix-table th {
            background: #f8fafc;
            padding: 10px 12px;
            font-weight: 600;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .variants-matrix-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .variants-matrix-table tr:hover td {
            background-color: #fafbfc;
        }

        .variant-spec-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 6px;
            border-radius: 4px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            font-size: 11.5px;
            margin-right: 4px;
        }

        .variant-spec-k {
            color: #64748b;
            font-weight: 500;
        }

        .variant-spec-v {
            color: #0f172a;
            font-weight: 700;
        }

        .table-cell-input {
            height: 32px !important;
            padding: 0 8px !important;
            font-size: 12px !important;
            border-radius: 5px !important;
            border: 1px solid #cbd5e1 !important;
            font-variant-numeric: tabular-nums;
        }

        .table-cell-input:focus {
            border-color: var(--fi-primary, #2563eb) !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
            outline: none;
        }

        .btn-trash-icon {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            border: 1px solid transparent;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.12s ease;
        }

        .btn-trash-icon:hover {
            background: #fee2e2;
            color: #ef4444;
            border-color: #fecaca;
        }

        /* Clean Functional Switch Toggle for Active Status */
        .switch-control {
            position: relative;
            display: inline-block;
            width: 32px;
            height: 18px;
            vertical-align: middle;
        }

        .switch-control input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .switch-slider-round {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background-color: #cbd5e1;
            border-radius: 18px;
            transition: .15s ease;
        }

        .switch-slider-round:before {
            position: absolute;
            content: "";
            height: 14px;
            width: 14px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            border-radius: 50%;
            transition: .15s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.15);
        }

        .switch-control input:checked + .switch-slider-round {
            background-color: #059669;
        }

        .switch-control input:checked + .switch-slider-round:before {
            transform: translateX(14px);
        }
    </style>

    <x-admin.page-header
        :title="$title"
        :description="$record ? 'Chỉnh sửa và cập nhật sản phẩm #' . $record->id : 'Tạo mới sản phẩm trong hệ thống (Simple, Variable)'"
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

        <!-- 1. Product Type Selector Banner (Dropdown) -->
        <div class="product-type-card">
            <div style="flex: 1; min-width: 260px;">
                <label for="field_type" style="display: block; font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 3px;">
                    Loại sản phẩm (Product Type) <span style="color: #ef4444;">*</span>
                </label>
                <p style="margin: 0; font-size: 12.5px; color: #64748b;">
                    Chọn mô hình sản phẩm Đơn giản hoặc Có biến thể.
                    <span style="display: inline-block; margin-left: 6px; color: #6366f1;">
                        (Sản phẩm Combo được quản lý riêng tại <a href="{{ route('admin.combo-products.index') }}" target="_blank" style="color: #2563eb; text-decoration: underline; font-weight: 600;">Quản lý Combo Sản phẩm &rarr;</a>)
                    </span>
                </p>
            </div>
            <div style="min-width: 250px;">
                <select name="type" id="field_type" class="product-type-select" onchange="switchProductType(this.value)">
                    <option value="simple" {{ $productType === 'simple' ? 'selected' : '' }}>Sản phẩm đơn giản (Simple Product)</option>
                    <option value="variable" {{ $productType === 'variable' ? 'selected' : '' }}>Sản phẩm có biến thể (Variable Product)</option>
                </select>
            </div>
        </div>

        <!-- 2. Dual Column Layout (Enterprise Craft Standard) -->
        <div class="edit-layout">
            <!-- ================= MAIN CONTENT COLUMN ================= -->
            <div class="show-main" style="display: flex; flex-direction: column; gap: 20px;">
                <!-- Card 1: Thông tin chung -->
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--fi-primary);"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            <h3 class="card-title">Thông tin chung sản phẩm</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-row" style="grid-column: 1 / -1;">
                                <label for="field_name" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px;">
                                    Tên sản phẩm <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="text" name="name" id="field_name" value="{{ old('name', is_array($record->name ?? null) ? ($record->name['vi'] ?? '') : ($record->name ?? '')) }}" class="form-control" required style="font-size: 14px; font-weight: 600;">
                            </div>
                            <div class="form-row">
                                <label for="field_sku" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px;">
                                    Mã SKU chính <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="text" name="sku" id="field_sku" value="{{ old('sku', $record->sku ?? '') }}" class="form-control mono" required placeholder="VD: TSHIRT-001">
                            </div>
                            <div class="form-row">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                    <label for="field_slug" style="margin: 0; font-size: 13px; font-weight: 600; color: #334155;">
                                        Đường dẫn (Slug) <span style="color: #ef4444;">*</span>
                                    </label>
                                    <button type="button" onclick="generateSlugFromName()" class="link-action" style="font-size: 11.5px; color: var(--fi-primary); cursor: pointer; border: none; background: none; padding: 0;">
                                        &olarr; Tạo tự động từ tên
                                    </button>
                                </div>
                                <input type="text" name="slug" id="field_slug" value="{{ old('slug', $record->slug ?? '') }}" class="form-control mono" required placeholder="duong-dan-san-pham">
                            </div>
                            <div class="form-row" style="grid-column: 1 / -1;">
                                <label for="field_description" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                    Mô tả chi tiết sản phẩm (Rich Text Editor)
                                </label>
                                <textarea name="description" id="field_description" class="ckeditor-input" rows="8">{{ old('description', is_array($record->description ?? null) ? ($record->description['vi'] ?? '') : ($record->description ?? '')) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Section Thuộc tính sản phẩm (Variable Products Only) -->
                <div id="section-attributes" class="card" style="display: none;">
                    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #8b5cf6;"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                            <h3 class="card-title">Thuộc tính sản phẩm (Attributes)</h3>
                        </div>
                        <a href="{{ route('admin.attributes.index') }}" target="_blank" style="font-size: 12px; color: var(--fi-primary, #2563eb); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; font-weight: 600;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            <span>Quản lý thư viện thuộc tính &rarr;</span>
                        </a>
                    </div>
                    <div class="card-body">
                        <p style="margin: 0 0 16px 0; font-size: 12.5px; color: #64748b;">
                            Cấu hình các thuộc tính định danh (như Kích cỡ, Màu sắc, Chất liệu) để tạo ra các biến thể sản phẩm riêng biệt.
                        </p>

                        <!-- Attribute Selector Toolbar -->
                        <div class="attr-toolbar">
                            <div class="attr-toolbar-left">
                                <label style="font-size: 12.5px; font-weight: 600; color: #475569; white-space: nowrap;">Thêm thuộc tính mẫu:</label>
                                <select id="select_global_attribute" class="form-control" style="flex: 1; height: 36px; font-size: 12.5px;">
                                    <option value="">-- Chọn thuộc tính đã cấu hình --</option>
                                    @foreach ($formData['availableAttributes'] ?? [] as $gAttr)
                                        <option value="{{ $gAttr['id'] }}">{{ $gAttr['name'] }} ({{ count($gAttr['values'] ?? []) }} giá trị mẫu)</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-primary btn-sm" onclick="addExistingAttribute()" style="white-space: nowrap; height: 36px;">
                                    Thêm vào SP
                                </button>
                            </div>
                            <div style="border-left: 1px solid #cbd5e1; height: 24px; display: inline-block;"></div>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openInlineAttributeModal()" style="white-space: nowrap; height: 36px; font-weight: 600;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align: middle; margin-right: 4px;"><path d="M12 5v14M5 12h14"/></svg>
                                Tạo thuộc tính mới
                            </button>
                        </div>

                        <!-- Attributes List Container -->
                        <div id="attributes-container" style="display: flex; flex-direction: column; gap: 14px;">
                            <!-- Populated by JS -->
                        </div>

                        <div style="margin-top: 18px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 14px; flex-wrap: wrap; gap: 10px;">
                            <div style="font-size: 12px; color: #64748b;">
                                Gõ giá trị vào ô và nhấn <kbd style="background: #f1f5f9; border: 1px solid #cbd5e1; padding: 1px 5px; border-radius: 4px; font-size: 11px;">Enter</kbd> hoặc click các chip mẫu để thêm nhanh.
                            </div>
                            <button type="button" class="btn btn-primary" onclick="generateVariantsFromAttributes()" style="font-size: 13px; padding: 8px 18px; font-weight: 600;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align: middle; margin-right: 4px;"><path d="M12 2v20M2 12h20"/></svg>
                                Tạo tổ hợp biến thể (Generate Variants)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal: Create New Global Attribute Inline -->
                <div id="modal-inline-attribute" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
                    <div style="background: #ffffff; border-radius: 12px; width: 480px; max-width: 92vw; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); border: 1px solid #e2e8f0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <h4 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">Tạo thuộc tính dùng chung mới</h4>
                            <button type="button" onclick="closeInlineAttributeModal()" style="border: none; background: none; font-size: 22px; color: #94a3b8; cursor: pointer; line-height: 1;">&times;</button>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 14px;">
                            <div>
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px;">Tên thuộc tính <span style="color: #ef4444;">*</span></label>
                                <input type="text" id="inline_attr_name" class="form-control" placeholder="VD: Dung tích, Kích cỡ, Form dáng">
                            </div>
                            <div>
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px;">Loại hiển thị</label>
                                <select id="inline_attr_type" class="form-control">
                                    <option value="button">Nút chọn (Button / Pill)</option>
                                    <option value="select">Danh sách thả xuống (Dropdown Select)</option>
                                    <option value="color">Màu sắc (Color Swatch)</option>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px;">Các giá trị mẫu (cách nhau bởi dấu phẩy)</label>
                                <textarea id="inline_attr_values" rows="3" class="form-control" placeholder="VD: S, M, L, XL hoặc 250ml, 500ml"></textarea>
                                <span style="font-size: 11.5px; color: #64748b; margin-top: 4px; display: block;">Thuộc tính này sẽ được lưu vào cơ sở dữ liệu và có thể tái sử dụng cho mọi sản phẩm sau.</span>
                            </div>
                        </div>
                        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                            <button type="button" class="btn btn-secondary" onclick="closeInlineAttributeModal()" style="font-size: 13px; padding: 7px 16px;">Hủy</button>
                            <button type="button" class="btn btn-primary" id="btn_save_inline_attr" onclick="saveInlineAttribute()" style="font-size: 13px; padding: 7px 16px;">Lưu & Chọn vào SP</button>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Section Danh sách biến thể (Variants) -->
                <div id="section-variants" class="card" style="display: none;">
                    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #059669;"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                            <h3 class="card-title">Danh sách biến thể (Variants)</h3>
                            <span id="variant-count-badge" class="badge badge-secondary" style="font-size: 11.5px; font-weight: 600;">0 biến thể</span>
                        </div>
                        <div style="font-size: 12px; color: #64748b;">
                            Cấu hình độc lập SKU, giá bán, lịch khuyến mãi và số lượng tồn kho cho từng biến thể.
                        </div>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div class="variants-bulk-bar">
                        <span style="font-size: 12px; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 4px;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path></svg>
                            Thao tác hàng loạt:
                        </span>
                        <div class="bulk-inputs-wrap">
                            <div class="bulk-field-group">
                                <input type="number" id="bulk_variant_price" placeholder="Giá bán" class="form-control">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="applyBulkVariantPrice()">Áp giá</button>
                            </div>
                            <div class="bulk-field-group">
                                <input type="number" id="bulk_variant_sale_price" placeholder="Giá KM" class="form-control">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="applyBulkVariantSalePrice()">Áp KM</button>
                            </div>
                            <div class="bulk-field-group">
                                <input type="number" id="bulk_variant_stock" placeholder="Kho" class="form-control">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="applyBulkVariantStock()">Áp kho</button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body" style="padding: 0;">
                        <div class="table-wrap" style="border: none; border-radius: 0; overflow-x: auto;">
                            <table class="variants-matrix-table">
                                <thead>
                                    <tr>
                                        <th style="min-width: 140px;">Tổ hợp thuộc tính</th>
                                        <th style="width: 130px;">Mã SKU</th>
                                        <th style="width: 110px;">Giá bán (VND)</th>
                                        <th style="width: 110px;">Giá KM (VND)</th>
                                        <th style="min-width: 155px;">Bắt đầu KM</th>
                                        <th style="min-width: 155px;">Kết thúc KM</th>
                                        <th style="width: 75px;">Kho</th>
                                        <th style="width: 60px; text-align: center;">Bật</th>
                                        <th style="width: 44px; text-align: center;">Xóa</th>
                                    </tr>
                                </thead>
                                <tbody id="variants-table-body">
                                    <!-- Populated by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Giá bán & Khuyến mãi (Pricing) -->
                <div id="section-pricing" class="card">
                    <div class="card-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #10b981;"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                            <h3 class="card-title">Giá bán & Khuyến mãi tiêu chuẩn</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-row">
                                <label for="field_price" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px;">
                                    Giá bán tiêu chuẩn (VND) <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="number" name="price" id="field_price" value="{{ old('price', $record->price ?? 0) }}" class="form-control mono" style="font-size: 13.5px;">
                                <span id="price_hint" style="font-size: 11.5px; color: #64748b; margin-top: 4px; display: block;">Đối với sản phẩm có biến thể, giá này là giá cơ sở tối thiểu.</span>
                            </div>
                            <div class="form-row">
                                <label for="field_sale_price" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px;">
                                    Giá khuyến mãi (Sale Price)
                                </label>
                                <input type="number" name="sale_price" id="field_sale_price" value="{{ old('sale_price', $record->sale_price ?? '') }}" class="form-control mono" placeholder="Để trống nếu không giảm giá">
                            </div>
                            <div class="form-row">
                                <label for="field_sale_start_date" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px;">
                                    Ngày bắt đầu khuyến mãi
                                </label>
                                <input type="datetime-local" id="field_sale_start_date" name="sale_start_date" value="{{ old('sale_start_date', $record->sale_start_date ? $record->sale_start_date->format('Y-m-d\TH:i') : '') }}" class="form-control mono">
                            </div>
                            <div class="form-row">
                                <label for="field_sale_end_date" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px;">
                                    Ngày kết thúc khuyến mãi
                                </label>
                                <input type="datetime-local" id="field_sale_end_date" name="sale_end_date" value="{{ old('sale_end_date', $record->sale_end_date ? $record->sale_end_date->format('Y-m-d\TH:i') : '') }}" class="form-control mono">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 5: Quản lý kho hàng & Thuế -->
                <div id="section-inventory" class="card">
                    <div class="card-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #64748b;"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                            <h3 class="card-title">Kho hàng & Cấu hình thuế</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-row" id="wrapper_qty">
                                <label for="field_qty" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px;">
                                    Số lượng tồn kho (Quantity)
                                </label>
                                <input type="number" name="qty" id="field_qty" value="{{ old('qty', $record->qty ?? 0) }}" class="form-control mono">
                                <span id="qty_hint" style="font-size: 11.5px; color: #64748b; margin-top: 4px; display: block;">Đối với sản phẩm có biến thể, tồn kho tính tự động.</span>
                            </div>
                            <div class="form-row">
                                <label for="field_security_stock" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px;">
                                    Ngưỡng tồn an toàn (Security Stock)
                                </label>
                                <input type="number" id="field_security_stock" name="security_stock" value="{{ old('security_stock', $record->security_stock ?? 0) }}" class="form-control mono">
                            </div>
                            <div class="form-row">
                                <label for="field_apply_tax" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px;">
                                    Áp dụng thuế (Apply Tax)
                                </label>
                                <select name="apply_tax" id="field_apply_tax" class="form-control">
                                    <option value="1" {{ old('apply_tax', $record->apply_tax ?? 1) == 1 ? 'selected' : '' }}>Có (Áp dụng thuế)</option>
                                    <option value="0" {{ old('apply_tax', $record->apply_tax ?? 1) == 0 ? 'selected' : '' }}>Không (Miễn thuế)</option>
                                </select>
                            </div>
                            <div class="form-row">
                                <label for="field_tax_class_id" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px;">
                                    Nhóm thuế (Tax Class)
                                </label>
                                <select name="tax_class_id" id="field_tax_class_id" class="form-control">
                                    <option value="">-- Mặc định hệ thống / Miễn thuế --</option>
                                    @foreach ($taxClasses as $tc)
                                        <option value="{{ $tc->id }}" {{ old('tax_class_id', $record->tax_class_id ?? '') == $tc->id ? 'selected' : '' }}>
                                            {{ $tc->name }} ({{ $tc->rates->first()?->rate ?? 0 }}%)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 6: Tối ưu hóa SEO (Google SEO Live Preview) -->
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #2563eb;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            <h3 class="card-title">Tối ưu hóa công cụ tìm kiếm (Google SEO)</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Live Google Search Snippet Preview -->
                        <div class="seo-snippet-preview">
                            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                                <div style="width: 14px; height: 14px; border-radius: 50%; background: #2563eb; display: grid; place-items: center; color: #fff; font-size: 8px; font-weight: 800;">G</div>
                                <div class="seo-snippet-url">
                                    https://mycompany.com/products/<span id="seo-preview-slug">{{ old('slug', $record->slug ?? 'duong-dan-san-pham') }}</span>
                                </div>
                            </div>
                            <div id="seo-preview-title" class="seo-snippet-title">
                                {{ old('seo_title', $record->seo_title ?? ($record->name ?? 'Tiêu đề sản phẩm sẽ hiển thị ở đây trên Google')) }}
                            </div>
                            <div id="seo-preview-desc" class="seo-snippet-desc">
                                {{ old('seo_description', $record->seo_description ?? 'Mô tả ngắn gọn về sản phẩm để thu hút khách hàng nhấp vào kết quả tìm kiếm trên Google...') }}
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 14px; margin-top: 14px;">
                            <div class="form-row">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                    <label for="seo_title" style="margin: 0; font-size: 12.5px; font-weight: 600;">Tiêu đề SEO (SEO Title)</label>
                                    <small style="font-size: 11px; color: var(--fi-muted);"><span id="seo-title-count">0</span> / 60 ký tự</small>
                                </div>
                                <input type="text" id="seo_title" name="seo_title" value="{{ old('seo_title', $record->seo_title ?? '') }}" class="form-control js-seo-input" placeholder="Tiêu đề trang xuất hiện trên Google">
                            </div>
                            <div class="form-row">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                    <label for="seo_description" style="margin: 0; font-size: 12.5px; font-weight: 600;">Mô tả SEO (SEO Description)</label>
                                    <small style="font-size: 11px; color: var(--fi-muted);"><span id="seo-desc-count">0</span> / 160 ký tự</small>
                                </div>
                                <textarea id="seo_description" name="seo_description" rows="3" class="form-control js-seo-input" placeholder="Tóm tắt nội dung hấp dẫn cho công cụ tìm kiếm">{{ old('seo_description', $record->seo_description ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= SIDEBAR COLUMN (Matching Blog Layout) ================= -->
            <div class="show-sidebar" style="display: flex; flex-direction: column; gap: 20px;">
                <!-- Card A: Thiết lập & Xuất bản -->
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--fi-gold, #f59e0b);"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <h3 class="card-title">Thiết lập & Xuất bản</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-direction: column; gap: 14px;">
                            <div class="form-row">
                                <label for="field_is_visible" style="margin-bottom: 5px; font-weight: 600; font-size: 12.5px;">Công khai trên website</label>
                                <select name="is_visible" id="field_is_visible" class="form-control">
                                    <option value="1" {{ old('is_visible', $record->is_visible ?? 1) == 1 ? 'selected' : '' }}>Hiển thị (Active)</option>
                                    <option value="0" {{ old('is_visible', $record->is_visible ?? 1) == 0 ? 'selected' : '' }}>Tạm ẩn (Draft)</option>
                                </select>
                            </div>
                            <div class="form-row">
                                <label for="field_featured" style="margin-bottom: 5px; font-weight: 600; font-size: 12.5px;">Sản phẩm nổi bật (Featured)</label>
                                <select name="featured" id="field_featured" class="form-control">
                                    <option value="0" {{ old('featured', $record->featured ?? 0) == 0 ? 'selected' : '' }}>Bình thường</option>
                                    <option value="1" {{ old('featured', $record->featured ?? 0) == 1 ? 'selected' : '' }}>Nổi bật (Trang chủ)</option>
                                </select>
                            </div>
                            <div class="form-row">
                                <label for="field_requires_shipping" style="margin-bottom: 5px; font-weight: 600; font-size: 12.5px;">Yêu cầu giao hàng</label>
                                <select name="requires_shipping" id="field_requires_shipping" class="form-control">
                                    <option value="1" {{ old('requires_shipping', $record->requires_shipping ?? 1) == 1 ? 'selected' : '' }}>Có vận chuyển</option>
                                    <option value="0" {{ old('requires_shipping', $record->requires_shipping ?? 1) == 0 ? 'selected' : '' }}>Không (Kỹ thuật số)</option>
                                </select>
                            </div>
                            <div class="form-row">
                                <label for="field_backorder" style="margin-bottom: 5px; font-weight: 600; font-size: 12.5px;">Đặt hàng trước (Backorder)</label>
                                <select name="backorder" id="field_backorder" class="form-control">
                                    <option value="0" {{ old('backorder', $record->backorder ?? 0) == 0 ? 'selected' : '' }}>Không cho phép</option>
                                    <option value="1" {{ old('backorder', $record->backorder ?? 0) == 1 ? 'selected' : '' }}>Cho phép đặt khi hết hàng</option>
                                </select>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px; padding-top: 14px; border-top: 1px solid var(--fi-border, #e2e8f0);">
                                <button class="btn btn-primary" type="submit" form="product-main-form" style="width: 100%; justify-content: center;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                    {{ __('admin.actions.save') }}
                                </button>
                                <a class="btn btn-secondary" href="{{ route($routePrefix . '.index') }}" style="width: 100%; justify-content: center;">
                                    {{ __('admin.actions.cancel') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Loyalty: Điểm thưởng tích lũy (Impeccable Craft) -->
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--fi-gold, #f59e0b);"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <h3 class="card-title">Điểm thưởng Loyalty</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-direction: column; gap: 14px;">
                            <div class="form-row">
                                <label for="field_is_loyalty_earnable" style="margin-bottom: 5px; font-weight: 600; font-size: 12.5px;">Tích điểm khi mua</label>
                                <select name="is_loyalty_earnable" id="field_is_loyalty_earnable" class="form-control">
                                    <option value="1" {{ old('is_loyalty_earnable', $record->is_loyalty_earnable ?? 1) == 1 ? 'selected' : '' }}>Có (Tích điểm tự động)</option>
                                    <option value="0" {{ old('is_loyalty_earnable', $record->is_loyalty_earnable ?? 1) == 0 ? 'selected' : '' }}>Không tích điểm</option>
                                </select>
                            </div>
                            <div class="form-row">
                                <label for="field_is_loyalty_redeemable" style="margin-bottom: 5px; font-weight: 600; font-size: 12.5px;">Cho phép dùng điểm thanh toán</label>
                                <select name="is_loyalty_redeemable" id="field_is_loyalty_redeemable" class="form-control">
                                    <option value="1" {{ old('is_loyalty_redeemable', $record->is_loyalty_redeemable ?? 1) == 1 ? 'selected' : '' }}>Có (Được áp dụng đổi điểm)</option>
                                    <option value="0" {{ old('is_loyalty_redeemable', $record->is_loyalty_redeemable ?? 1) == 0 ? 'selected' : '' }}>Không áp dụng đổi điểm</option>
                                </select>
                            </div>
                            <div class="form-row">
                                <label for="field_loyalty_points_reward" style="margin-bottom: 5px; font-weight: 600; font-size: 12.5px;">Điểm thưởng cố định tặng kèm</label>
                                <div style="position: relative; display: flex; align-items: center;">
                                    <input type="number" name="loyalty_points_reward" id="field_loyalty_points_reward" value="{{ old('loyalty_points_reward', $record->loyalty_points_reward ?? '') }}" class="form-control" placeholder="Tự động tính theo giá" min="0" style="padding-right: 50px;">
                                    <span style="position: absolute; right: 10px; font-size: 11.5px; font-weight: 600; color: #64748b; pointer-events: none;">điểm</span>
                                </div>
                                <span class="field-hint" style="font-size: 11px; color: #64748b; margin-top: 4px; display: block;">Nếu nhập số cụ thể (VD: 50), khách mua sẽ nhận thẳng số điểm này thay vì tính theo giá tiền.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card B: Hình ảnh đại diện sản phẩm (Featured Image - Matching Blog) -->
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #6366f1;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                            <h3 class="card-title">Hình ảnh đại diện</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="featured-media-card">
                            <div class="featured-media-frame">
                                <img id="preview-image" src="{{ $resolvedImgUrl ?: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' }}" alt="Preview" style="{{ $resolvedImgUrl ? '' : 'display: none;' }}">
                                <div id="placeholder-image" class="featured-media-placeholder" style="{{ $resolvedImgUrl ? 'display: none;' : '' }}">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="color: #94a3b8;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                    <span style="font-size: 11.5px; color: #64748b;">Chưa có ảnh đại diện</span>
                                </div>
                            </div>
                            <div class="custom-file-picker" style="width: 100%; justify-content: center;">
                                <input type="file" id="image" name="image" accept="image/*" onchange="previewFeaturedImage(this, 'preview-image', 'placeholder-image')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                <span>Chọn / Tải lên hình ảnh</span>
                            </div>
                            @if($rawImgPath)
                                <div style="margin-top: 8px; font-size: 11px; color: var(--fi-muted, #64748b); font-family: monospace; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ basename($rawImgPath) }}
                                </div>
                            @endif
                        </div>
                        @error('image')
                            <div class="error" style="color: #dc2626; font-size: 12px; margin-top: 6px;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Card C: Thương hiệu (Brands Tree Checklist - Matching Categories layout) -->
                <div class="card">
                    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #f43f5e;"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                            <h3 class="card-title">Thương hiệu (Brands)</h3>
                        </div>
                        @if(\Illuminate\Support\Facades\Route::has('admin.brands.index'))
                            <a href="{{ route('admin.brands.index') }}" target="_blank" style="font-size: 11.5px; color: var(--fi-primary); text-decoration: none; font-weight: 600;">+ Quản lý</a>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="tree-select-wrapper" style="max-height: 280px; overflow-y: auto; padding-right: 4px;">
                            @include('admin.crud.tree_node', ['nodes' => $brandTree, 'level' => 0, 'name' => 'brands', 'selected' => $selectedBrands])
                        </div>
                        @error('brands')
                            <div class="error" style="color: #dc2626; font-size: 12px; margin-top: 6px;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Card D: Danh mục sản phẩm (Categories Tree Checklist - Matching Blog) -->
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #0891b2;"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                            <h3 class="card-title">Danh mục sản phẩm (Categories)</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="tree-select-wrapper" style="max-height: 280px; overflow-y: auto; padding-right: 4px;">
                            @include('admin.crud.tree_node', ['nodes' => $categoryTree, 'level' => 0, 'name' => 'categories', 'selected' => $selectedCats])
                        </div>
                        @error('categories')
                            <div class="error" style="color: #dc2626; font-size: 12px; margin-top: 6px;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Card E: Thẻ / Nhãn (Tags) -->
                @if(isset($allTags) && count($allTags) > 0)
                    <div class="card">
                        <div class="card-header">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #8b5cf6;"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                                <h3 class="card-title">Thẻ / Tags</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="tag-pills-grid" style="display: flex; flex-wrap: wrap; gap: 6px;">
                                @foreach ($allTags as $t)
                                    <label class="tag-pill-label" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 9999px; background: #f1f5f9; border: 1px solid #cbd5e1; font-size: 12px; cursor: pointer;">
                                        <input type="checkbox" name="tags[]" value="{{ $t->name }}" {{ in_array($t->name, (array) $selectedTags) ? 'checked' : '' }}>
                                        <span>{{ $t->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </form>

    <!-- CKEditor 5 CDN Script -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

    <script>
        let availableAttributes = @json($formData['availableAttributes'] ?? []);
        let currentProductType = "{{ $productType }}";
        let attributesData = {!! old('attributes_json', $formData['attributes_json'] ?? '[]') !!};
        let variantsData = {!! old('variants_json', $formData['variants_json'] ?? '[]') !!};
        let productEditorInstance = null;

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // ==================== PRODUCT TYPE SWITCHER ====================
        function switchProductType(type) {
            currentProductType = type;
            const secAttr = document.getElementById('section-attributes');
            const secVar = document.getElementById('section-variants');
            const priceHint = document.getElementById('price_hint');

            if (type === 'variable') {
                secAttr.style.display = 'block';
                secVar.style.display = 'block';
                if (priceHint) priceHint.textContent = 'Giá tối thiểu hoặc cơ sở của biến thể.';
            } else {
                secAttr.style.display = 'none';
                secVar.style.display = 'none';
                if (priceHint) priceHint.textContent = 'Giá bán sản phẩm đơn lẻ.';
            }
        }

        // ==================== SLUG GENERATOR ====================
        function generateSlugFromName() {
            const nameInput = document.getElementById('field_name');
            const slugInput = document.getElementById('field_slug');
            if (!nameInput || !slugInput) return;

            let str = nameInput.value.toLowerCase().trim();
            str = str.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            str = str.replace(/đ/g, 'd').replace(/Đ/g, 'd');
            str = str.replace(/[^a-z0-9 -]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
            slugInput.value = str;

            const seoSlug = document.getElementById('seo-preview-slug');
            if (seoSlug) seoSlug.innerText = str || 'duong-dan-san-pham';
        }

        // ==================== FEATURED IMAGE PREVIEW ====================
        function previewFeaturedImage(input, previewId, placeholderId) {
            const preview = document.getElementById(previewId);
            const placeholder = document.getElementById(placeholderId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // ==================== INLINE ATTRIBUTE MODAL ====================
        function openInlineAttributeModal() {
            document.getElementById('inline_attr_name').value = '';
            document.getElementById('inline_attr_values').value = '';
            document.getElementById('inline_attr_type').value = 'button';
            document.getElementById('modal-inline-attribute').style.display = 'flex';
        }

        function closeInlineAttributeModal() {
            document.getElementById('modal-inline-attribute').style.display = 'none';
        }

        function saveInlineAttribute() {
            const name = document.getElementById('inline_attr_name').value.trim();
            const type = document.getElementById('inline_attr_type').value;
            const valuesStr = document.getElementById('inline_attr_values').value.trim();

            if (!name) {
                alert('Vui lòng nhập tên thuộc tính.');
                return;
            }

            const btn = document.getElementById('btn_save_inline_attr');
            btn.disabled = true;
            btn.textContent = 'Đang lưu...';

            fetch('{{ route("admin.attributes.inline-store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    type: type,
                    values: valuesStr
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.textContent = 'Lưu & Chọn vào SP';

                if (data.success && data.attribute) {
                    const newAttr = data.attribute;
                    const existingIdx = availableAttributes.findIndex(a => a.id === newAttr.id);
                    if (existingIdx >= 0) {
                        availableAttributes[existingIdx] = newAttr;
                    } else {
                        availableAttributes.push(newAttr);
                    }

                    refreshAttributeDropdown();

                    const inFormData = attributesData.find(a => (a.attribute_id && a.attribute_id == newAttr.id) || (a.name && a.name.toLowerCase() === newAttr.name.toLowerCase()));
                    if (!inFormData) {
                        attributesData.push({
                            attribute_id: newAttr.id,
                            name: newAttr.name,
                            type: newAttr.type,
                            is_variation: true,
                            options: [...(newAttr.values || [])]
                        });
                        renderAttributes();
                    }

                    closeInlineAttributeModal();
                } else {
                    alert(data.message || 'Lỗi khi lưu thuộc tính.');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.textContent = 'Lưu & Chọn vào SP';
                console.error(err);
                alert('Không thể kết nối đến máy chủ.');
            });
        }

        function refreshAttributeDropdown() {
            const select = document.getElementById('select_global_attribute');
            if (!select) return;
            const currentVal = select.value;
            select.innerHTML = '<option value="">-- Chọn thuộc tính đã cấu hình --</option>';
            availableAttributes.forEach(attr => {
                const opt = document.createElement('option');
                opt.value = attr.id;
                opt.textContent = `${attr.name} (${(attr.values || []).length} giá trị mẫu)`;
                select.appendChild(opt);
            });
            select.value = currentVal;
        }

        function addExistingAttribute() {
            const select = document.getElementById('select_global_attribute');
            const attrId = parseInt(select.value);
            if (!attrId) {
                alert('Vui lòng chọn một thuộc tính từ danh sách.');
                return;
            }

            const found = availableAttributes.find(a => a.id === attrId);
            if (!found) return;

            const existing = attributesData.find(a => (a.attribute_id && a.attribute_id === found.id) || (a.name && a.name.toLowerCase().trim() === found.name.toLowerCase().trim()));
            if (existing) {
                alert(`Thuộc tính "${found.name}" đã có trong danh sách.`);
                return;
            }

            attributesData.push({
                attribute_id: found.id,
                name: found.name,
                type: found.type,
                is_variation: true,
                options: [...(found.values || [])]
            });

            select.value = '';
            renderAttributes();
        }

        function removeAttributeRow(idx) {
            attributesData.splice(idx, 1);
            renderAttributes();
        }

        function toggleAttributeVariation(idx, val) {
            attributesData[idx].is_variation = val;
            syncAttributesJson();
        }

        // ==================== CRAFTED TAG PILLS LOGIC (IMPECCABLE) ====================
        function focusTagInput(idx) {
            const input = document.getElementById(`tag_input_${idx}`);
            if (input) input.focus();
        }

        function handleTagInputKey(e, idx) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                const input = e.target;
                const val = input.value.trim().replace(/^,|,$/g, '');
                if (val) {
                    addTagToAttribute(idx, val);
                    input.value = '';
                }
            } else if (e.key === 'Backspace' && e.target.value === '') {
                const attr = attributesData[idx];
                const optionsArr = Array.isArray(attr.options) ? attr.options : [];
                if (optionsArr.length > 0) {
                    removeTagFromAttribute(idx, optionsArr.length - 1);
                }
            }
        }

        function handleTagInputBlur(input, idx) {
            const val = input.value.trim().replace(/^,|,$/g, '');
            if (val) {
                addTagToAttribute(idx, val);
                input.value = '';
            }
        }

        function addTagToAttribute(idx, val) {
            val = String(val).trim();
            if (!val) return;
            const attr = attributesData[idx];
            if (!Array.isArray(attr.options)) {
                attr.options = [];
            }
            if (!attr.options.includes(val)) {
                attr.options.push(val);
                renderAttributes();
                setTimeout(() => focusTagInput(idx), 50);
            }
        }

        function removeTagFromAttribute(idx, optIdx) {
            const attr = attributesData[idx];
            if (Array.isArray(attr.options)) {
                attr.options.splice(optIdx, 1);
                renderAttributes();
            }
        }

        function selectAllAttributeValues(idx) {
            const attr = attributesData[idx];
            let matchedGlobal = null;
            if (attr.attribute_id) {
                matchedGlobal = availableAttributes.find(a => a.id == attr.attribute_id);
            }
            if (!matchedGlobal && attr.name) {
                matchedGlobal = availableAttributes.find(a => a.name.toLowerCase().trim() === attr.name.toLowerCase().trim());
            }

            if (matchedGlobal && Array.isArray(matchedGlobal.values)) {
                attr.options = [...matchedGlobal.values];
                renderAttributes();
            }
        }

        function clearAttributeValues(idx) {
            attributesData[idx].options = [];
            renderAttributes();
        }

        function renderAttributes() {
            const container = document.getElementById('attributes-container');
            container.innerHTML = '';

            if (attributesData.length === 0) {
                container.innerHTML = `
                    <div style="padding: 32px 16px; text-align: center; background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 8px;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 8px; color: #94a3b8;"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        <div style="font-weight: 600; font-size: 13.5px; color: #334155; margin-bottom: 3px;">Chưa có thuộc tính nào được cấu hình cho sản phẩm</div>
                        <div style="font-size: 12px; color: #64748b;">Chọn một thuộc tính mẫu từ thanh công cụ ở trên hoặc bấm "Tạo thuộc tính mới" để bắt đầu.</div>
                    </div>
                `;
                syncAttributesJson();
                return;
            }

            attributesData.forEach((attr, idx) => {
                let matchedGlobal = null;
                if (attr.attribute_id) {
                    matchedGlobal = availableAttributes.find(a => a.id == attr.attribute_id);
                }
                if (!matchedGlobal && attr.name) {
                    matchedGlobal = availableAttributes.find(a => a.name.toLowerCase().trim() === attr.name.toLowerCase().trim());
                }

                const optionsArr = Array.isArray(attr.options) ? attr.options : (attr.options ? String(attr.options).split(',').map(s => s.trim()).filter(Boolean) : []);
                const isVariation = attr.is_variation !== false;

                // Tag Pills HTML
                const pillsHtml = optionsArr.map((opt, optIdx) => `
                    <span class="attr-pill">
                        <span>${escapeHtml(opt)}</span>
                        <button type="button" class="attr-pill-remove" onclick="removeTagFromAttribute(${idx}, ${optIdx})" title="Gỡ bỏ giá trị này">&times;</button>
                    </span>
                `).join('');

                // Suggested chips from global attribute (excluding already selected)
                let suggestionsHtml = '';
                if (matchedGlobal && Array.isArray(matchedGlobal.values)) {
                    const remainingValues = matchedGlobal.values.filter(v => !optionsArr.includes(v));
                    if (remainingValues.length > 0) {
                        suggestionsHtml = remainingValues.map(v => `
                            <button type="button" class="attr-suggestion-chip" onclick="addTagToAttribute(${idx}, '${escapeHtml(v)}')">
                                + ${escapeHtml(v)}
                            </button>
                        `).join(' ');
                    }
                }

                const div = document.createElement('div');
                div.className = 'attr-item-card';
                div.innerHTML = `
                    <div class="attr-item-header">
                        <div class="attr-item-title">
                            <span style="font-size: 13.5px; font-weight: 700; color: #0f172a;">${escapeHtml(attr.name || 'Thuộc tính')}</span>
                            ${matchedGlobal ? `<span class="attr-badge-global">Thư viện dùng chung</span>` : `<span class="attr-badge-new">Tạo mới (sẽ lưu toàn cục)</span>`}
                        </div>
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; color: #334155; cursor: pointer; user-select: none;">
                                <input type="checkbox" ${isVariation ? 'checked' : ''} onchange="toggleAttributeVariation(${idx}, this.checked)">
                                <span>Sử dụng tạo biến thể</span>
                            </label>
                            <button type="button" class="btn-trash-icon" onclick="removeAttributeRow(${idx})" title="Xóa thuộc tính này">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Clean Tag Pills Input Box -->
                    <div class="attr-tag-box" onclick="focusTagInput(${idx})">
                        ${pillsHtml}
                        <input type="text" id="tag_input_${idx}" class="attr-tag-input" placeholder="${optionsArr.length === 0 ? 'Nhập giá trị và nhấn Enter hoặc dấu phẩy...' : 'Thêm giá trị...'}" onkeydown="handleTagInputKey(event, ${idx})" onblur="handleTagInputBlur(this, ${idx})">
                    </div>

                    <div class="attr-suggestions-bar">
                        <div class="attr-suggestions-list">
                            ${suggestionsHtml ? `<span style="font-size: 11.5px; font-weight: 600; color: #64748b; margin-right: 2px;">Gợi ý mẫu:</span> ${suggestionsHtml}` : ''}
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            ${matchedGlobal ? `<button type="button" onclick="selectAllAttributeValues(${idx})" style="font-size: 11.5px; color: var(--fi-primary, #2563eb); background: none; border: none; cursor: pointer; text-decoration: underline; font-weight: 600;">[ Chọn tất cả mẫu ]</button>` : ''}
                            <button type="button" onclick="clearAttributeValues(${idx})" style="font-size: 11.5px; color: #ef4444; background: none; border: none; cursor: pointer; text-decoration: underline; font-weight: 500;">[ Bỏ chọn tất cả ]</button>
                        </div>
                    </div>
                `;
                container.appendChild(div);
            });

            syncAttributesJson();
        }

        function syncAttributesJson() {
            document.getElementById('input_attributes_json').value = JSON.stringify(attributesData);
        }

        // ==================== VARIANTS GENERATION & MANAGEMENT ====================
        function generateVariantsFromAttributes() {
            const variationAttributes = attributesData.filter(a => a.is_variation !== false);

            if (variationAttributes.length === 0) {
                alert('Vui lòng thêm ít nhất một thuộc tính có bật tùy chọn "Sử dụng tạo biến thể".');
                return;
            }

            const arrays = variationAttributes.map(a => {
                const opts = Array.isArray(a.options) ? a.options : String(a.options).split(',').map(s => s.trim()).filter(Boolean);
                return opts.map(opt => ({ [a.name]: opt }));
            });

            if (arrays.some(arr => arr.length === 0)) {
                alert('Vui lòng chọn hoặc nhập ít nhất một giá trị cho mỗi thuộc tính biến thể.');
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
            const baseSalePrice = parseInt(document.getElementById('field_sale_price').value) || null;
            const baseSaleStart = document.getElementById('field_sale_start_date').value || '';
            const baseSaleEnd = document.getElementById('field_sale_end_date').value || '';

            // Regeneration Safety: preserve existing variants matching combinations
            const updatedVariants = combinations.map((combo) => {
                const existing = variantsData.find(v => {
                    const vAttrs = v.attributes || {};
                    const keysA = Object.keys(combo).sort();
                    const keysB = Object.keys(vAttrs).sort();
                    if (keysA.length !== keysB.length) return false;
                    return keysA.every(k => String(combo[k]) === String(vAttrs[k]));
                });

                if (existing) {
                    return existing;
                }

                const comboStr = Object.values(combo).join('-');
                return {
                    id: null,
                    sku: `${baseSku}-${comboStr}`.toUpperCase().replace(/\s+/g, ''),
                    attributes: combo,
                    price: basePrice,
                    sale_price: baseSalePrice,
                    sale_start_date: baseSaleStart,
                    sale_end_date: baseSaleEnd,
                    qty: 10,
                    is_active: true
                };
            });

            variantsData = updatedVariants;
            renderVariantsTable();
        }

        function renderVariantsTable() {
            const tbody = document.getElementById('variants-table-body');
            const badge = document.getElementById('variant-count-badge');
            tbody.innerHTML = '';

            if (badge) {
                badge.textContent = `${variantsData.length} biến thể`;
            }

            if (variantsData.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" style="padding: 40px 16px; text-align: center;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 8px; color: #94a3b8;"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                            <div style="font-weight: 600; font-size: 13.5px; color: #334155; margin-bottom: 3px;">Chưa có biến thể nào được tạo</div>
                            <div style="font-size: 12px; color: #64748b; margin-bottom: 12px;">Cấu hình giá trị thuộc tính ở trên, sau đó bấm "Tạo tổ hợp biến thể" để sinh danh sách tự động.</div>
                            <button type="button" class="btn btn-primary btn-sm" onclick="generateVariantsFromAttributes()">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align: middle; margin-right: 4px;"><path d="M12 2v20M2 12h20"/></svg>
                                Tạo tổ hợp biến thể ngay
                            </button>
                        </td>
                    </tr>
                `;
                return;
            }

            variantsData.forEach((v, idx) => {
                const attrPills = Object.entries(v.attributes || {}).map(([k, val]) => `
                    <span class="variant-spec-badge">
                        <span class="variant-spec-k">${escapeHtml(k)}:</span>
                        <span class="variant-spec-v">${escapeHtml(val)}</span>
                    </span>
                `).join('');

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${attrPills}</td>
                    <td><input type="text" value="${escapeHtml(v.sku || '')}" onchange="updateVariantField(${idx}, 'sku', this.value)" class="form-control mono table-cell-input" style="width: 125px;"></td>
                    <td><input type="number" value="${v.price || 0}" onchange="updateVariantField(${idx}, 'price', this.value)" class="form-control mono table-cell-input" style="width: 105px; text-align: right;"></td>
                    <td><input type="number" value="${v.sale_price !== null && v.sale_price !== undefined ? v.sale_price : ''}" placeholder="—" onchange="updateVariantField(${idx}, 'sale_price', this.value)" class="form-control mono table-cell-input" style="width: 105px; text-align: right;"></td>
                    <td><input type="datetime-local" value="${v.sale_start_date || ''}" onchange="updateVariantField(${idx}, 'sale_start_date', this.value)" class="form-control mono table-cell-input" style="width: 155px; font-size: 11px !important;"></td>
                    <td><input type="datetime-local" value="${v.sale_end_date || ''}" onchange="updateVariantField(${idx}, 'sale_end_date', this.value)" class="form-control mono table-cell-input" style="width: 155px; font-size: 11px !important;"></td>
                    <td><input type="number" value="${v.qty || 0}" onchange="updateVariantField(${idx}, 'qty', this.value)" class="form-control mono table-cell-input" style="width: 70px; text-align: center;"></td>
                    <td style="text-align: center;">
                        <label class="switch-control" title="Bật/Tắt biến thể">
                            <input type="checkbox" ${v.is_active ? 'checked' : ''} onchange="updateVariantField(${idx}, 'is_active', this.checked)">
                            <span class="switch-slider-round"></span>
                        </label>
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="btn-trash-icon" onclick="removeVariantRow(${idx})" title="Xóa biến thể này">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            syncVariantsJson();
        }

        function updateVariantField(idx, field, val) {
            if (field === 'price' || field === 'qty') {
                variantsData[idx][field] = parseInt(val) || 0;
            } else if (field === 'sale_price') {
                variantsData[idx][field] = (val !== '' && !isNaN(val)) ? parseInt(val) : null;
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

        function applyBulkVariantSalePrice() {
            const val = parseInt(document.getElementById('bulk_variant_sale_price').value);
            variantsData.forEach(v => v.sale_price = isNaN(val) ? null : val);
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

        // ==================== INITIALIZATION ====================
        document.addEventListener('DOMContentLoaded', () => {
            switchProductType(currentProductType);
            renderAttributes();
            renderVariantsTable();

            // Initialize Classic CKEditor 5 on #field_description
            const descTextarea = document.querySelector('#field_description');
            if (descTextarea && window.ClassicEditor) {
                ClassicEditor.create(descTextarea, {
                    toolbar: [
                        'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote',
                        '|', 'insertTable', 'undo', 'redo'
                    ]
                }).then(editor => {
                    productEditorInstance = editor;
                    editor.model.document.on('change:data', () => {
                        descTextarea.value = editor.getData();
                    });
                }).catch(error => {
                    console.error('CKEditor initialization error:', error);
                });
            }

            // Sync data before form submit
            const mainForm = document.getElementById('product-main-form');
            if (mainForm) {
                mainForm.addEventListener('submit', () => {
                    if (productEditorInstance) {
                        descTextarea.value = productEditorInstance.getData();
                    }
                    syncAttributesJson();
                    syncVariantsJson();
                });
            }

            // SEO Live Snippet Preview & Counters
            const nameInput = document.getElementById('field_name');
            const slugInput = document.getElementById('field_slug');
            const seoTitleInput = document.getElementById('seo_title');
            const seoDescInput = document.getElementById('seo_description');

            const previewTitle = document.getElementById('seo-preview-title');
            const previewSlug = document.getElementById('seo-preview-slug');
            const previewDesc = document.getElementById('seo-preview-desc');

            const titleCount = document.getElementById('seo-title-count');
            const descCount = document.getElementById('seo-desc-count');

            const updateSeoPreview = () => {
                if (previewTitle) {
                    const t = (seoTitleInput && seoTitleInput.value.trim()) || (nameInput && nameInput.value.trim()) || 'Tiêu đề sản phẩm sẽ hiển thị ở đây trên Google';
                    previewTitle.innerText = t;
                }
                if (previewSlug && slugInput) {
                    previewSlug.innerText = slugInput.value.trim() || 'duong-dan-san-pham';
                }
                if (previewDesc) {
                    const d = (seoDescInput && seoDescInput.value.trim()) || 'Mô tả ngắn gọn về sản phẩm để thu hút khách hàng nhấp vào kết quả tìm kiếm trên Google...';
                    previewDesc.innerText = d;
                }
                if (titleCount && seoTitleInput) {
                    titleCount.innerText = seoTitleInput.value.length;
                }
                if (descCount && seoDescInput) {
                    descCount.innerText = seoDescInput.value.length;
                }
            };

            [nameInput, slugInput, seoTitleInput, seoDescInput].forEach(el => {
                if (el) {
                    el.addEventListener('input', updateSeoPreview);
                }
            });
            updateSeoPreview();
        });
    </script>
@endsection
