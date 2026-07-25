@php
    $navGroups = [
        [
            'label' => 'admin.sidebar.group.dashboard',
            'items' => [
                ['label' => 'admin.sidebar.dashboard', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard'],
            ],
        ],
        [
            'label' => 'admin.sidebar.group.products',
            'items' => [
                ['label' => 'admin.sidebar.products', 'route' => 'admin.products.index', 'match' => 'admin.products.*'],
                ['label' => 'admin.sidebar.product_categories', 'route' => 'admin.product-categories.index', 'match' => 'admin.product-categories.*'],
                ['label' => 'admin.sidebar.brands', 'route' => 'admin.brands.index', 'match' => 'admin.brands.*'],
            ],
        ],
        [
            'label' => 'admin.sidebar.group.orders',
            'items' => [
                ['label' => 'admin.sidebar.orders', 'route' => 'admin.orders.index', 'match' => 'admin.orders.*'],
                ['label' => 'admin.sidebar.refunds', 'route' => 'admin.refunds.index', 'match' => 'admin.refunds.*'],
            ],
        ],
        [
            'label' => 'admin.sidebar.group.customers',
            'items' => [
                ['label' => 'admin.sidebar.users', 'route' => 'admin.users.index', 'match' => 'admin.users.*'],
            ],
        ],
        [
            'label' => 'admin.sidebar.group.content',
            'items' => [
                ['label' => 'admin.sidebar.posts', 'route' => 'admin.posts.index', 'match' => 'admin.posts.*'],
                ['label' => 'admin.sidebar.post_categories', 'route' => 'admin.post-categories.index', 'match' => 'admin.post-categories.*'],
                ['label' => 'admin.sidebar.pages', 'route' => 'admin.pages.index', 'match' => 'admin.pages.*'],
                ['label' => 'admin.sidebar.menus', 'route' => 'admin.menus.index', 'match' => 'admin.menus.*'],
                ['label' => 'admin.sidebar.menu_items', 'route' => 'admin.menu-items.index', 'match' => 'admin.menu-items.*'],
                ['label' => 'admin.sidebar.comments', 'route' => 'admin.comments.index', 'match' => 'admin.comments.*'],
                ['label' => 'admin.sidebar.media', 'route' => 'admin.media.index', 'match' => 'admin.media.*'],
            ],
        ],
        [
            'label' => 'department.sidebar.title',
            'items' => \Illuminate\Support\Facades\Cache::remember('workspace_sidebar_items', 3600, function () {
                $items = [];
                $departments = \App\Models\Department::where('is_active', true)->get();
                foreach ($departments as $dept) {
                    $items[] = [
                        'label' => __("workspace.sidebar.{$dept->code}") === "workspace.sidebar.{$dept->code}" ? $dept->name : "workspace.sidebar.{$dept->code}",
                        'route' => 'admin.workspace.show',
                        'query' => ['code' => $dept->code],
                        'match' => 'admin.workspace.show',
                        'skip_permission' => true,
                    ];
                }
                return $items;
            }),
        ],
        [
            'label' => 'admin.sidebar.group.settings',
            'items' => [
                ['label' => 'admin.sidebar.general_settings', 'route' => 'admin.settings.index', 'match' => 'admin.settings.*', 'query' => ['tab' => 'settings']]
            ],
        ],
    ];

    $marketingSettings = app(\App\Settings\MarketingSettings::class);
    $marketingItems = [];
    if ($marketingSettings->upsell_enabled) {
        $marketingItems[] = ['label' => 'Upsell Products', 'route' => 'admin.upsell-products.index', 'match' => 'admin.upsell-products.*'];
    }
    if ($marketingSettings->cross_sell_enabled) {
        $marketingItems[] = ['label' => 'Cross-sell Products', 'route' => 'admin.cross-sell-products.index', 'match' => 'admin.cross-sell-products.*'];
    }
    if ($marketingSettings->combo_enabled) {
        $marketingItems[] = ['label' => 'Combo Products', 'route' => 'admin.combo-products.index', 'match' => 'admin.combo-products.*'];
    }
    if ($marketingSettings->loyalty_enabled) {
        $marketingItems[] = ['label' => 'Điểm Loyalty', 'route' => 'admin.loyalty-points.index', 'match' => 'admin.loyalty-points.*'];
    }
    if ($marketingSettings->enable_coupons) {
        $marketingItems[] = ['label' => 'Coupons', 'route' => 'admin.coupons.index', 'match' => 'admin.coupons.*'];
    }
    if (!empty($marketingItems)) {
        array_splice($navGroups, count($navGroups) - 1, 0, [
            [
                'label' => 'Marketing',
                'items' => $marketingItems,
            ]
        ]);
    }

    if (auth()->check() && !auth()->user()->hasRole('super_admin')) {
        foreach ($navGroups as $groupIndex => &$group) {
            foreach ($group['items'] as $itemIndex => &$item) {
                if (isset($item['children'])) {
                    foreach ($item['children'] as $childIndex => $child) {
                        $module = str_replace(['admin.', '.*'], '', $child['match']);
                        try {
                            if (!auth()->user()->hasPermissionTo("view_{$module}")) {
                                unset($item['children'][$childIndex]);
                            }
                        } catch (\Exception $e) {
                            unset($item['children'][$childIndex]);
                        }
                    }
                    if (empty($item['children'])) {
                        unset($group['items'][$itemIndex]);
                    }
                } else {
                    if (!($item['skip_permission'] ?? false)) {
                        $module = str_replace(['admin.', '.*'], '', $item['match']);
                        try {
                            if (!auth()->user()->hasPermissionTo("view_{$module}")) {
                                unset($group['items'][$itemIndex]);
                            }
                        } catch (\Exception $e) {
                            unset($group['items'][$itemIndex]);
                        }
                    }
                }
            }
            if (empty($group['items'])) {
                unset($navGroups[$groupIndex]);
            }
        }
        unset($group);
        unset($item);
    }

    $navUrl = function (array $item): string {
        return isset($item['query'])
            ? route($item['route'], $item['query'])
            : route($item['route']);
    };

    $isItemActive = function (array $item): bool {
        if (!request()->routeIs($item['match'])) return false;
        if (isset($item['query'])) {
            foreach ($item['query'] as $k => $v) {
                if (request()->route($k) !== $v && request()->query($k) !== $v) return false;
            }
        }
        return true;
    };
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? __('admin.brand.title') }}</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --fi-bg: #f8fafc;
            --fi-panel: #ffffff;
            --fi-soft: #f1f5f9;
            --fi-border: #e2e8f0;
            --fi-text: #0f172a;
            --fi-muted: #64748b;
            --fi-primary: {{ app(\App\Settings\DBSettings::class)->primary_color ?? '#d97706' }};
            --fi-primary-strong: color-mix(in srgb, var(--fi-primary) 80%, black);
            --fi-danger: #dc2626;
            --fi-success: #047857;
            --fi-ring: 0 0 0 1px rgba(15, 23, 42, .06), 0 12px 28px rgba(15, 23, 42, .08);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--fi-bg);
            color: var(--fi-text);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 14px;
            line-height: 1.5;
        }
        a { color: inherit; }
        .admin-shell { min-height: 100vh; display: flex; }
        .sidebar {
            width: 292px;
            flex: 0 0 292px;
            background: var(--fi-panel);
            border-right: 1px solid var(--fi-border);
            padding: 18px 14px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 8px 18px;
            border-bottom: 1px solid var(--fi-border);
            margin-bottom: 16px;
        }
        .brand-mark {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: #111827;
            color: #fff;
            font-weight: 800;
        }
        .brand-title { font-weight: 700; }
        .brand-subtitle { color: var(--fi-muted); font-size: 12px; margin-top: 1px; }
        .nav-group { margin: 16px 0; }
        .nav-heading {
            color: #94a3b8;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            padding: 0 10px 7px;
        }
        .nav-section {
            margin: 6px 0 10px;
            border-radius: 10px;
        }
        .nav-summary {
            list-style: none;
            cursor: pointer;
            user-select: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            min-height: 38px;
            padding: 8px 10px;
            border-radius: 8px;
            color: #334155;
            font-weight: 700;
        }
        .nav-summary::-webkit-details-marker { display: none; }
        .nav-summary:hover { background: var(--fi-soft); color: var(--fi-text); }
        .nav-caret {
            width: 8px;
            height: 8px;
            border-right: 2px solid #94a3b8;
            border-bottom: 2px solid #94a3b8;
            transform: rotate(45deg);
            transition: transform .15s ease, border-color .15s ease;
            flex: 0 0 auto;
            margin-right: 2px;
        }
        .nav-section[open] > .nav-summary .nav-caret {
            transform: rotate(225deg);
            border-color: var(--fi-primary);
        }
        .nav-link {
            min-height: 36px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 8px;
            text-decoration: none;
            color: #334155;
            font-weight: 600;
        }
        .nav-link:hover { background: var(--fi-soft); color: var(--fi-text); }
        .nav-link.active {
            background: color-mix(in srgb, var(--fi-primary) 10%, transparent);
            color: var(--fi-primary-strong);
            box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--fi-primary) 30%, transparent);
        }
        .nav-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #cbd5e1;
            flex: 0 0 auto;
        }
        .nav-link.active .nav-dot { background: var(--fi-primary); }
        .nav-subgroup { margin: 4px 0 10px; }
        .nav-subheading {
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 10px 6px;
        }
        .nav-subgroup.active .nav-subheading { color: #0f172a; }
        .nav-subitems { padding-left: 8px; }
        .nav-link-child {
            padding-left: 18px;
            font-size: 13px;
            font-weight: 600;
        }
        .main { min-width: 0; flex: 1; display: flex; flex-direction: column; }
        .topbar {
            min-height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 28px;
            background: rgba(248, 250, 252, .9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--fi-border);
            position: sticky;
            top: 0;
            z-index: 5;
        }
        .topbar-title { font-weight: 700; }
        .topbar-meta { color: var(--fi-muted); font-size: 13px; }
        .content { width: 100%; max-width: 1440px; padding: 28px; margin: 0 auto; }
        .card, .table-panel {
            background: var(--fi-panel);
            border: 1px solid var(--fi-border);
            border-radius: 8px;
            box-shadow: var(--fi-ring);
        }
        .card { padding: 22px; }
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }
        .page-title { margin: 0; font-size: 24px; line-height: 1.25; letter-spacing: 0; }
        .page-description { margin: 6px 0 0; color: var(--fi-muted); }
        .actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .btn {
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid transparent;
            border-radius: 8px;
            padding: 8px 13px;
            background: var(--fi-primary);
            color: #fff;
            cursor: pointer;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            line-height: 1;
        }
        .btn:hover { background: var(--fi-primary-strong); }
        .btn-secondary { background: #fff; color: #334155; border-color: var(--fi-border); }
        .btn-secondary:hover { background: var(--fi-soft); color: var(--fi-text); }
        .btn-danger { background: var(--fi-danger); color: #fff; }
        .btn-danger:hover { background: #b91c1c; }
        .link-action {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 5px 8px;
            border-radius: 7px;
            color: #475569;
            font-weight: 700;
            text-decoration: none;
            border: 0;
            background: transparent;
            cursor: pointer;
        }
        .link-action:hover { background: var(--fi-soft); color: var(--fi-text); }
        .link-danger { color: var(--fi-danger); }
        .searchbar { margin: 0 0 14px; position: relative; }
        .toolbar-row {
            display: grid;
            grid-template-columns: minmax(260px, 1fr) auto;
            gap: 12px;
            align-items: start;
            margin-bottom: 14px;
        }
        .toolbar-row .searchbar { margin: 0; }
        .list-filter-panel {
            background: #f8fafc;
            border-bottom: 1px solid var(--fi-border);
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -22px -22px 22px -22px;
        }
        .list-filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        .list-filter-grid label {
            display: block;
            margin-bottom: 6px;
            color: var(--fi-muted);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
        }
        .list-filter-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            align-items: center;
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 16px;
        }
        .list-filter-actions .btn {
            min-height: 38px;
            padding: 8px 24px;
            font-weight: 600;
        }
        .list-filter-actions .btn-secondary {
            padding-left: 16px;
            padding-right: 16px;
            color: #475569;
        }
        .crud-import-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 16px;
        }
        .workspace-filter-card {
            padding-bottom: 0;
            margin-bottom: 16px;
        }
        .import-form {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
        }
        .import-form input[type="file"] {
            width: min(100%, 260px);
            min-height: 36px;
            padding: 6px 8px;
            color: var(--fi-muted);
        }
        .workspace-import-card {
            margin-bottom: 16px;
            padding: 14px;
        }
        .workspace-import-card .import-form {
            justify-content: flex-start;
        }
        .workspace-import-card select {
            width: min(100%, 240px);
        }
        .import-error { margin: 0 0 12px; }
        .searchbar input { padding-left: 38px; }
        .searchbar::before {
            content: "";
            width: 14px;
            height: 14px;
            border: 2px solid #94a3b8;
            border-radius: 999px;
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
        }
        .searchbar::after {
            content: "";
            width: 7px;
            height: 2px;
            background: #94a3b8;
            position: absolute;
            left: 25px;
            top: 24px;
            transform: rotate(45deg);
        }
        label { display: block; color: #334155; font-weight: 700; margin-bottom: 6px; }
        input, select, textarea {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
            color: var(--fi-text);
            padding: 9px 11px;
            font: inherit;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--fi-primary);
            box-shadow: 0 0 0 3px rgba(217, 119, 6, .14);
        }
        textarea { min-height: 120px; resize: vertical; }
        .form-grid { display: grid; gap: 16px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .form-row { margin-bottom: 0; }
        .form-row-wide { grid-column: 1 / -1; }
        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 10px;
            border: 1px solid var(--fi-border);
            border-radius: 10px;
            background: #f8fafc;
            padding: 12px;
            max-height: 420px;
            overflow: auto;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 9px;
            margin: 0;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
            color: #334155;
            padding: 9px 10px;
            font-weight: 700;
        }
        .checkbox-item input {
            width: 16px;
            height: 16px;
            flex: 0 0 auto;
            accent-color: var(--fi-primary);
        }
        .form-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            border-top: 1px solid var(--fi-border);
            margin-top: 20px;
            padding-top: 18px;
        }
        .error { color: var(--fi-danger); font-size: 13px; margin-top: 6px; }
        .status-message {
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: var(--fi-success);
            padding: 10px 12px;
            border-radius: 8px;
            margin: 0 0 14px;
            font-weight: 700;
        }
        .table-wrap { overflow: auto; border-radius: 8px; border: 1px solid var(--fi-border); }
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .04em;
            font-weight: 800;
            text-align: left;
            border-bottom: 1px solid var(--fi-border);
            padding: 10px 12px;
            white-space: nowrap;
        }
        tbody td, tbody th {
            border-bottom: 1px solid #edf2f7;
            padding: 11px 12px;
            vertical-align: top;
        }
        tbody tr:last-child td, tbody tr:last-child th { border-bottom: 0; }
        tbody tr:hover td { background: #ffffbf; }
        .cell-muted { color: var(--fi-muted); }
        .details-table th { width: 240px; color: #475569; background: #f8fafc; }
        .details-table td { white-space: pre-wrap; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-top: 18px; }
        .stat {
            border: 1px solid var(--fi-border);
            border-radius: 8px;
            background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
            padding: 18px;
        }
        .label { color: var(--fi-muted); font-size: 13px; font-weight: 700; }
        .value { font-size: 30px; line-height: 1.1; font-weight: 800; margin-top: 8px; }
        .dashboard-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(280px, .8fr);
            gap: 16px;
            margin-top: 16px;
        }
        .metric-list { display: grid; gap: 10px; }
        .metric-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border: 1px solid var(--fi-border);
            border-radius: 8px;
            padding: 10px 12px;
            background: #fff;
        }
        .metric-row strong { font-size: 18px; }
        .login-wrap { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        .login-card { width: min(100%, 430px); }
        .section-title { margin: 22px 0 10px; font-size: 16px; }
        .stack { display: grid; gap: 12px; }
        .inline-form { display: grid; gap: 10px; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); align-items: end; }
        .panel-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 18px;
        }
        .panel-heading h2 {
            margin: 0;
            font-size: 18px;
            line-height: 1.25;
        }
        .panel-heading p { margin: 5px 0 0; color: var(--fi-muted); }
        .shield-form { display: grid; gap: 16px; }
        .shield-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 260px;
            gap: 16px;
            align-items: stretch;
        }
        .shield-card { min-height: 100%; }
        .shield-summary {
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: linear-gradient(135deg, #111827 0%, #431407 100%);
            color: #fff;
        }
        .shield-summary .label, .shield-summary .page-description { color: rgba(255, 255, 255, .72); }
        .compact-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .shield-permissions { padding: 0; overflow: hidden; }
        .shield-heading {
            padding: 18px 20px;
            margin: 0;
            border-bottom: 1px solid var(--fi-border);
            background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        }
        .shield-matrix { display: grid; gap: 0; }
        .shield-resource { border-bottom: 1px solid var(--fi-border); }
        .shield-resource:last-child { border-bottom: 0; }
        .shield-resource-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 13px 18px;
            background: #f8fafc;
            color: var(--fi-muted);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .shield-resource-title {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            color: var(--fi-text);
            font-size: 13px;
        }
        .shield-resource-title input,
        .shield-permission-pill input {
            width: 16px;
            height: 16px;
            accent-color: var(--fi-primary);
        }
        .shield-action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 10px;
            padding: 14px 18px 18px;
        }
        .shield-action-cell {
            display: grid;
            gap: 8px;
            align-content: start;
            border: 1px solid var(--fi-border);
            border-radius: 10px;
            background: #fff;
            padding: 10px;
        }
        .shield-action-name {
            color: #92400e;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .05em;
            text-transform: uppercase;
        }
        .shield-permission-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            border: 1px solid transparent;
            border-radius: 8px;
            padding: 7px 8px;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
            background: #f8fafc;
            overflow-wrap: anywhere;
        }
        .shield-permission-pill:has(input:checked) {
            border-color: #fed7aa;
            background: #fff7ed;
            color: #9a3412;
        }
        .user-role-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 8px;
            padding: 10px;
            border: 1px solid var(--fi-border);
            border-radius: 8px;
            background: #f8fafc;
        }
        .sticky-footer {
            position: sticky;
            bottom: 0;
            z-index: 4;
            background: rgba(248, 250, 252, .92);
            backdrop-filter: blur(10px);
            border: 1px solid var(--fi-border);
            border-radius: 10px;
            padding: 12px;
            margin-top: 0;
        }
        .settings-shell {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            gap: 16px;
            align-items: start;
        }
        .settings-tabs {
            position: sticky;
            top: 84px;
            display: grid;
            gap: 6px;
            padding: 10px;
        }
        .settings-tab {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border-radius: 9px;
            padding: 10px 11px;
            color: #334155;
            text-decoration: none;
            font-weight: 800;
        }
        .settings-tab small {
            color: var(--fi-muted);
            font-size: 11px;
            font-weight: 800;
        }
        .settings-tab:hover { background: var(--fi-soft); }
        .settings-tab.active {
            background: color-mix(in srgb, var(--fi-primary) 10%, transparent);
            color: var(--fi-primary-strong);
            box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--fi-primary) 30%, transparent);
        }
        .settings-panel { padding: 0; overflow: hidden; }
        .settings-heading {
            padding: 20px 22px;
            margin: 0;
            border-bottom: 1px solid var(--fi-border);
            background:
                radial-gradient(circle at top right, rgba(217, 119, 6, .14), transparent 32%),
                linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        }
        .settings-chip {
            border: 1px solid color-mix(in srgb, var(--fi-primary) 30%, transparent);
            border-radius: 999px;
            background: color-mix(in srgb, var(--fi-primary) 10%, transparent);
            color: var(--fi-primary-strong);
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 900;
        }
        .settings-field-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            padding: 22px;
        }
        .settings-actions-panel {
            border-top: 1px solid var(--fi-border);
            padding: 0 22px 22px;
        }
        .settings-subheading {
            margin: 0;
            padding: 18px 0 14px;
        }
        .settings-subheading h3 {
            margin: 0;
            font-size: 16px;
        }
        .settings-action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
        }
        .settings-action-card {
            display: grid;
            gap: 4px;
            border: 1px solid var(--fi-border);
            border-radius: 10px;
            background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
            padding: 14px;
            text-decoration: none;
            box-shadow: 0 10px 20px rgba(15, 23, 42, .04);
        }
        .settings-action-card:hover {
            border-color: #fed7aa;
            background: #fff7ed;
            color: #9a3412;
        }
        .settings-action-card span {
            color: var(--fi-muted);
            font-size: 12px;
            font-weight: 700;
        }
        .settings-field-wide { grid-column: 1 / -1; }
        .field-hint {
            display: block;
            margin-top: 6px;
            color: var(--fi-muted);
            font-size: 12px;
            font-weight: 700;
        }
        .toggle-row {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 40px;
            margin: 0;
            border: 1px solid var(--fi-border);
            border-radius: 8px;
            background: #fff;
            padding: 8px 10px;
        }
        .toggle-row input { display: none; }
        .toggle-switch {
            width: 38px;
            height: 22px;
            border-radius: 999px;
            background: #cbd5e1;
            position: relative;
            transition: background .15s ease;
        }
        .toggle-switch::after {
            content: "";
            width: 18px;
            height: 18px;
            border-radius: 999px;
            background: #fff;
            position: absolute;
            top: 2px;
            left: 2px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .25);
            transition: transform .15s ease;
        }
        .toggle-row input:checked + .toggle-switch { background: var(--fi-primary); }
        .toggle-row input:checked + .toggle-switch::after { transform: translateX(16px); }
        .media-library { display: grid; gap: 14px; }
        .media-toolbar { margin-bottom: 0; }
        .media-import {
            justify-content: flex-start;
            border: 1px dashed var(--fi-border);
            border-radius: 10px;
            background: #f8fafc;
            padding: 10px;
        }
        .view-toggle {
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--fi-border);
            border-radius: 8px;
            background: #fff;
            color: #475569;
            padding: 8px 12px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 900;
        }
        .view-toggle:hover { background: var(--fi-soft); }
        .view-toggle.active {
            border-color: color-mix(in srgb, var(--fi-primary) 30%, transparent);
            background: color-mix(in srgb, var(--fi-primary) 10%, transparent);
            color: var(--fi-primary-strong);
        }
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 14px;
        }
        .media-card {
            overflow: hidden;
            border: 1px solid var(--fi-border);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 8px 22px rgba(15, 23, 42, .06);
        }
        .media-preview {
            display: grid;
            place-items: center;
            aspect-ratio: 4 / 3;
            background:
                linear-gradient(45deg, #f8fafc 25%, transparent 25%),
                linear-gradient(-45deg, #f8fafc 25%, transparent 25%),
                linear-gradient(45deg, transparent 75%, #f8fafc 75%),
                linear-gradient(-45deg, transparent 75%, #f8fafc 75%);
            background-color: #eef2f7;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0;
            background-size: 20px 20px;
        }
        .media-preview img, .media-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .media-file-icon {
            display: grid;
            place-items: center;
            min-width: 74px;
            min-height: 74px;
            border-radius: 18px;
            background: #111827;
            color: #fff;
            font-weight: 900;
            letter-spacing: .05em;
        }
        .media-meta {
            display: grid;
            gap: 4px;
            padding: 12px;
        }
        .media-meta strong {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .media-meta span {
            color: var(--fi-muted);
            font-size: 12px;
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .media-actions {
            border-top: 1px solid var(--fi-border);
            padding: 8px;
        }
        .media-thumb {
            width: 72px;
            height: 54px;
            overflow: hidden;
            display: grid;
            place-items: center;
            border: 1px solid var(--fi-border);
            border-radius: 8px;
            background: #f8fafc;
            color: var(--fi-muted);
            font-size: 11px;
            font-weight: 900;
        }
        .empty-state { padding: 26px; text-align: center; color: var(--fi-muted); }
        .pagination { margin-top: 14px; }
        .pagination-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            border-top: 1px solid var(--fi-border);
            padding-top: 14px;
        }
        .pagination-summary {
            color: var(--fi-muted);
            font-size: 13px;
            font-weight: 700;
        }
        .pagination-pages {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .pagination-btn {
            min-width: 34px;
            min-height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--fi-border);
            border-radius: 8px;
            background: #fff;
            color: #475569;
            padding: 7px 10px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 800;
            line-height: 1;
        }
        .pagination-btn:hover { background: var(--fi-soft); color: var(--fi-text); }
        .pagination-btn.active {
            border-color: var(--fi-primary);
            background: #fff7ed;
            color: #9a3412;
        }
        .pagination-btn.disabled {
            color: #cbd5e1;
            background: #f8fafc;
            cursor: not-allowed;
        }

        @media (max-width: 960px) {
            .admin-shell { display: block; }
            .sidebar { position: static; width: auto; height: auto; border-right: 0; border-bottom: 1px solid var(--fi-border); }
            .nav-group { margin: 10px 0; }
            .main { display: block; }
            .topbar { position: static; padding: 14px 18px; }
            .content { padding: 18px; }
            .form-grid { grid-template-columns: 1fr; }
            .shield-layout, .settings-shell, .settings-field-grid, .compact-grid { grid-template-columns: 1fr; }
            .settings-tabs { position: static; }
            .toolbar-row, .list-filter-grid { grid-template-columns: 1fr; }
            .list-filter-actions, .crud-import-row { justify-content: stretch; }
            .list-filter-actions .btn { flex: 1; justify-content: center; }
            .import-form { justify-content: stretch; }
            .import-form .btn { width: 100%; }
            .dashboard-grid { grid-template-columns: 1fr; }
            .pagination-wrap { align-items: stretch; }
            .pagination-pages { width: 100%; }
        }
    </style>
</head>
<body>
    @auth
        <div class="admin-shell">
            <aside class="sidebar">
                <div class="brand">
                    <div class="brand-mark">A</div>
                    <div>
                    <div class="brand-title">{{ __('admin.brand.title') }}</div>
                    <div class="brand-subtitle">{{ __('admin.brand.subtitle') }}</div>
                    </div>
                </div>

                @foreach ($navGroups as $section)
                    <nav class="nav-group" aria-label="{{ __($section['label']) }}">
                        @if ($section['label'] === 'admin.sidebar.group.dashboard' || count($section['items']) === 1 && !isset($section['items'][0]['children']))
                            @foreach ($section['items'] as $item)
                                <a class="nav-link {{ $isItemActive($item) ? 'active' : '' }}" href="{{ $navUrl($item) }}">
                                    <span>{{ __($item['label']) }}</span>
                                    <span class="nav-dot"></span>
                                </a>
                            @endforeach
                        @else
                            @php
                                $sectionActive = collect($section['items'])->contains(function ($item) use ($isItemActive) {
                                    if (isset($item['children'])) {
                                        return collect($item['children'])->contains(fn ($child) => $isItemActive($child));
                                    }

                                    return $isItemActive($item);
                                });
                            @endphp
                            <details class="nav-section" {{ $sectionActive ? 'open' : '' }}>
                                <summary class="nav-summary">
                                    <span>{{ __($section['label']) }}</span>
                                    <span class="nav-caret" aria-hidden="true"></span>
                                </summary>
                                <div class="nav-subitems">
                                    @foreach ($section['items'] as $item)
                                        @if (isset($item['children']))
                                            @php($childActive = collect($item['children'])->contains(fn ($child) => $isItemActive($child)))
                                            <div class="nav-subgroup {{ $childActive ? 'active' : '' }}">
                                                <div class="nav-subheading">{{ __($item['label']) }}</div>
                                                <div class="nav-subitems">
                                                    @foreach ($item['children'] as $child)
                                                        <a class="nav-link nav-link-child {{ $isItemActive($child) ? 'active' : '' }}" href="{{ $navUrl($child) }}">
                                                            <span>{{ __($child['label']) }}</span>
                                                            <span class="nav-dot"></span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <a class="nav-link {{ $isItemActive($item) ? 'active' : '' }}" href="{{ $navUrl($item) }}">
                                                <span>{{ __($item['label']) }}</span>
                                                <span class="nav-dot"></span>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    </nav>
                @endforeach
            </aside>

            <div class="main">
                <header class="topbar">
                    <div>
                        <div class="topbar-title">{{ $title ?? __('admin.brand.title') }}</div>
                        <div class="topbar-meta">{{ auth()->user()->email }} &bull; {{ auth()->user()->roles->pluck('name')->map(fn($r) => \App\Support\AdminLabel::role($r))->join(', ') ?: 'Thành viên' }}</div>
                    </div>
                    <div class="actions">
                        <a class="btn {{ app()->getLocale() === 'vi' ? '' : 'btn-secondary' }}" href="{{ request()->fullUrlWithQuery(['lang' => 'vi']) }}">VI</a>
                        <a class="btn {{ app()->getLocale() === 'en' ? '' : 'btn-secondary' }}" href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}">EN</a>
                        <form action="{{ route('admin.logout') }}" method="post">
                            @csrf
                            <button class="btn btn-secondary" type="submit">{{ __('admin.auth.logout') }}</button>
                        </form>
                    </div>
                </header>

                <main class="content">
                    @yield('content')
                </main>
            </div>
        </div>
    @else
        <main class="login-wrap">
            @yield('content')
        </main>
    @endauth
</body>
</html>
