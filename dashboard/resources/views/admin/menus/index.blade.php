@extends('admin.layouts.app', ['title' => $title])

@php
    $renderMenuItem = function ($item, $depth = 0) use (&$renderMenuItem, $activeMenu, $flatItems) {
        $children = $item->children ?? collect();
        $viTitle = is_array($item->title) ? ($item->title['vi'] ?? '') : ($item->title ?? $item->label ?? '');
        $enTitle = is_array($item->title) ? ($item->title['en'] ?? '') : '';
        if (empty($viTitle)) {
            $viTitle = $item->label ?: ($item->name ?: 'Item #' . $item->id);
        }

        echo '<li class="dd-item" data-id="' . $item->id . '">';
        echo '<div class="dd-handle">';
        echo '<div style="display:flex; justify-content:space-between; align-items:center; gap: 12px; width: 100%;">';
        
        // Left info
        echo '<div style="display:flex; align-items:center; gap:10px; min-width:0; flex-wrap:wrap;">';
        echo '<span class="cell-muted mono" style="font-size:11px; font-weight:600;">#' . e($item->id) . '</span>';
        echo '<strong style="color: var(--fi-text); font-size:13.5px;">' . e($viTitle) . '</strong>';
        if (!empty($enTitle) && $enTitle !== $viTitle) {
            echo '<span class="cell-muted" style="font-size:11.5px;">(EN: ' . e($enTitle) . ')</span>';
        }
        echo '<span class="settings-chip mono" style="font-size:11.5px; background: #f1f5f9; padding: 2px 7px; border-radius: 4px; color: #475569;">' . e($item->url ?: '/') . '</span>';
        if ($item->target === '_blank') {
            echo '<span class="badge badge-secondary" style="font-size:10.5px; padding: 2px 6px;">_blank</span>';
        }
        echo '</div>';

        // Right actions
        $itemJson = htmlspecialchars(json_encode([
            'id' => $item->id,
            'menu_id' => $item->menu_id,
            'parent_id' => $item->parent_id,
            'title_vi' => $viTitle,
            'title_en' => $enTitle,
            'url' => $item->url,
            'target' => $item->target ?: '_self',
            'order' => $item->order ?? 0,
        ]), ENT_QUOTES, 'UTF-8');

        echo '<div class="item-actions dd-nodrag actions" style="display:flex; align-items:center; gap:8px; flex-shrink:0;">';
        echo '<button type="button" class="link-action" style="font-size:12px; font-weight:600;" onclick="openAddItemModal(' . $item->id . ')">+ Thêm con</button>';
        echo '<button type="button" class="link-action" style="font-size:12px; font-weight:600;" onclick="openEditItemModal(' . $itemJson . ')">Sửa</button>';
        echo '<form method="post" action="' . e(route('admin.menu-items.destroy', $item->id)) . '" style="display:inline; margin:0;">';
        echo csrf_field();
        echo method_field('delete');
        echo '<button type="submit" class="link-action link-danger" style="font-size:12px;" onclick="return confirm(\'Bạn có chắc chắn muốn xóa mục menu này không?\')">Xóa</button>';
        echo '</form>';
        echo '</div>';

        echo '</div>';
        echo '</div>';

        if ($children->isNotEmpty()) {
            echo '<ol class="dd-list">';
            foreach ($children as $child) {
                $renderMenuItem($child, $depth + 1);
            }
            echo '</ol>';
        }
        echo '</li>';
    };
@endphp

@section('content')
    <div class="page-header" style="margin-bottom: 20px;">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            <p class="page-description">Quản lý menu điều hướng và danh sách mục liên kết (menu items) trên website.</p>
        </div>
        <div class="actions">
            <button type="button" class="btn btn-primary" onclick="openCreateMenuModal()">+ Tạo Menu mới</button>
        </div>
    </div>

    @if (session('status'))
        <div class="status-message" style="margin-bottom: 18px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <div class="menu-manager-grid" style="display: grid; grid-template-columns: 310px 1fr; gap: 20px; align-items: start;">
        {{-- Left Column: Menus List --}}
        <div class="menu-sidebar-card card" style="padding: 0; overflow: hidden; margin-bottom: 0;">
            <div class="card-header" style="margin: 0; border-radius: 0; padding: 14px 18px; border-bottom: 1px solid var(--fi-border);">
                <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                    <h3 class="card-title" style="font-size: 13.5px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                        Danh sách Menu
                    </h3>
                    <span class="badge badge-secondary" style="font-size: 11px;">{{ $menus->count() }} menu</span>
                </div>
            </div>

            <div class="card-body" style="padding: 10px;">
                @if ($menus->isEmpty())
                    <div style="padding: 24px 16px; text-align: center; color: var(--fi-muted); font-size: 13px;">
                        Chưa có menu nào.
                    </div>
                @else
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        @foreach ($menus as $m)
                            @php
                                $isActive = $activeMenu && $activeMenu->id === $m->id;
                            @endphp
                            <a href="{{ route('admin.menus.index', ['menu_id' => $m->id]) }}" 
                               class="menu-nav-item {{ $isActive ? 'active' : '' }}" 
                               style="display: flex; align-items: center; justify-content: space-between; padding: 11px 14px; border-radius: 8px; text-decoration: none; border: 1px solid {{ $isActive ? 'var(--fi-primary)' : 'transparent' }}; background: {{ $isActive ? 'var(--fi-primary-soft)' : '#ffffff' }}; transition: all 0.15s ease;">
                                <div style="min-width: 0;">
                                    <div style="font-weight: {{ $isActive ? '700' : '600' }}; color: {{ $isActive ? 'var(--fi-primary)' : 'var(--fi-text)' }}; font-size: 13.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $m->name }}
                                    </div>
                                    <div class="mono" style="font-size: 11px; color: var(--fi-muted); margin-top: 2px;">
                                        handle: {{ $m->handle ?? $m->slug ?? 'n/a' }}
                                    </div>
                                </div>
                                <span class="badge {{ $isActive ? 'badge-primary' : 'badge-secondary' }}" style="font-size: 11px; flex-shrink: 0; margin-left: 8px;">
                                    {{ $m->items_count }} mục
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif

                <div style="margin-top: 12px; padding-top: 10px; border-top: 1px dashed var(--fi-border);">
                    <button type="button" class="btn btn-secondary btn-sm" style="width: 100%; justify-content: center;" onclick="openCreateMenuModal()">
                        + Thêm Menu mới
                    </button>
                </div>
            </div>
        </div>

        {{-- Right Column: Menu Items Management --}}
        <div class="menu-content-card card" style="padding: 0; overflow: hidden; margin-bottom: 0;">
            @if (!$activeMenu)
                <div style="padding: 60px 20px; text-align: center; color: var(--fi-muted);">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 12px; color: #94a3b8;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <h3 style="font-size: 15px; font-weight: 600; color: var(--fi-text); margin-bottom: 4px;">Chưa chọn Menu</h3>
                    <p style="font-size: 13px; margin-bottom: 16px;">Vui lòng chọn hoặc tạo mới một menu để bắt đầu quản lý các mục liên kết.</p>
                    <button type="button" class="btn btn-primary" onclick="openCreateMenuModal()">+ Tạo Menu đầu tiên</button>
                </div>
            @else
                {{-- Header of Active Menu --}}
                <div class="card-header" style="margin: 0; border-radius: 0; padding: 14px 20px; border-bottom: 1px solid var(--fi-border); background: #f8fafc; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <h2 style="font-size: 16px; font-weight: 700; color: var(--fi-text); margin: 0;">
                                {{ $activeMenu->name }}
                            </h2>
                            <code class="mono" style="font-size: 11.5px; background: #e2e8f0; color: #334155; padding: 2px 7px; border-radius: 4px;">
                                handle: {{ $activeMenu->handle ?? $activeMenu->slug }}
                            </code>
                        </div>
                        <div style="font-size: 12px; color: var(--fi-muted); margin-top: 3px;">
                            Tổng cộng {{ $flatItems->count() }} mục liên kết trong menu này
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        @php
                            $activeMenuJson = htmlspecialchars(json_encode([
                                'id' => $activeMenu->id,
                                'name' => $activeMenu->name,
                                'handle' => $activeMenu->handle ?? '',
                                'slug' => $activeMenu->slug ?? '',
                            ]), ENT_QUOTES, 'UTF-8');
                        @endphp
                        <button type="button" class="btn btn-secondary btn-sm" onclick="openEditMenuModal({{ $activeMenuJson }})">
                            ✏️ Đổi tên Menu
                        </button>
                        
                        <form method="post" action="{{ route('admin.menus.destroy', $activeMenu->id) }}" style="display:inline; margin:0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa menu \'{{ $activeMenu->name }}\' cùng tất cả các mục của nó không?');">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--fi-danger); border-color: var(--fi-danger-border);">
                                Xóa Menu
                            </button>
                        </form>

                        <button type="button" class="btn btn-primary btn-sm" onclick="openAddItemModal(null)">
                            + Thêm mục menu
                        </button>
                    </div>
                </div>

                {{-- Body of Active Menu --}}
                <div class="card-body" style="padding: 20px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; background: #f8fafc; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--fi-border);">
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: var(--fi-text-secondary);">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #64748b;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                            Kéo thả các thanh mục để sắp xếp thứ tự hoặc thụt lề để tạo mục cấp con đa tầng.
                        </div>
                        <button id="save-tree-btn" class="btn btn-primary btn-sm" style="display: none;">
                            💾 Lưu vị trí đã sắp xếp
                        </button>
                    </div>

                    @if ($menuItems->isEmpty())
                        <div style="padding: 48px 20px; text-align: center; border: 2px dashed var(--fi-border); border-radius: 8px; background: #fafafa;">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 10px; color: #94a3b8;"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                            <h4 style="font-size: 14px; font-weight: 600; color: var(--fi-text); margin-bottom: 4px;">Menu chưa có mục liên kết nào</h4>
                            <p style="font-size: 12.5px; color: var(--fi-muted); margin-bottom: 14px;">Bấm nút bên dưới để thêm liên kết đầu tiên vào menu này.</p>
                            <button type="button" class="btn btn-primary btn-sm" onclick="openAddItemModal(null)">+ Thêm mục menu</button>
                        </div>
                    @else
                        <div class="dd" id="nestable-tree">
                            <ol class="dd-list">
                                @foreach ($menuItems as $item)
                                    {!! $renderMenuItem($item, 0) !!}
                                @endforeach
                            </ol>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL 1: Create Menu --}}
    <div id="create-menu-modal" class="modal-backdrop" style="display: none;">
        <div class="card modal-dialog">
            <div class="card-header">
                <h3 class="card-title">Tạo Menu Mới</h3>
                <button type="button" class="modal-close" onclick="closeCreateMenuModal()">&times;</button>
            </div>
            <form method="post" action="{{ route('admin.menus.store') }}">
                @csrf
                <div style="padding: 16px 20px; display: grid; gap: 14px;">
                    <div>
                        <label class="form-label">Tên Menu <span style="color: var(--fi-danger);">*</span></label>
                        <input type="text" name="name" id="create-menu-name" class="form-control" placeholder="Ví dụ: Menu Header, Menu Footer, Mobile Menu..." required oninput="autoGenerateHandle(this.value, 'create-menu-handle')">
                    </div>
                    <div>
                        <label class="form-label">Mã định danh (Handle / Slug)</label>
                        <input type="text" name="handle" id="create-menu-handle" class="form-control mono" placeholder="Ví dụ: header-menu, footer-menu...">
                        <small style="font-size: 11.5px; color: var(--fi-muted);">Dùng để gọi menu trong code giao diện hoặc website.</small>
                    </div>
                </div>
                <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid var(--fi-border); display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateMenuModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu Menu</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2: Edit Menu --}}
    <div id="edit-menu-modal" class="modal-backdrop" style="display: none;">
        <div class="card modal-dialog">
            <div class="card-header">
                <h3 class="card-title">Chỉnh sửa Menu</h3>
                <button type="button" class="modal-close" onclick="closeEditMenuModal()">&times;</button>
            </div>
            <form id="edit-menu-form" method="post" action="">
                @csrf
                @method('put')
                <div style="padding: 16px 20px; display: grid; gap: 14px;">
                    <div>
                        <label class="form-label">Tên Menu <span style="color: var(--fi-danger);">*</span></label>
                        <input type="text" name="name" id="edit-menu-name" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">Mã định danh (Handle / Slug)</label>
                        <input type="text" name="handle" id="edit-menu-handle" class="form-control mono">
                        <small style="font-size: 11.5px; color: var(--fi-muted);">Dùng để gọi menu trong code giao diện hoặc website.</small>
                    </div>
                </div>
                <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid var(--fi-border); display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" class="btn btn-secondary" onclick="closeEditMenuModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật Menu</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 3: Add Menu Item --}}
    @if ($activeMenu)
    <div id="add-item-modal" class="modal-backdrop" style="display: none;">
        <div class="card modal-dialog" style="width: min(100%, 560px);">
            <div class="card-header">
                <h3 class="card-title">Thêm mục menu vào "{{ $activeMenu->name }}"</h3>
                <button type="button" class="modal-close" onclick="closeAddItemModal()">&times;</button>
            </div>
            <form method="post" action="{{ route('admin.menu-items.store') }}">
                @csrf
                <input type="hidden" name="menu_id" value="{{ $activeMenu->id }}">
                <input type="hidden" name="type" value="custom">

                <div style="padding: 16px 20px; display: grid; gap: 14px;">
                    {{-- Quick Pick Presets --}}
                    <div style="background: #f8fafc; border: 1px solid var(--fi-border); border-radius: 8px; padding: 12px;">
                        <div style="font-size: 12px; font-weight: 700; color: var(--fi-text); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                            <span>⚡ Chọn nhanh từ trang hoặc danh mục có sẵn:</span>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div>
                                <label style="font-size: 11px; color: var(--fi-muted); margin-bottom: 3px; display: block;">Trang tĩnh (Pages):</label>
                                <select class="form-control" style="font-size: 12px; height: 32px;" onchange="applyQuickPickPage(this, 'add')">
                                    <option value="">-- Chọn trang --</option>
                                    @foreach ($pages as $p)
                                        @php
                                            $pVi = is_array($p->title) ? ($p->title['vi'] ?? '') : $p->title;
                                            $pEn = is_array($p->title) ? ($p->title['en'] ?? '') : '';
                                            $pUrl = $p->slug === '/' ? '/' : '/pages/' . $p->slug;
                                        @endphp
                                        <option value="{{ $pUrl }}" data-vi="{{ $pVi }}" data-en="{{ $pEn }}">{{ $pVi }} ({{ $pUrl }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label style="font-size: 11px; color: var(--fi-muted); margin-bottom: 3px; display: block;">Danh mục sản phẩm:</label>
                                <select class="form-control" style="font-size: 12px; height: 32px;" onchange="applyQuickPickCategory(this, 'add')">
                                    <option value="">-- Chọn danh mục --</option>
                                    @foreach ($categories as $c)
                                        @php
                                            $cName = is_array($c->name) ? ($c->name['vi'] ?? '') : $c->name;
                                            $cUrl = '/shop?category=' . $c->slug;
                                        @endphp
                                        <option value="{{ $cUrl }}" data-name="{{ $cName }}">{{ $cName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Titles --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label class="form-label">Tiêu đề (Tiếng Việt) <span style="color: var(--fi-danger);">*</span></label>
                            <input type="text" name="title[vi]" id="add-item-title-vi" class="form-control" placeholder="Ví dụ: Trang chủ, Cửa hàng..." required>
                        </div>
                        <div>
                            <label class="form-label">Tiêu đề (Tiếng Anh)</label>
                            <input type="text" name="title[en]" id="add-item-title-en" class="form-control" placeholder="Ví dụ: Home, Shop...">
                        </div>
                    </div>

                    {{-- URL --}}
                    <div>
                        <label class="form-label">Đường dẫn URL / Liên kết <span style="color: var(--fi-danger);">*</span></label>
                        <input type="text" name="url" id="add-item-url" class="form-control mono" placeholder="Ví dụ: /shop, /pages/about hoặc https://..." required>
                    </div>

                    {{-- Parent and Target --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label class="form-label">Mục cha (Phân cấp)</label>
                            <select name="parent_id" id="add-item-parent-id" class="form-control">
                                <option value="">-- Gốc (Cấp cao nhất) --</option>
                                @foreach ($flatItems as $fi)
                                    <option value="{{ $fi->id }}">{{ $fi->label }} (#{{ $fi->id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Chế độ mở liên kết</label>
                            <select name="target" class="form-control">
                                <option value="_self">Cùng tab hiện tại (_self)</option>
                                <option value="_blank">Mở trong tab mới (_blank)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid var(--fi-border); display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" class="btn btn-secondary" onclick="closeAddItemModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">+ Thêm mục</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 4: Edit Menu Item --}}
    <div id="edit-item-modal" class="modal-backdrop" style="display: none;">
        <div class="card modal-dialog" style="width: min(100%, 560px);">
            <div class="card-header">
                <h3 class="card-title">Chỉnh sửa mục Menu</h3>
                <button type="button" class="modal-close" onclick="closeEditItemModal()">&times;</button>
            </div>
            <form id="edit-item-form" method="post" action="">
                @csrf
                @method('put')
                <input type="hidden" name="menu_id" value="{{ $activeMenu->id }}">
                <input type="hidden" name="type" value="custom">

                <div style="padding: 16px 20px; display: grid; gap: 14px;">
                    {{-- Titles --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label class="form-label">Tiêu đề (Tiếng Việt) <span style="color: var(--fi-danger);">*</span></label>
                            <input type="text" name="title[vi]" id="edit-item-title-vi" class="form-control" required>
                        </div>
                        <div>
                            <label class="form-label">Tiêu đề (Tiếng Anh)</label>
                            <input type="text" name="title[en]" id="edit-item-title-en" class="form-control">
                        </div>
                    </div>

                    {{-- URL --}}
                    <div>
                        <label class="form-label">Đường dẫn URL / Liên kết <span style="color: var(--fi-danger);">*</span></label>
                        <input type="text" name="url" id="edit-item-url" class="form-control mono" required>
                    </div>

                    {{-- Parent and Target --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label class="form-label">Mục cha (Phân cấp)</label>
                            <select name="parent_id" id="edit-item-parent-id" class="form-control">
                                <option value="">-- Gốc (Cấp cao nhất) --</option>
                                @foreach ($flatItems as $fi)
                                    <option value="{{ $fi->id }}">{{ $fi->label }} (#{{ $fi->id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Chế độ mở liên kết</label>
                            <select name="target" id="edit-item-target" class="form-control">
                                <option value="_self">Cùng tab hiện tại (_self)</option>
                                <option value="_blank">Mở trong tab mới (_blank)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid var(--fi-border); display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" class="btn btn-secondary" onclick="closeEditItemModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Nestable & Modal CSS --}}
    <style>
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(9, 30, 52, 0.5);
            backdrop-filter: blur(2px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .modal-dialog {
            width: min(100%, 460px);
            margin: 0;
            box-shadow: var(--fi-shadow-md);
            animation: modalFadeIn 0.15s ease-out;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.97); }
            to { opacity: 1; transform: scale(1); }
        }
        .modal-close {
            background: transparent;
            border: 0;
            font-size: 20px;
            line-height: 1;
            color: var(--fi-muted);
            cursor: pointer;
            padding: 0 4px;
        }
        .modal-close:hover { color: var(--fi-text); }
        .form-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--fi-text);
            margin-bottom: 5px;
        }

        /* Nestable CSS */
        .dd { position: relative; display: block; margin: 0; padding: 0; max-width: 100%; list-style: none; font-size: 13px; line-height: 20px; }
        .dd-list { display: block; position: relative; margin: 0; padding: 0; list-style: none; }
        .dd-list .dd-list { padding-left: 28px; }
        .dd-item, .dd-empty, .dd-placeholder { display: block; position: relative; margin: 0; padding: 0; min-height: 20px; }
        .dd-handle { display: block; min-height: 42px; margin: 7px 0; padding: 9px 14px; color: var(--fi-text); font-weight: 500; border: 1px solid var(--fi-border); background: #ffffff; border-radius: 8px; cursor: move; transition: border-color 0.14s ease, background 0.14s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.03); }
        .dd-handle:hover { background: #f8fafc; border-color: var(--fi-border-strong); }
        .dd-item > button { position: relative; cursor: pointer; float: left; width: 24px; height: 28px; margin: 7px 0; padding: 0; text-indent: 100%; white-space: nowrap; overflow: hidden; border: 0; background: transparent; font-size: 15px; line-height: 1; text-align: center; font-weight: bold; }
        .dd-item > button:before { content: '+'; display: block; position: absolute; width: 100%; text-align: center; text-indent: 0; color: var(--fi-muted); margin-top: 7px; }
        .dd-item > button[data-action="collapse"]:before { content: '−'; }
        .dd-placeholder, .dd-empty { margin: 7px 0; padding: 0; min-height: 42px; background: #f8fafc; border: 1px dashed var(--fi-border-strong); border-radius: 8px; }
        .dd-dragel { position: absolute; pointer-events: none; z-index: 9999; }
        .dd-dragel > .dd-item .dd-handle { margin-top: 0; opacity: 0.95; box-shadow: var(--fi-shadow-md); }
        .item-actions { pointer-events: auto; }

        @media (max-width: 900px) {
            .menu-manager-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.js"></script>
    <script>
        function autoGenerateHandle(val, targetId) {
            const target = document.getElementById(targetId);
            if (!target) return;
            const slug = val.toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            target.value = slug;
        }

        function openCreateMenuModal() {
            document.getElementById('create-menu-modal').style.display = 'flex';
            setTimeout(() => document.getElementById('create-menu-name').focus(), 50);
        }
        function closeCreateMenuModal() {
            document.getElementById('create-menu-modal').style.display = 'none';
        }

        function openEditMenuModal(menu) {
            const form = document.getElementById('edit-menu-form');
            form.action = "{{ url('admin/menus') }}/" + menu.id;
            document.getElementById('edit-menu-name').value = menu.name || '';
            document.getElementById('edit-menu-handle').value = menu.handle || menu.slug || '';
            document.getElementById('edit-menu-modal').style.display = 'flex';
            setTimeout(() => document.getElementById('edit-menu-name').focus(), 50);
        }
        function closeEditMenuModal() {
            document.getElementById('edit-menu-modal').style.display = 'none';
        }

        function openAddItemModal(parentId) {
            const modal = document.getElementById('add-item-modal');
            if (!modal) return;
            const parentSelect = document.getElementById('add-item-parent-id');
            if (parentSelect) {
                parentSelect.value = parentId ? String(parentId) : '';
            }
            modal.style.display = 'flex';
            setTimeout(() => document.getElementById('add-item-title-vi').focus(), 50);
        }
        function closeAddItemModal() {
            const modal = document.getElementById('add-item-modal');
            if (modal) modal.style.display = 'none';
        }

        function openEditItemModal(item) {
            const modal = document.getElementById('edit-item-modal');
            if (!modal) return;
            const form = document.getElementById('edit-item-form');
            form.action = "{{ url('admin/menu-items') }}/" + item.id;
            document.getElementById('edit-item-title-vi').value = item.title_vi || '';
            document.getElementById('edit-item-title-en').value = item.title_en || '';
            document.getElementById('edit-item-url').value = item.url || '';
            document.getElementById('edit-item-parent-id').value = item.parent_id ? String(item.parent_id) : '';
            document.getElementById('edit-item-target').value = item.target || '_self';
            modal.style.display = 'flex';
            setTimeout(() => document.getElementById('edit-item-title-vi').focus(), 50);
        }
        function closeEditItemModal() {
            const modal = document.getElementById('edit-item-modal');
            if (modal) modal.style.display = 'none';
        }

        function applyQuickPickPage(select, prefix) {
            const opt = select.options[select.selectedIndex];
            if (!opt.value) return;
            const vi = opt.getAttribute('data-vi') || '';
            const en = opt.getAttribute('data-en') || '';
            document.getElementById(prefix + '-item-title-vi').value = vi;
            if (en) {
                document.getElementById(prefix + '-item-title-en').value = en;
            }
            document.getElementById(prefix + '-item-url').value = opt.value;
        }

        function applyQuickPickCategory(select, prefix) {
            const opt = select.options[select.selectedIndex];
            if (!opt.value) return;
            const name = opt.getAttribute('data-name') || '';
            document.getElementById(prefix + '-item-title-vi').value = name;
            document.getElementById(prefix + '-item-url').value = opt.value;
        }

        // Close on backdrop click
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-backdrop')) {
                e.target.style.display = 'none';
            }
        });

        // Initialize Nestable Tree
        $(document).ready(function() {
            var $tree = $('#nestable-tree');
            var $saveBtn = $('#save-tree-btn');

            if ($tree.length) {
                $tree.nestable({
                    group: 1,
                    maxDepth: 5
                }).on('change', function() {
                    $saveBtn.show();
                });

                $saveBtn.on('click', function() {
                    var data = $tree.nestable('serialize');
                    var btn = $(this);
                    btn.text('Đang lưu...').prop('disabled', true);

                    $.ajax({
                        url: '{{ route('admin.menu-items.reorder') }}',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            tree: data,
                            menu_id: {{ $activeMenu ? $activeMenu->id : 'null' }}
                        },
                        success: function() {
                            btn.text('✓ Đã lưu thứ tự').css('background-color', '#059669');
                            setTimeout(() => {
                                btn.text('💾 Lưu vị trí đã sắp xếp').css('background-color', '').hide();
                                btn.prop('disabled', false);
                            }, 2000);
                        },
                        error: function() {
                            alert('Có lỗi xảy ra khi lưu thứ tự menu items.');
                            btn.text('💾 Lưu vị trí đã sắp xếp').prop('disabled', false);
                        }
                    });
                });
            }
        });
    </script>
@endsection
