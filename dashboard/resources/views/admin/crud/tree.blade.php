@extends('admin.layouts.app', ['title' => $title])

@php
    $items = $items ?? $allCategories ?? collect();
    $groupedCategories = $groupedCategories ?? $items->groupBy('parent_id');
    $rootCategories = $rootCategories ?? $groupedCategories->get(null, collect());
    
    // Safety check for orphans
    $allIds = $items->pluck('id')->toArray();
    $orphans = $items->filter(function ($item) use ($allIds) {
        return !is_null($item->parent_id) && !in_array($item->parent_id, $allIds, true);
    });
    if ($orphans->isNotEmpty()) {
        $rootCategories = $rootCategories->merge($orphans);
    }

    $entityLabel = $entityLabel ?? 'danh mục';
    $entityPlural = $entityPlural ?? ($entityLabel . 's');
    $itemCountLabel = $itemCountLabel ?? 'sp';
    $itemCountRelation = $itemCountRelation ?? 'products_count';
    $itemCountRoute = $itemCountRoute ?? 'admin.products.index';
    $itemCountParam = $itemCountParam ?? 'category_id';

    // Metrics
    $metrics = $metrics ?? [
        'total' => $items->count(),
        'root' => $rootCategories->count(),
        'visible' => $items->where('is_visible', true)->count(),
        'hidden' => $items->where('is_visible', false)->count(),
        'total_items' => $items->sum($itemCountRelation),
    ];
@endphp

@section('content')
<div class="tree-admin-container" x-data="treeAdmin({
    csrfToken: '{{ csrf_token() }}',
    toggleUrl: '{{ url(str_replace('admin.', 'admin/', $routePrefix)) }}',
    reorderUrl: '{{ route($routePrefix . '.reorder') }}'
})" x-cloak>

    <!-- 1. TOP BAR -->
    <div class="top-nav-bar">
        <div class="top-nav-left">
            <div class="clean-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Trang chủ</a>
                <span>/</span>
                <span>{{ $title }}</span>
            </div>
            <div class="title-with-count">
                <h1 class="main-heading">{{ $title }}</h1>
                <span class="quiet-count">({{ $metrics['total'] }})</span>
            </div>
        </div>

        <div class="top-nav-actions">
            @if (\Illuminate\Support\Facades\Route::has($routePrefix . '.export'))
                <a href="{{ route($routePrefix . '.export') }}" class="btn-clean">Xuất CSV</a>
            @endif

            <a href="{{ route($routePrefix . '.index') }}" class="btn-clean">Làm mới</a>

            @if ($canCreate ?? true)
                <a href="{{ route($routePrefix . '.create') }}" class="btn-primary-green">+ Thêm {{ $entityLabel }}</a>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="clean-alert-bar">
            {{ session('status') }}
        </div>
    @endif

    <!-- 2. MAIN TREE CONTAINER -->
    <div class="tree-main-panel">
        
        <!-- TOOLBAR: FILTER TABS, SEARCH, CONTROLS -->
        <div class="tree-action-bar">
            <!-- Filter Tabs -->
            <div class="quiet-tabs">
                <button type="button" 
                        @click="activeStatusFilter = 'all'" 
                        :class="{ 'is-active': activeStatusFilter === 'all' }" 
                        class="tab-link">
                    Tất cả <span class="tab-num">{{ $metrics['total'] }}</span>
                </button>
                <button type="button" 
                        @click="activeStatusFilter = 'visible'" 
                        :class="{ 'is-active': activeStatusFilter === 'visible' }" 
                        class="tab-link">
                    Hiển thị <span class="tab-num">{{ $metrics['visible'] }}</span>
                </button>
                <button type="button" 
                        @click="activeStatusFilter = 'hidden'" 
                        :class="{ 'is-active': activeStatusFilter === 'hidden' }" 
                        class="tab-link">
                    Đang ẩn <span class="tab-num">{{ $metrics['hidden'] }}</span>
                </button>
            </div>

            <!-- Search Field -->
            <div class="quiet-search-box">
                <input type="text" 
                       x-model="searchTerm" 
                       @input="onSearchInput()"
                       placeholder="Tìm kiếm {{ $entityLabel }}..." 
                       class="quiet-search-input">
                <button type="button" 
                        x-show="searchTerm.length > 0" 
                        @click="searchTerm = ''; onSearchInput();" 
                        class="clear-text-btn">✕</button>
            </div>

            <!-- Controls -->
            <div class="quiet-controls">
                <button type="button" @click="expandAll()" class="btn-quiet-action">Mở rộng</button>
                <button type="button" @click="collapseAll()" class="btn-quiet-action">Thu gọn</button>
            </div>
        </div>

        <!-- 3. TREE VIEW -->
        <div class="tree-content-area">
            @if ($rootCategories->isEmpty())
                <div class="clean-empty-state">
                    <p>Chưa có {{ $entityLabel }} nào.</p>
                    @if ($canCreate ?? true)
                        <a href="{{ route($routePrefix . '.create') }}" class="btn-primary-green">+ Tạo {{ $entityLabel }} đầu tiên</a>
                    @endif
                </div>
            @else
                <div class="dd" id="master-nestable-tree">
                    <ol class="dd-list">
                        @php
                            $renderNode = function ($node, $depth = 0) use (&$renderNode, $groupedCategories, $routePrefix, $entityLabel, $itemCountRelation, $itemCountLabel, $itemCountRoute, $itemCountParam) {
                                $children = $groupedCategories->get($node->id, collect());
                                $hasChildren = $children->isNotEmpty();
                                $displayName = method_exists($node, 'getDisplayNameAttribute') ? $node->display_name : ($node->name ?? 'Mục #' . $node->id);
                                $itemCount = $node->{$itemCountRelation} ?? 0;
                                $sortOrder = $node->sort ?? $node->order_column ?? 0;
                                $isVisible = isset($node->is_visible) ? (bool) $node->is_visible : true;

                                echo '<li class="dd-item tree-row-item" data-id="' . $node->id . '" data-status="' . ($isVisible ? 'visible' : 'hidden') . '" data-search="' . strtolower(addslashes($displayName . ' ' . ($node->slug ?? '') . ' ' . $node->id)) . '">';
                                
                                echo '<div class="tree-row-content">';
                                
                                // LEFT: Drag Grip, Name, Slug
                                echo '  <div class="row-left">';
                                echo '      <span class="row-drag-handle" title="Kéo để đổi vị trí hoặc cấp bậc">⋮⋮</span>';
                                
                                echo '      <div class="row-meta dd-nodrag">';
                                echo '          <a href="' . e(route($routePrefix . '.edit', $node->id)) . '" class="item-title">' . e($displayName) . '</a>';
                                echo '          <span class="item-slug">/' . e($node->slug ?? '') . '</span>';
                                echo '      </div>';
                                echo '  </div>';

                                // RIGHT: Thứ tự, Count, Toggle, Actions
                                echo '  <div class="row-right dd-nodrag">';

                                // THỨ TỰ
                                echo '      <span class="meta-order">Thứ tự: ' . $sortOrder . '</span>';

                                // COUNT LIÊN KẾT
                                $routeExists = \Illuminate\Support\Facades\Route::has($itemCountRoute);
                                $countUrl = $routeExists ? route($itemCountRoute, [$itemCountParam => $node->id]) : '#';
                                
                                echo '      <a href="' . e($countUrl) . '" class="meta-count ' . ($itemCount > 0 ? 'has-count' : 'zero-count') . '" title="Xem danh sách">';
                                echo '          ' . $itemCount . ' ' . $itemCountLabel;
                                echo '      </a>';

                                // SHOPIFY SLIDING TOGGLE SWITCH
                                echo '      <button type="button" class="clean-switch ' . ($isVisible ? 'is-active' : '') . '" data-id="' . $node->id . '" onclick="window.cleanToggleStatus(' . $node->id . ', this);" title="Bấm để ' . ($isVisible ? 'tạm ẩn' : 'bật hiển thị') . '">';
                                echo '          <span class="switch-rail"><span class="switch-ball"></span></span>';
                                echo '          <span class="switch-status-text">' . ($isVisible ? 'Hiển thị' : 'Đang ẩn') . '</span>';
                                echo '      </button>';

                                // ACTION LINKS
                                echo '      <div class="meta-actions">';
                                echo '          <a href="' . e(route($routePrefix . '.create', ['parent_id' => $node->id])) . '" class="action-link" title="Thêm danh mục con">+ Con</a>';
                                echo '          <span class="action-sep">•</span>';
                                echo '          <a href="' . e(route($routePrefix . '.edit', $node->id)) . '" class="action-link">Sửa</a>';
                                echo '          <span class="action-sep">•</span>';
                                echo '          <form method="POST" action="' . e(route($routePrefix . '.destroy', $node->id)) . '" style="display:inline; margin:0;" onsubmit="return confirm(\'Xóa ' . $entityLabel . ' «' . addslashes($displayName) . '»?\');">';
                                echo csrf_field();
                                echo method_field('DELETE');
                                echo '              <button type="submit" class="action-link link-del">Xóa</button>';
                                echo '          </form>';
                                echo '      </div>';

                                echo '  </div>';
                                echo '</div>'; // End tree-row-content

                                // CHILDREN
                                if ($hasChildren) {
                                    echo '<ol class="dd-list tree-branch">';
                                    foreach ($children as $child) {
                                        $renderNode($child, $depth + 1);
                                    }
                                    echo '</ol>';
                                }

                                echo '</li>';
                            };
                        @endphp

                        @foreach ($rootCategories as $rootNode)
                            {!! $renderNode($rootNode, 0) !!}
                        @endforeach
                    </ol>
                </div>

                @if ($canCreate ?? true)
                    <div class="bottom-add-strip">
                        <a href="{{ route($routePrefix . '.create') }}" class="add-root-link">+ Thêm {{ $entityLabel }} gốc</a>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- 4. FLOATING SAVE BAR -->
    <div id="clean-save-bar" class="clean-save-bar" style="display: none;">
        <div class="save-bar-inner">
            <span class="save-bar-msg">Vị trí phân cấp đã thay đổi.</span>
            <div class="save-bar-btns">
                <button type="button" onclick="window.location.reload();" class="btn-bar-discard">Hủy</button>
                <button id="btn-save-order" type="button" class="btn-bar-save">Lưu cấu trúc</button>
            </div>
        </div>
    </div>

    <!-- 5. TOAST -->
    <div x-show="toast.visible" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="clean-toast"
         :class="toast.type === 'error' ? 'toast-error' : 'toast-ok'"
         x-text="toast.message">
    </div>

</div>

<!-- =========================================================
     CLEAN, UNDERSTATED SHOPIFY STYLES (NO DECORATIVE ICONS)
     ========================================================= -->
<style>
    .tree-admin-container {
        max-width: 1140px;
        margin: 0 auto;
        padding-bottom: 80px;
        color: #1e293b;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    /* Top Bar */
    .top-nav-bar {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 16px;
        gap: 12px;
        flex-wrap: wrap;
    }
    .clean-breadcrumb {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .clean-breadcrumb a {
        color: #64748b;
        text-decoration: none;
    }
    .clean-breadcrumb a:hover { color: #0f172a; }
    .clean-breadcrumb span { color: #94a3b8; }

    .title-with-count {
        display: flex;
        align-items: baseline;
        gap: 8px;
    }
    .main-heading {
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        letter-spacing: -0.01em;
    }
    .quiet-count {
        font-size: 13px;
        color: #64748b;
        font-weight: 500;
    }

    .top-nav-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-clean {
        height: 32px;
        padding: 0 12px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 12.5px;
        font-weight: 500;
        color: #334155;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        transition: all 0.12s ease;
    }
    .btn-clean:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a;
    }
    .btn-primary-green {
        height: 32px;
        padding: 0 14px;
        background: #008060;
        border: 1px solid #006e52;
        border-radius: 6px;
        font-size: 12.5px;
        font-weight: 600;
        color: #ffffff;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        transition: background 0.12s ease;
    }
    .btn-primary-green:hover { background: #006e52; }

    .clean-alert-bar {
        padding: 10px 14px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        font-size: 12.5px;
        border-radius: 6px;
        margin-bottom: 14px;
    }

    /* Main Panel */
    .tree-main-panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        overflow: hidden;
    }

    /* Toolbar */
    .tree-action-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 16px;
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        gap: 12px;
        flex-wrap: wrap;
    }
    .quiet-tabs {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .tab-link {
        border: none;
        background: transparent;
        color: #64748b;
        font-size: 12.5px;
        font-weight: 500;
        padding: 5px 9px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.12s ease;
    }
    .tab-link:hover { color: #0f172a; background: #f8fafc; }
    .tab-link.is-active {
        color: #0f172a;
        font-weight: 600;
        background: #f1f5f9;
    }
    .tab-num {
        font-size: 11px;
        color: #94a3b8;
        margin-left: 2px;
    }
    .tab-link.is-active .tab-num { color: #475569; }

    .quiet-search-box {
        position: relative;
        flex: 1;
        min-width: 200px;
        max-width: 320px;
    }
    .quiet-search-input {
        width: 100%;
        height: 30px;
        padding: 0 24px 0 10px;
        border: 1px solid #cbd5e1;
        border-radius: 5px;
        font-size: 12px;
        color: #0f172a;
        background: #ffffff;
        outline: none;
    }
    .quiet-search-input:focus {
        border-color: #008060;
    }
    .clear-text-btn {
        position: absolute;
        right: 6px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: transparent;
        color: #94a3b8;
        cursor: pointer;
        font-size: 11px;
    }

    .quiet-controls {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .btn-quiet-action {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        font-size: 11.5px;
        padding: 4px 8px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 500;
    }
    .btn-quiet-action:hover {
        background: #f8fafc;
        color: #0f172a;
    }

    /* Tree Content */
    .tree-content-area {
        padding: 10px 16px 16px;
    }

    /* Nestable */
    .dd { position: relative; display: block; margin: 0; padding: 0; list-style: none; }
    .dd-list { display: block; position: relative; margin: 0; padding: 0; list-style: none; }
    .dd-list .dd-list {
        padding-left: 24px;
        border-left: 1px solid #e2e8f0;
        margin-left: 16px;
        margin-top: 1px;
    }
    .dd-item { display: block; position: relative; margin: 0; padding: 0; }
    
    .dd-placeholder, .dd-empty {
        margin: 3px 0;
        padding: 0;
        min-height: 40px;
        background: #f0fdf4;
        border: 1px dashed #10b981;
        border-radius: 6px;
    }
    .dd-dragel {
        position: absolute;
        pointer-events: none;
        z-index: 9999;
    }
    .dd-dragel > .dd-item .tree-row-content {
        box-shadow: 0 10px 20px -3px rgba(15, 23, 42, 0.14);
        border-color: #008060;
        background: #ffffff;
    }

    /* Minimalist Collapse Button */
    .dd-item > button {
        position: relative;
        cursor: pointer;
        float: left;
        width: 18px;
        height: 38px;
        margin: 0 4px 0 -22px;
        padding: 0;
        text-indent: 100%;
        white-space: nowrap;
        overflow: hidden;
        border: 0;
        background: transparent;
    }
    .dd-item > button:before {
        content: '+';
        display: block;
        position: absolute;
        width: 15px;
        height: 15px;
        line-height: 13px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        border-radius: 3px;
        text-align: center;
        text-indent: 0;
        color: #475569;
        margin-top: 12px;
        font-weight: 600;
        font-size: 11px;
    }
    .dd-item > button[data-action="collapse"]:before {
        content: '−';
        color: #0f172a;
    }

    /* Tree Row */
    .tree-row-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        margin: 3px 0;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        transition: background 0.1s ease, border-color 0.1s ease;
        gap: 12px;
    }
    .tree-row-content:hover {
        background: #fafbfc;
        border-color: #cbd5e1;
    }
    .tree-row-item.is-dimmed { opacity: 0.25; }
    .tree-row-item.is-matched > .tree-row-content {
        border-color: #008060;
        background: #f0fdf4;
    }

    /* Row Left */
    .row-left {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        flex: 1;
    }
    .row-drag-handle {
        color: #94a3b8;
        cursor: grab;
        font-size: 13px;
        letter-spacing: -1px;
        user-select: none;
        line-height: 1;
        padding: 4px 2px;
    }
    .row-drag-handle:hover { color: #0f172a; }
    .row-drag-handle:active { cursor: grabbing; }

    .row-meta {
        display: flex;
        align-items: baseline;
        gap: 8px;
        min-width: 0;
        flex-wrap: wrap;
    }
    .item-title {
        font-size: 13px;
        font-weight: 600;
        color: #0f172a;
        text-decoration: none;
    }
    .item-title:hover {
        color: #008060;
        text-decoration: underline;
    }
    .item-slug {
        font-size: 11.5px;
        color: #64748b;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }

    /* Row Right */
    .row-right {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }

    .meta-order {
        font-size: 11.5px;
        color: #64748b;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 2px 6px;
        border-radius: 4px;
        white-space: nowrap;
    }

    .meta-count {
        font-size: 12px;
        text-decoration: none;
        color: #475569;
        white-space: nowrap;
    }
    .meta-count:hover {
        color: #008060;
        text-decoration: underline;
    }
    .meta-count.has-count {
        color: #166534;
        font-weight: 600;
    }

    /* Clean Sliding Switch */
    .clean-switch {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: none;
        background: transparent;
        cursor: pointer;
        padding: 0;
    }
    .switch-rail {
        width: 30px;
        height: 16px;
        background: #cbd5e1;
        border-radius: 999px;
        position: relative;
        transition: background 0.15s ease;
        display: inline-block;
    }
    .switch-ball {
        position: absolute;
        top: 2px;
        left: 2px;
        width: 12px;
        height: 12px;
        background: #ffffff;
        border-radius: 999px;
        transition: transform 0.15s ease;
    }
    .clean-switch.is-active .switch-rail {
        background: #008060;
    }
    .clean-switch.is-active .switch-ball {
        transform: translateX(14px);
    }
    .switch-status-text {
        font-size: 11.5px;
        font-weight: 500;
        color: #64748b;
        min-width: 44px;
        text-align: left;
    }
    .clean-switch.is-active .switch-status-text {
        color: #047857;
    }

    /* Action Links */
    .meta-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .action-link {
        font-size: 12px;
        color: #475569;
        text-decoration: none;
        font-weight: 500;
        border: none;
        background: transparent;
        cursor: pointer;
        padding: 0;
    }
    .action-link:hover {
        color: #008060;
        text-decoration: underline;
    }
    .action-link.link-del:hover {
        color: #dc2626;
    }
    .action-sep {
        color: #cbd5e1;
        font-size: 10px;
    }

    /* Bottom Link */
    .bottom-add-strip {
        margin-top: 10px;
        padding-top: 8px;
        border-top: 1px dashed #e2e8f0;
    }
    .add-root-link {
        font-size: 12px;
        color: #008060;
        text-decoration: none;
        font-weight: 600;
    }
    .add-root-link:hover { text-decoration: underline; }

    /* Empty */
    .clean-empty-state {
        text-align: center;
        padding: 36px 16px;
        color: #64748b;
        font-size: 13px;
    }

    /* Floating Save Bar */
    .clean-save-bar {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 990;
    }
    .save-bar-inner {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 9px 16px;
        background: #0f172a;
        color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 10px 25px -4px rgba(15, 23, 42, 0.25);
    }
    .save-bar-msg {
        font-size: 12.5px;
        font-weight: 500;
    }
    .save-bar-btns {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-bar-discard {
        background: transparent;
        border: none;
        color: #94a3b8;
        font-size: 12px;
        cursor: pointer;
        padding: 4px 8px;
    }
    .btn-bar-discard:hover { color: #ffffff; }
    .btn-bar-save {
        background: #008060;
        border: none;
        color: #ffffff;
        padding: 5px 12px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-bar-save:hover { background: #006e52; }

    /* Toast */
    .clean-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        padding: 9px 14px;
        border-radius: 6px;
        font-size: 12.5px;
        font-weight: 500;
        color: #ffffff;
    }
    .toast-ok { background: #065f46; }
    .toast-err { background: #991b1b; }
</style>

<!-- =========================================================
     JAVASCRIPT: ALPINE.JS LOGIC & NESTABLE TREE INTERACTION
     ========================================================= -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.js"></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('treeAdmin', (config) => ({
            searchTerm: '',
            activeStatusFilter: 'all',
            toast: {
                visible: false,
                message: '',
                type: 'success',
                timer: null
            },

            init() {
                this.$nextTick(() => {
                    this.initTree();
                });

                this.$watch('activeStatusFilter', () => {
                    this.applyFilters();
                });
            },

            showToast(message, type = 'success') {
                this.toast.message = message;
                this.toast.type = type;
                this.toast.visible = true;

                if (this.toast.timer) clearTimeout(this.toast.timer);
                this.toast.timer = setTimeout(() => {
                    this.toast.visible = false;
                }, 2500);
            },

            initTree() {
                const $tree = $('#master-nestable-tree');
                const $saveBar = $('#clean-save-bar');
                const $saveBtn = $('#btn-save-order');
                const self = this;

                if (!$tree.length) return;

                $tree.nestable({
                    group: 1,
                    maxDepth: 5,
                    handleClass: 'row-drag-handle',
                    itemClass: 'tree-row-item',
                    listClass: 'dd-list',
                    placeClass: 'dd-placeholder',
                    collapsedClass: 'dd-collapsed',
                    dragClass: 'dd-dragel'
                }).on('change', function() {
                    $saveBar.fadeIn(150);
                    self.updateVisualSortOrder();
                });

                $saveBtn.off('click').on('click', function() {
                    const data = $tree.nestable('serialize');
                    const btn = $(this);
                    const originalText = btn.text();

                    btn.prop('disabled', true).text('Đang lưu...');

                    $.ajax({
                        url: config.reorderUrl,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': config.csrfToken,
                            'Accept': 'application/json'
                        },
                        data: { tree: data },
                        success: function() {
                            self.showToast('Đã lưu cấu trúc thành công!');
                            btn.text('✓ Đã lưu').css('background', '#047857');
                            setTimeout(() => {
                                $saveBar.fadeOut(150);
                                btn.prop('disabled', false).text(originalText).css('background', '');
                            }, 1000);
                        },
                        error: function() {
                            self.showToast('Lỗi khi lưu!', 'error');
                            btn.prop('disabled', false).text(originalText);
                        }
                    });
                });
            },

            updateVisualSortOrder() {
                $('#master-nestable-tree').find('> ol > li').each(function(rootIdx) {
                    $(this).find('> .tree-row-content .meta-order').text('Thứ tự: ' + rootIdx);
                    $(this).find('> ol > li').each(function(subIdx) {
                        $(this).find('> .tree-row-content .meta-order').text('Thứ tự: ' + subIdx);
                        $(this).find('> ol > li').each(function(subSubIdx) {
                            $(this).find('> .tree-row-content .meta-order').text('Thứ tự: ' + subSubIdx);
                        });
                    });
                });
            },

            expandAll() {
                $('#master-nestable-tree').nestable('expandAll');
            },

            collapseAll() {
                $('#master-nestable-tree').nestable('collapseAll');
            },

            onSearchInput() {
                this.applyFilters();
            },

            applyFilters() {
                const term = this.searchTerm.trim().toLowerCase();
                const status = this.activeStatusFilter;
                const $items = $('.tree-row-item');

                if (term === '' && status === 'all') {
                    $items.removeClass('is-dimmed is-matched').show();
                    return;
                }

                $items.each(function() {
                    const $item = $(this);
                    const itemSearch = ($item.attr('data-search') || '').toLowerCase();
                    const itemStatus = $item.attr('data-status') || '';

                    const matchesTerm = term === '' || itemSearch.includes(term);
                    const matchesStatus = status === 'all' || itemStatus === status;

                    if (matchesTerm && matchesStatus) {
                        $item.removeClass('is-dimmed').addClass('is-matched').show();
                        $item.parents('.tree-row-item').removeClass('is-dimmed').show();
                        $item.parents('.tree-branch').show();
                        $item.parents('.dd-item').removeClass('dd-collapsed');
                    } else {
                        if (term !== '') {
                            $item.removeClass('is-matched').addClass('is-dimmed');
                        } else {
                            $item.removeClass('is-matched is-dimmed');
                            if (!matchesStatus) {
                                $item.hide();
                            } else {
                                $item.show();
                            }
                        }
                    }
                });
            }
        }));
    });

    window.cleanToggleStatus = function(id, btn) {
        const isCurrentlyActive = btn.classList.contains('is-active');
        const nextStatus = !isCurrentlyActive;

        btn.classList.toggle('is-active', nextStatus);
        btn.querySelector('.switch-status-text').textContent = nextStatus ? 'Hiển thị' : 'Đang ẩn';
        btn.setAttribute('data-status', nextStatus ? '1' : '0');

        const treeItem = btn.closest('.tree-row-item');
        if (treeItem) {
            treeItem.setAttribute('data-status', nextStatus ? 'visible' : 'hidden');
        }

        const appElem = document.querySelector('.tree-admin-container');

        fetch(window.location.pathname.replace(/\/$/, '') + '/' + id + '/toggle-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ is_visible: nextStatus })
        }).then(res => res.json()).then(data => {
            if (data.success) {
                if (appElem && appElem.__x) {
                    appElem.__x.$data.showToast(data.message, 'success');
                }
            } else {
                throw new Error(data.message || 'Lỗi');
            }
        }).catch(err => {
            btn.classList.toggle('is-active', isCurrentlyActive);
            btn.querySelector('.switch-status-text').textContent = isCurrentlyActive ? 'Hiển thị' : 'Đang ẩn';
            alert('Không thể cập nhật: ' + err.message);
        });
    };
</script>
@endsection
