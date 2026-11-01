@extends('admin.layouts.app', ['title' => $title])

@php
    $mainFields = [];
    $pricingFields = [];
    $seoFields = [];
    $sidebarMetaFields = [];
    $sidebarMediaFields = [];
    $sidebarTaxonomyFields = [];

    $metaFieldKeys = [
        'is_visible', 'featured', 'type', 'status', 'is_approved', 'role',
        'published_at', 'author_id', 'shop_brand_id', 'department_id',
        'apply_tax', 'backorder', 'requires_shipping', 'is_active', 'is_urgent'
    ];
    $pricingFieldKeys = [
        'price', 'old_price', 'cost', 'sale_price', 'sale_start_date', 'sale_end_date',
        'qty', 'security_stock', 'stock_quantity', 'low_stock_threshold',
        'amount', 'base_salary', 'allowance', 'tax', 'insurance', 'net_salary'
    ];

    foreach ($fields as $name => $field) {
        $type = $field['type'] ?? 'text';
        if ($type === 'image') {
            $sidebarMediaFields[$name] = $field;
        } elseif (in_array($name, ['categories', 'tags'], true) || in_array($type, ['tree-select', 'tags-checkboxes'], true)) {
            $sidebarTaxonomyFields[$name] = $field;
        } elseif (in_array($name, ['seo_title', 'seo_description'], true) || \Illuminate\Support\Str::startsWith($name, 'seo_')) {
            $seoFields[$name] = $field;
        } elseif (in_array($name, $metaFieldKeys, true)) {
            $sidebarMetaFields[$name] = $field;
        } elseif (in_array($name, $pricingFieldKeys, true)) {
            $pricingFields[$name] = $field;
        } else {
            $mainFields[$name] = $field;
        }
    }

    // Ensure main fields order has title/name & slug/sku first
    $topKeys = ['title', 'name', 'slug', 'sku'];
    $orderedMain = [];
    foreach ($topKeys as $k) {
        if (isset($mainFields[$k])) {
            $orderedMain[$k] = $mainFields[$k];
            unset($mainFields[$k]);
        }
    }
    $mainFields = array_merge($orderedMain, $mainFields);

    // Reorder pricing fields so sale_start_date and sale_end_date are side-by-side on the same row
    $pricingOrder = ['price', 'old_price', 'cost', 'sale_price', 'sale_start_date', 'sale_end_date', 'qty', 'security_stock', 'backorder', 'requires_shipping'];
    $orderedPricing = [];
    foreach ($pricingOrder as $pk) {
        if (isset($pricingFields[$pk])) {
            $orderedPricing[$pk] = $pricingFields[$pk];
            unset($pricingFields[$pk]);
        }
    }
    $pricingFields = array_merge($orderedPricing, $pricingFields);

    $hasSidebar = !empty($sidebarMetaFields) || !empty($sidebarMediaFields) || !empty($sidebarTaxonomyFields);
@endphp

@section('content')
    <x-admin.page-header
        :title="$title"
        :description="__('admin.common.record_details') . ' #' . $record->id"
        :backUrl="route($routePrefix . '.index')"
    >
        <x-slot:actions>
            <a class="btn btn-secondary" href="{{ route($routePrefix . '.index') }}">
                &larr; {{ __('admin.actions.back') }}
            </a>
            @if ($canEdit ?? true)
                <a class="btn btn-primary" href="{{ route($routePrefix . '.edit', $record->id) }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    <span>{{ __('admin.actions.edit') }}</span>
                </a>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    @if (!empty($tabs))
        <div style="margin-bottom: var(--fi-space-4, 16px);">
            <x-admin.tabs
                mode="link"
                :active="$activeTab ?? ''"
                :tabs="$tabs"
            />
        </div>
    @endif

    <div class="{{ $hasSidebar ? 'edit-layout' : '' }}">
        <!-- ================= MAIN CONTENT COLUMN ================= -->
        <div class="{{ $hasSidebar ? 'show-main' : '' }}">
            <!-- Card 1: Thông tin nội dung chính -->
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--fi-primary);"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        <h3 class="card-title">Thông tin nội dung chính</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        @foreach ($mainFields as $name => $field)
                            @php
                                $type = $field['type'] ?? 'text';
                                $value = data_get($record, $name);
                                $isWide = in_array($type, ['textarea', 'editor', 'blocks'], true) || in_array($name, ['content', 'description', 'blocks'], true) || ($field['colspan'] ?? null) === 'full';
                            @endphp
                            <div class="form-row {{ $isWide ? 'form-row-wide' : '' }}">
                                <div class="field-label" style="font-size: 11.5px; font-weight: 700; color: var(--fi-muted); text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 6px;">
                                    {{ $field['label'] ?? $name }}
                                </div>
                                <div class="field-value" style="font-size: 13.5px; color: var(--fi-text);">
                                    @include('admin.crud.show_field', ['type' => $type, 'value' => $value, 'record' => $record, 'name' => $name, 'field' => $field])
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Card 2: Giá bán & Quản lý kho hàng (Nếu có trường pricing) -->
            @if (!empty($pricingFields))
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #059669;"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                            <h3 class="card-title">Giá bán & Quản lý kho hàng</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            @foreach ($pricingFields as $name => $field)
                                @if ($name === 'sale_start_date' && isset($pricingFields['sale_end_date']))
                                    @php
                                        $startField = $field;
                                        $endField = $pricingFields['sale_end_date'];
                                        $startVal = data_get($record, 'sale_start_date');
                                        $endVal = data_get($record, 'sale_end_date');
                                    @endphp
                                    <div class="form-row form-row-wide" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px;">
                                        <div>
                                            <div class="field-label" style="font-size: 11.5px; font-weight: 700; color: var(--fi-muted); text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 6px;">
                                                {{ $startField['label'] ?? 'Ngày bắt đầu KM' }}
                                            </div>
                                            <div class="field-value" style="font-size: 13.5px; color: var(--fi-text);">
                                                @include('admin.crud.show_field', ['type' => $startField['type'] ?? 'datetime-local', 'value' => $startVal, 'record' => $record, 'name' => 'sale_start_date', 'field' => $startField])
                                            </div>
                                        </div>
                                        <div>
                                            <div class="field-label" style="font-size: 11.5px; font-weight: 700; color: var(--fi-muted); text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 6px;">
                                                {{ $endField['label'] ?? 'Ngày kết thúc KM' }}
                                            </div>
                                            <div class="field-value" style="font-size: 13.5px; color: var(--fi-text);">
                                                @include('admin.crud.show_field', ['type' => $endField['type'] ?? 'datetime-local', 'value' => $endVal, 'record' => $record, 'name' => 'sale_end_date', 'field' => $endField])
                                            </div>
                                        </div>
                                    </div>
                                @elseif ($name === 'sale_end_date' && isset($pricingFields['sale_start_date']))
                                    @continue
                                @else
                                    @php
                                        $type = $field['type'] ?? 'number';
                                        $value = data_get($record, $name);
                                    @endphp
                                    <div class="form-row">
                                        <div class="field-label" style="font-size: 11.5px; font-weight: 700; color: var(--fi-muted); text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 6px;">
                                            {{ $field['label'] ?? $name }}
                                        </div>
                                        <div class="field-value" style="font-size: 13.5px; color: var(--fi-text);">
                                            @include('admin.crud.show_field', ['type' => $type, 'value' => $value, 'record' => $record, 'name' => $name, 'field' => $field])
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Card 3: Tối ưu hóa SEO (Google SEO) -->
            @if (!empty($seoFields))
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #2563eb;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            <h3 class="card-title">Tối ưu hóa công cụ tìm kiếm (Google SEO)</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="seo-snippet-preview" style="background: #f8fafc; border: 1px solid var(--fi-border); border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
                            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                                <div style="width: 14px; height: 14px; border-radius: 50%; background: #2563eb; display: grid; place-items: center; color: #fff; font-size: 8px; font-weight: 800;">G</div>
                                <div class="seo-snippet-url" style="font-size: 11.5px; color: #475569;">
                                    https://mycompany.com/{{ $routePrefix === 'admin.posts' ? 'posts' : 'products' }}/<span>{{ $record->slug ?? 'duong-dan' }}</span>
                                </div>
                            </div>
                            <div class="seo-snippet-title" style="font-size: 15px; font-weight: 600; color: #1a0dab; margin-bottom: 3px;">
                                {{ $record->seo_title ?? ($record->title ?? ($record->name ?? 'Tiêu đề trang')) }}
                            </div>
                            <div class="seo-snippet-desc" style="font-size: 12.5px; color: #475569; line-height: 1.45;">
                                {{ $record->seo_description ?? 'Chưa có mô tả tóm tắt cho công cụ tìm kiếm.' }}
                            </div>
                        </div>

                        <div class="form-grid">
                            @foreach ($seoFields as $name => $field)
                                @php
                                    $type = $field['type'] ?? 'text';
                                    $value = data_get($record, $name);
                                @endphp
                                <div class="form-row form-row-wide">
                                    <div class="field-label" style="font-size: 11.5px; font-weight: 700; color: var(--fi-muted); text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 6px;">
                                        {{ $field['label'] ?? $name }}
                                    </div>
                                    <div class="field-value" style="font-size: 13.5px; color: var(--fi-text);">
                                        @include('admin.crud.show_field', ['type' => $type, 'value' => $value, 'record' => $record, 'name' => $name, 'field' => $field])
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Card 4: Đánh giá & Bình luận sản phẩm / Bình luận bài viết nếu có -->
            @php
                $isProduct = ($record instanceof \App\Models\Product) || ($routePrefix === 'admin.products') || (isset($routePrefix) && str_contains($routePrefix, 'products')) || request()->is('*products*');
                $isPost = ($record instanceof \App\Models\Post) || ($routePrefix === 'admin.posts') || (isset($routePrefix) && str_contains($routePrefix, 'posts')) || request()->is('*posts*');
            @endphp
            @if($record && ($isProduct || $isPost) && method_exists($record, 'comments'))
                <div class="card" style="overflow: hidden;">
                    <div class="card-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #64748b;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            <h3 class="card-title">
                                {{ $isProduct ? 'Đánh giá & Bình luận sản phẩm' : 'Bình luận bài viết' }}
                                ({{ $record->comments->count() }})
                            </h3>
                        </div>
                    </div>
                    <div class="table-wrap" style="border: 0; border-radius: 0;">
                        <table>
                            <thead>
                                <tr>
                                    <th style="padding: 10px 14px;">Tác giả</th>
                                    <th style="padding: 10px 14px;">Nội dung</th>
                                    <th style="padding: 10px 14px;">Thời gian</th>
                                    <th style="padding: 10px 14px; text-align: center;">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($record->comments as $comment)
                                    <tr>
                                        <td style="font-weight: 600; color: var(--fi-text); padding: 10px 14px;">{{ $comment->author->name ?? $comment->author_name ?? 'Khách' }}</td>
                                        <td style="padding: 10px 14px;">{{ Str::limit($comment->content, 100) }}</td>
                                        <td class="cell-muted mono" style="padding: 10px 14px;">{{ $comment->created_at->format('d/m/Y H:i') }}</td>
                                        <td style="padding: 10px 14px; text-align: center;"><x-admin.status-badge :status="$comment->is_approved ?? $comment->status ?? 'pending'" /></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="empty-state" style="padding: 24px; text-align: center; color: var(--fi-muted);">
                                            {{ $isProduct ? 'Chưa có đánh giá hoặc bình luận nào cho sản phẩm này.' : 'Chưa có bình luận nào cho bài viết này.' }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- ================= SIDEBAR COLUMN ================= -->
        @if ($hasSidebar)
            <div class="show-sidebar">
                <!-- Card A: Thiết lập & Trạng thái -->
                @if (!empty($sidebarMetaFields))
                    <div class="card">
                        <div class="card-header">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--fi-gold);"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                <h3 class="card-title">Thiết lập & Trạng thái</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div style="display: flex; flex-direction: column; gap: 14px;">
                                @foreach ($sidebarMetaFields as $name => $field)
                                    @php
                                        $type = $field['type'] ?? 'text';
                                        $value = data_get($record, $name);
                                    @endphp
                                    <div class="form-row">
                                        <div class="field-label" style="font-size: 11.5px; font-weight: 700; color: var(--fi-muted); text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 5px;">
                                            {{ $field['label'] ?? $name }}
                                        </div>
                                        <div class="field-value" style="font-size: 13.5px; color: var(--fi-text);">
                                            @include('admin.crud.show_field', ['type' => $type, 'value' => $value, 'record' => $record, 'name' => $name, 'field' => $field])
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Card B: Hình ảnh đại diện (Media) -->
                @if (!empty($sidebarMediaFields))
                    <div class="card">
                        <div class="card-header">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #6366f1;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                <h3 class="card-title">Hình ảnh đại diện</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div style="display: flex; flex-direction: column; gap: 14px;">
                                @foreach ($sidebarMediaFields as $name => $field)
                                    @php
                                        $type = $field['type'] ?? 'image';
                                        $value = data_get($record, $name);
                                    @endphp
                                    <div class="form-row">
                                        <div class="field-value">
                                            @include('admin.crud.show_field', ['type' => $type, 'value' => $value, 'record' => $record, 'name' => $name, 'field' => $field])
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Card C: Phân loại & Danh mục -->
                @if (!empty($sidebarTaxonomyFields))
                    <div class="card">
                        <div class="card-header">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #0891b2;"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                                <h3 class="card-title">Phân loại & Danh mục</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div style="display: flex; flex-direction: column; gap: 14px;">
                                @foreach ($sidebarTaxonomyFields as $name => $field)
                                    @php
                                        $type = $field['type'] ?? 'text';
                                        $value = data_get($record, $name);
                                    @endphp
                                    <div class="form-row">
                                        <div class="field-label" style="font-size: 11.5px; font-weight: 700; color: var(--fi-muted); text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 5px;">
                                            {{ $field['label'] ?? $name }}
                                        </div>
                                        <div class="field-value">
                                            @if (in_array($name, ['categories', 'shop_categories'], true) && method_exists($record, 'categories'))
                                                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                                    @forelse ($record->categories as $cat)
                                                        <span class="badge bg-blue" style="font-size: 12px; font-weight: 600;">{{ $cat->name }}</span>
                                                    @empty
                                                        <span class="val-empty">—</span>
                                                    @endforelse
                                                </div>
                                            @elseif ($name === 'tags' && method_exists($record, 'tags'))
                                                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                                    @forelse ($record->tags as $t)
                                                        <span class="badge badge-secondary" style="font-size: 11px;">#{{ $t->name }}</span>
                                                    @empty
                                                        <span class="val-empty">—</span>
                                                    @endforelse
                                                </div>
                                            @else
                                                @include('admin.crud.show_field', ['type' => $type, 'value' => $value, 'record' => $record, 'name' => $name, 'field' => $field])
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
