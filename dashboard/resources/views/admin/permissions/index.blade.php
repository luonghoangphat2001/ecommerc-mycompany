@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            <p class="page-description">Ma trận kiểm soát quyền truy cập (Access Control List), đối chiếu quyền thực thi và vai trò người dùng.</p>
        </div>
        <div class="actions">
            <a class="btn btn-secondary" href="{{ route('admin.roles.index') }}">
                <span>{{ __('admin.sidebar.roles') }}</span>
            </a>
            <a class="btn btn-primary" href="{{ route($routePrefix . '.create') }}">
                <span>{{ __('admin.actions.create') }}</span>
            </a>
        </div>
    </div>

    <div class="perm-matrix-wrapper">
        {{-- Toolbar: Search + Role Audit Selector + Live Telemetry --}}
        <div class="perm-top-bar">
            <div class="perm-search-box">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input id="perm-search-input" class="perm-search-input" type="search" placeholder="Tìm theo phân hệ, quyền hạn... (nhấn '/' để tìm)" autocomplete="off">
                <kbd class="perm-kbd">/</kbd>
            </div>

            <div class="perm-filter-controls">
                <select id="perm-role-filter" class="perm-role-select" title="Lọc và kiểm tra quyền theo vai trò">
                    <option value="">-- Tất cả vai trò (Audit) --</option>
                    @foreach ($roles as $r)
                        <option value="{{ $r->name }}">{{ \App\Support\AdminLabel::role($r->name) }} ({{ $r->name }})</option>
                    @endforeach
                </select>

                <div class="perm-telemetry-badge">
                    <span class="perm-telemetry-dot"></span>
                    <span>{{ $stats['total_permissions'] }} quyền &bull; {{ $stats['total_modules'] }} phân hệ &bull; Guard: {{ $stats['guard'] }}</span>
                </div>
            </div>
        </div>

        {{-- Matrix Container with Domain Tabs Strip & Dense Data Table --}}
        <div class="perm-matrix-container">
            <div class="perm-tabs-strip" id="perm-tabs" role="tablist">
                <button type="button" class="perm-tab-btn active" data-category="all" role="tab">
                    <span>Tất cả</span>
                    <span class="perm-tab-counter">{{ count($modules) }}</span>
                </button>
                @foreach ($categories as $catKey => $catInfo)
                    @php
                        $cCount = $categoryCounts[$catKey] ?? 0;
                    @endphp
                    @if ($cCount > 0)
                        <button type="button" class="perm-tab-btn" data-category="{{ $catKey }}" role="tab">
                            <span>{{ $catInfo['label'] }}</span>
                            <span class="perm-tab-counter">{{ $cCount }}</span>
                        </button>
                    @endif
                @endforeach
            </div>

            <div style="overflow-x: auto;">
                <table class="perm-matrix-table" id="perm-matrix-table">
                    <thead>
                        <tr>
                            <th style="min-width: 240px;">Phân hệ chức năng</th>
                            <th style="width: 110px;">Xem (Read)</th>
                            <th style="width: 110px;">Tạo (Create)</th>
                            <th style="width: 110px;">Sửa (Update)</th>
                            <th style="width: 110px;">Xoá (Delete)</th>
                            <th style="min-width: 200px;">Vai trò áp dụng</th>
                            <th style="width: 110px; text-align: right;">Cấu hình</th>
                        </tr>
                    </thead>
                    <tbody id="perm-matrix-body">
                        @foreach ($modules as $mod)
                            <tr class="perm-row"
                                data-category="{{ $mod['category'] }}"
                                data-roles="{{ implode(',', $mod['roles']) }}"
                                data-search="{{ strtolower($mod['name'] . ' ' . $mod['slug'] . ' ' . implode(' ', array_column($mod['all_permissions'], 'name'))) }}">
                                
                                {{-- Phân hệ / Chức năng --}}
                                <td>
                                    <div class="perm-module-cell">
                                        <span class="perm-module-name">{{ $mod['name'] }}</span>
                                        <div class="perm-module-sub">
                                            <code class="perm-slug-code">{{ $mod['slug'] }}</code>
                                            <span class="perm-action-count">{{ count($mod['all_permissions']) }} quyền</span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Xem (Read) --}}
                                <td>
                                    @if (!empty($mod['actions']['view']))
                                        @foreach ($mod['actions']['view'] as $p)
                                            <span class="acl-badge" data-perm-roles="{{ implode(',', $p['roles']) }}" title="{{ $p['name'] }}">{{ $p['action'] }}</span>
                                        @endforeach
                                    @else
                                        <span class="acl-empty-dash">&mdash;</span>
                                    @endif
                                </td>

                                {{-- Tạo (Create) --}}
                                <td>
                                    @if (!empty($mod['actions']['create']))
                                        @foreach ($mod['actions']['create'] as $p)
                                            <span class="acl-badge" data-perm-roles="{{ implode(',', $p['roles']) }}" title="{{ $p['name'] }}">{{ $p['action'] }}</span>
                                        @endforeach
                                    @else
                                        <span class="acl-empty-dash">&mdash;</span>
                                    @endif
                                </td>

                                {{-- Sửa (Update) --}}
                                <td>
                                    @if (!empty($mod['actions']['update']))
                                        @foreach ($mod['actions']['update'] as $p)
                                            <span class="acl-badge" data-perm-roles="{{ implode(',', $p['roles']) }}" title="{{ $p['name'] }}">{{ $p['action'] }}</span>
                                        @endforeach
                                    @else
                                        <span class="acl-empty-dash">&mdash;</span>
                                    @endif
                                </td>

                                {{-- Xoá (Delete) --}}
                                <td>
                                    @if (!empty($mod['actions']['delete']))
                                        @foreach ($mod['actions']['delete'] as $p)
                                            <span class="acl-badge" data-perm-roles="{{ implode(',', $p['roles']) }}" title="{{ $p['name'] }}">{{ $p['action'] }}</span>
                                        @endforeach
                                    @else
                                        <span class="acl-empty-dash">&mdash;</span>
                                    @endif
                                </td>

                                {{-- Vai trò áp dụng --}}
                                <td>
                                    @if (!empty($mod['roles']))
                                        <div class="acl-roles-group">
                                            @foreach ($mod['roles'] as $r)
                                                <span class="acl-role-pill {{ $r === 'super_admin' ? 'super' : '' }}">{{ \App\Support\AdminLabel::role($r) }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="acl-empty-dash" style="font-size: 11.5px;">Chưa gán</span>
                                    @endif
                                </td>

                                {{-- Thao tác --}}
                                <td style="text-align: right;">
                                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-sm" style="padding: 4px 10px; font-size: 12px; white-space: nowrap;" title="Phân quyền chi tiết cho vai trò">
                                        <span>Phân quyền &rarr;</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach

                        <tr id="perm-empty-row" class="perm-empty-row" style="display: none;">
                            <td colspan="7">
                                <div style="font-weight: 600; font-size: 14px; color: var(--fi-text);">Không tìm thấy phân hệ nào phù hợp</div>
                                <div style="font-size: 12.5px; color: var(--fi-muted); margin-top: 4px;">Vui lòng thử tìm kiếm với từ khóa khác hoặc chuyển phân hệ bên trên.</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function() {
                const searchInput = document.getElementById('perm-search-input');
                const roleSelect = document.getElementById('perm-role-filter');
                const tabButtons = document.querySelectorAll('#perm-tabs .perm-tab-btn');
                const rows = document.querySelectorAll('#perm-matrix-body .perm-row');
                const emptyRow = document.getElementById('perm-empty-row');
                const badges = document.querySelectorAll('#perm-matrix-body .acl-badge');

                let currentCategory = 'all';
                let currentQuery = '';
                let currentRole = '';

                function filterMatrix() {
                    let visibleCount = 0;

                    rows.forEach(row => {
                        const rowCategory = row.dataset.category;
                        const rowSearch = row.dataset.search || '';
                        const rowRoles = (row.dataset.roles || '').split(',').map(s => s.trim()).filter(Boolean);

                        const matchesCategory = (currentCategory === 'all' || rowCategory === currentCategory);
                        const matchesSearch = !currentQuery || rowSearch.includes(currentQuery);

                        let matchesRole = true;
                        if (currentRole) {
                            matchesRole = rowRoles.includes(currentRole);
                        }

                        if (matchesCategory && matchesSearch && matchesRole) {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    if (emptyRow) {
                        emptyRow.style.display = visibleCount === 0 ? '' : 'none';
                    }

                    // Audit highlighting on badges when role is chosen
                    badges.forEach(badge => {
                        const permRoles = (badge.dataset.permRoles || '').split(',').map(s => s.trim()).filter(Boolean);
                        badge.classList.remove('audit-has-role', 'audit-no-role');

                        if (currentRole) {
                            if (permRoles.includes(currentRole)) {
                                badge.classList.add('audit-has-role');
                            } else {
                                badge.classList.add('audit-no-role');
                            }
                        }
                    });
                }

                if (searchInput) {
                    searchInput.addEventListener('input', function(e) {
                        currentQuery = e.target.value.trim().toLowerCase();
                        filterMatrix();
                    });
                }

                if (roleSelect) {
                    roleSelect.addEventListener('change', function(e) {
                        currentRole = e.target.value.trim();
                        filterMatrix();
                    });
                }

                tabButtons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        tabButtons.forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                        currentCategory = this.dataset.category || 'all';
                        filterMatrix();
                    });
                });

                // Keyboard shortcut: '/' to focus search
                window.addEventListener('keydown', function(e) {
                    if (e.key === '/' && document.activeElement !== searchInput) {
                        e.preventDefault();
                        if (searchInput) {
                            searchInput.focus();
                            searchInput.select();
                        }
                    }
                });
            })();
        </script>
    @endpush
@endsection
