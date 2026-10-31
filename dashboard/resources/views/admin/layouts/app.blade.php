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
                    ];
                }
                return $items;
            }),
        ],
        [
            'label' => 'Điều Hành & Quản Trị',
            'items' => [
                ['label' => 'Trung tâm điều hành (CEO)', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard'],
                ['label' => 'Hộp thư phê duyệt chi phí', 'route' => 'admin.financial-proposals.index', 'match' => 'admin.financial-proposals.*'],
                ['label' => 'Sự cố & Rủi ro vận hành', 'route' => 'admin.incidents.index', 'match' => 'admin.incidents.*'],
                ['label' => 'Điều phối chuyên trách', 'route' => 'admin.department-agents.index', 'match' => 'admin.department-agents.*'],
                ['label' => 'Nhật ký kiểm toán hệ thống', 'route' => 'admin.department-audit-logs.index', 'match' => 'admin.department-audit-logs.*'],
            ],
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

    $configuredColor = app(\App\Settings\DBSettings::class)->primary_color ?? '#004687';
    $resolvedPrimaryColor = ($configuredColor === '#d97706' || empty($configuredColor)) ? '#004687' : $configuredColor;
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? __('admin.brand.title') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --fi-bg: #f8fafc;
            --fi-panel: #ffffff;
            --fi-soft: #f1f5f9;
            --fi-border: #e2e8f0;
            --fi-border-strong: #cbd5e1;
            --fi-text: #0f172a;
            --fi-text-secondary: #475569;
            --fi-muted: #64748b;
            --fi-navy-dark: #09253e;
            --fi-gold: #fbae17;
            --fi-primary: {{ $resolvedPrimaryColor }};
            --fi-primary-strong: color-mix(in srgb, var(--fi-primary) 82%, black);
            --fi-danger: #dc2626;
            --fi-success: #059669;
            --fi-warning: #d97706;
            --fi-info: #0284c7;
            --fi-ring: 0 1px 2px 0 rgba(15, 23, 42, .04);
            --fi-shadow-md: 0 4px 12px -2px rgba(15, 23, 42, .06), 0 2px 4px -2px rgba(15, 23, 42, .04);
        }

        * { box-sizing: border-box; }
        ::selection {
            background: var(--fi-primary);
            color: #ffffff;
        }
        body {
            margin: 0;
            background: var(--fi-bg);
            color: var(--fi-text);
            font-family: "Be Vietnam Pro", "DM Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 13.5px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        a { color: inherit; }
        .tabular-nums, .value, .stat, table td, .badge, .pagination {
            font-variant-numeric: tabular-nums;
        }

        /* Layout Shell */
        .admin-shell { min-height: 100vh; display: flex; }
        .sidebar {
            width: 264px;
            flex: 0 0 264px;
            background: var(--fi-panel);
            border-right: 1px solid var(--fi-border);
            padding: 16px 12px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 8px 16px;
            border-bottom: 1px solid var(--fi-border);
            margin-bottom: 14px;
        }
        .brand-mark {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: var(--fi-navy-dark);
            color: var(--fi-gold);
            font-weight: 800;
            font-size: 15px;
            box-shadow: var(--fi-ring);
            flex-shrink: 0;
        }
        .brand-title { font-weight: 700; font-size: 14px; color: var(--fi-navy-dark); letter-spacing: -0.01em; }
        .brand-subtitle { color: var(--fi-muted); font-size: 11px; margin-top: 1px; font-weight: 500; }

        /* Sidebar Navigation */
        .nav-group { margin: 10px 0; }
        .nav-heading {
            color: #94a3b8;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            padding: 0 10px 6px;
        }
        .nav-section {
            margin: 4px 0 8px;
            border-radius: 8px;
        }
        .nav-summary {
            list-style: none;
            cursor: pointer;
            user-select: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            min-height: 34px;
            padding: 6px 10px;
            border-radius: 7px;
            color: #475569;
            font-size: 12.5px;
            font-weight: 700;
            transition: background .15s ease, color .15s ease;
        }
        .nav-summary::-webkit-details-marker { display: none; }
        .nav-summary:hover { background: var(--fi-soft); color: var(--fi-text); }
        .nav-caret {
            width: 6px;
            height: 6px;
            border-right: 1.75px solid #94a3b8;
            border-bottom: 1.75px solid #94a3b8;
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
            min-height: 34px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 7px;
            text-decoration: none;
            color: #475569;
            font-size: 13px;
            font-weight: 500;
            transition: background .15s ease, color .15s ease;
        }
        .nav-link:hover { background: var(--fi-soft); color: var(--fi-text); }
        .nav-link.active {
            background: color-mix(in srgb, var(--fi-primary) 9%, transparent);
            color: var(--fi-primary);
            font-weight: 600;
        }
        .nav-dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: transparent;
            flex: 0 0 auto;
        }
        .nav-link.active .nav-dot { background: var(--fi-primary); }
        .nav-subgroup { margin: 4px 0 8px; }
        .nav-subheading {
            color: #64748b;
            font-size: 11.5px;
            font-weight: 700;
            padding: 4px 10px;
        }
        .nav-subgroup.active .nav-subheading { color: #0f172a; }
        .nav-subitems { padding-left: 8px; border-left: 1px solid #f1f5f9; margin-left: 10px; margin-top: 2px; }
        .nav-link-child {
            padding-left: 12px;
            font-size: 12.5px;
        }

        /* Main Area & Topbar */
        .main { min-width: 0; flex: 1; display: flex; flex-direction: column; }
        .topbar {
            min-height: 58px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 28px;
            background: rgba(255, 255, 255, .92);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--fi-border);
            position: sticky;
            top: 0;
            z-index: 20;
        }
        .topbar-title { font-weight: 700; font-size: 15px; color: var(--fi-navy-dark); letter-spacing: -0.01em; }
        .topbar-meta { color: var(--fi-muted); font-size: 12px; margin-top: 1px; }
        .content { width: 100%; max-width: 1440px; padding: 24px 28px; margin: 0 auto; }

        /* Cards & Panels */
        .card, .table-panel {
            background: var(--fi-panel);
            border: 1px solid var(--fi-border);
            border-radius: 12px;
            box-shadow: var(--fi-ring);
        }
        .card { padding: 20px 22px; }

        /* Page Headers */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }
        .page-title {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: var(--fi-navy-dark);
            line-height: 1.25;
            letter-spacing: -0.02em;
        }
        .page-description { margin: 4px 0 0; color: var(--fi-muted); font-size: 13px; }
        .actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

        /* Buttons */
        .btn {
            min-height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 1px solid transparent;
            border-radius: 8px;
            padding: 7px 13px;
            background: var(--fi-primary);
            color: #fff;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            font-size: 12.5px;
            line-height: 1;
            transition: background .15s ease, border-color .15s ease, transform .1s ease;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
        }
        .btn:hover { background: var(--fi-primary-strong); }
        .btn:active { transform: scale(0.99); }
        .btn-secondary {
            background: #fff;
            color: #334155;
            border-color: var(--fi-border-strong);
            box-shadow: none;
        }
        .btn-secondary:hover { background: var(--fi-soft); color: var(--fi-text); border-color: #94a3b8; }
        .btn-danger { background: var(--fi-danger); color: #fff; }
        .btn-danger:hover { background: #b91c1c; }

        .link-action {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 4px 8px;
            border-radius: 6px;
            color: #475569;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            border: 0;
            background: transparent;
            cursor: pointer;
            transition: background .15s ease, color .15s ease;
        }
        .link-action:hover { background: var(--fi-soft); color: var(--fi-primary); }
        .link-danger { color: var(--fi-danger); }
        .link-danger:hover { background: #fef2f2; color: #b91c1c; }

        /* Semantic Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 600;
            line-height: 1.4;
            border: 1px solid transparent;
            white-space: nowrap;
        }
        .badge-pending, .badge-warning, .bg-yellow {
            background: #fffbeb;
            color: #b45309;
            border-color: #fde68a;
        }
        .badge-new, .badge-processing, .badge-shipping, .badge-delivering, .badge-info, .bg-blue {
            background: #f0f9ff;
            color: #0369a1;
            border-color: #bae6fd;
        }
        .badge-completed, .badge-paid, .badge-success, .bg-green {
            background: #ecfdf5;
            color: #047857;
            border-color: #a7f3d0;
        }
        .badge-cancelled, .badge-failed, .badge-danger, .bg-red {
            background: #fef2f2;
            color: #b91c1c;
            border-color: #fecaca;
        }
        .badge-refunded, .badge-secondary, .bg-gray {
            background: #f1f5f9;
            color: #475569;
            border-color: #e2e8f0;
        }

        /* Filters & Toolbars */
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
            padding: 18px 22px;
            border-radius: 12px 12px 0 0;
            margin: -20px -22px 20px -22px;
        }
        .list-filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
        }
        .list-filter-grid label {
            display: block;
            margin-bottom: 5px;
            color: var(--fi-muted);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .list-filter-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            align-items: center;
            margin-top: 16px;
            border-top: 1px solid #e2e8f0;
            padding-top: 14px;
        }
        .list-filter-actions .btn {
            min-height: 34px;
            padding: 7px 18px;
        }
        .crud-import-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 14px;
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
            min-height: 34px;
            padding: 5px 8px;
            color: var(--fi-muted);
            font-size: 12px;
        }
        .workspace-import-card {
            margin-bottom: 16px;
            padding: 14px;
        }
        .workspace-import-card .import-form { justify-content: flex-start; }
        .workspace-import-card select { width: min(100%, 240px); }
        .import-error { margin: 0 0 12px; }

        .searchbar input { padding-left: 36px; }
        .searchbar::before {
            content: "";
            width: 13px;
            height: 13px;
            border: 1.75px solid #94a3b8;
            border-radius: 999px;
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-55%);
        }
        .searchbar::after {
            content: "";
            width: 6px;
            height: 1.75px;
            background: #94a3b8;
            position: absolute;
            left: 24px;
            top: 50%;
            margin-top: 4px;
            transform: rotate(45deg);
        }

        /* Form Controls */
        label { display: block; color: #334155; font-size: 12.5px; font-weight: 600; margin-bottom: 5px; }
        input, select, textarea {
            width: 100%;
            border: 1px solid var(--fi-border-strong);
            border-radius: 8px;
            background: #fff;
            color: var(--fi-text);
            padding: 8px 11px;
            font: inherit;
            font-size: 13px;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--fi-primary);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--fi-primary) 14%, transparent);
        }
        textarea { min-height: 110px; resize: vertical; }
        .form-grid { display: grid; gap: 16px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .form-row { margin-bottom: 0; }
        .form-row-wide { grid-column: 1 / -1; }
        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 8px;
            border: 1px solid var(--fi-border);
            border-radius: 10px;
            background: #f8fafc;
            padding: 12px;
            max-height: 400px;
            overflow: auto;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
            color: #334155;
            padding: 8px 10px;
            font-size: 12.5px;
            font-weight: 600;
        }
        .checkbox-item input {
            width: 15px;
            height: 15px;
            flex: 0 0 auto;
            accent-color: var(--fi-primary);
        }
        .form-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            border-top: 1px solid var(--fi-border);
            margin-top: 20px;
            padding-top: 16px;
        }
        .error { color: var(--fi-danger); font-size: 12px; margin-top: 5px; font-weight: 500; }
        .status-message {
            border: 1px solid #a7f3d0;
            background: #ecfdf5;
            color: #047857;
            padding: 10px 14px;
            border-radius: 8px;
            margin: 0 0 14px;
            font-size: 13px;
            font-weight: 600;
        }

        /* Data Tables */
        .table-wrap { overflow: auto; border-radius: 10px; border: 1px solid var(--fi-border); background: #fff; }
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .04em;
            font-weight: 700;
            text-align: left;
            border-bottom: 1px solid var(--fi-border);
            padding: 10px 14px;
            white-space: nowrap;
        }
        tbody td, tbody th {
            border-bottom: 1px solid #f1f5f9;
            padding: 11px 14px;
            vertical-align: middle;
            font-size: 13px;
        }
        tbody tr:last-child td, tbody tr:last-child th { border-bottom: 0; }
        tbody tr:hover td { background: #f8fafc; }
        .cell-muted { color: var(--fi-muted); }
        .details-table th { width: 220px; color: #475569; background: #f8fafc; font-weight: 600; }
        .details-table td { white-space: pre-wrap; }

        /* KPI Stats & Dashboard Widgets */
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px; margin-top: 16px; }
        .stat {
            border: 1px solid var(--fi-border);
            border-radius: 12px;
            background: #ffffff;
            padding: 16px 18px;
            box-shadow: var(--fi-ring);
        }
        .label { color: var(--fi-muted); font-size: 12px; font-weight: 600; }
        .value { font-size: 24px; line-height: 1.15; font-weight: 800; color: var(--fi-navy-dark); margin-top: 6px; letter-spacing: -0.02em; }
        .dashboard-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(280px, .8fr);
            gap: 16px;
            margin-top: 16px;
        }
        .metric-list { display: grid; gap: 8px; }
        .metric-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border: 1px solid var(--fi-border);
            border-radius: 8px;
            padding: 9px 12px;
            background: #fff;
        }
        .metric-row strong { font-size: 15px; color: var(--fi-navy-dark); }

        /* Auth Login */
        .login-wrap { min-height: 100vh; display: grid; place-items: center; padding: 24px; background: #f8fafc; }
        .login-card { width: min(100%, 410px); padding: 28px; }
        .section-title { margin: 20px 0 10px; font-size: 15px; font-weight: 700; color: var(--fi-navy-dark); }
        .stack { display: grid; gap: 12px; }
        .inline-form { display: grid; gap: 10px; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); align-items: end; }

        /* Panel Headings */
        .panel-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 16px;
        }
        .panel-heading h2 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: var(--fi-navy-dark);
            line-height: 1.3;
        }
        .panel-heading p { margin: 4px 0 0; color: var(--fi-muted); font-size: 12.5px; }

        /* Shield / Roles & Permissions */
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
            background: var(--fi-navy-dark);
            color: #fff;
        }
        .shield-summary .value { color: #fff; }
        .shield-summary .label, .shield-summary .page-description { color: rgba(255, 255, 255, .72); }
        .compact-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .shield-permissions { padding: 0; overflow: hidden; }
        .shield-heading {
            padding: 16px 20px;
            margin: 0;
            border-bottom: 1px solid var(--fi-border);
            background: #f8fafc;
        }
        .shield-matrix { display: grid; gap: 0; }
        .shield-resource { border-bottom: 1px solid var(--fi-border); }
        .shield-resource:last-child { border-bottom: 0; }
        .shield-resource-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 11px 18px;
            background: #f8fafc;
            color: var(--fi-muted);
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .shield-resource-title {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            color: var(--fi-text);
            font-size: 13px;
        }
        .shield-resource-title input,
        .shield-permission-pill input {
            width: 15px;
            height: 15px;
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
            color: var(--fi-primary);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .shield-permission-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            border: 1px solid transparent;
            border-radius: 7px;
            padding: 6px 8px;
            color: #334155;
            font-size: 12px;
            font-weight: 600;
            background: #f8fafc;
            overflow-wrap: anywhere;
        }
        .shield-permission-pill:has(input:checked) {
            border-color: color-mix(in srgb, var(--fi-primary) 30%, transparent);
            background: color-mix(in srgb, var(--fi-primary) 8%, transparent);
            color: var(--fi-primary-strong);
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
            background: rgba(255, 255, 255, .94);
            backdrop-filter: blur(8px);
            border: 1px solid var(--fi-border);
            border-radius: 10px;
            padding: 12px 16px;
            margin-top: 0;
        }

        /* Settings */
        .settings-shell {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            gap: 16px;
            align-items: start;
        }
        .settings-tabs {
            position: sticky;
            top: 78px;
            display: grid;
            gap: 4px;
            padding: 10px;
        }
        .settings-tab {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border-radius: 8px;
            padding: 9px 11px;
            color: #475569;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }
        .settings-tab small {
            color: var(--fi-muted);
            font-size: 11px;
            font-weight: 700;
        }
        .settings-tab:hover { background: var(--fi-soft); color: var(--fi-text); }
        .settings-tab.active {
            background: color-mix(in srgb, var(--fi-primary) 10%, transparent);
            color: var(--fi-primary);
            font-weight: 700;
        }
        .settings-panel { padding: 0; overflow: hidden; }
        .settings-heading {
            padding: 18px 22px;
            margin: 0;
            border-bottom: 1px solid var(--fi-border);
            background: #f8fafc;
        }
        .settings-chip {
            border: 1px solid color-mix(in srgb, var(--fi-primary) 25%, transparent);
            border-radius: 6px;
            background: color-mix(in srgb, var(--fi-primary) 8%, transparent);
            color: var(--fi-primary);
            padding: 4px 9px;
            font-size: 11.5px;
            font-weight: 700;
        }
        .settings-field-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            padding: 20px 22px;
        }
        .settings-actions-panel {
            border-top: 1px solid var(--fi-border);
            padding: 0 22px 22px;
        }
        .settings-subheading {
            margin: 0;
            padding: 16px 0 12px;
        }
        .settings-subheading h3 {
            margin: 0;
            font-size: 15px;
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
            background: #ffffff;
            padding: 14px;
            text-decoration: none;
            transition: border-color .15s ease, background .15s ease;
        }
        .settings-action-card:hover {
            border-color: var(--fi-primary);
            background: #f8fafc;
            color: var(--fi-primary);
        }
        .settings-action-card span {
            color: var(--fi-muted);
            font-size: 12px;
            font-weight: 500;
        }
        .settings-field-wide { grid-column: 1 / -1; }
        .field-hint {
            display: block;
            margin-top: 5px;
            color: var(--fi-muted);
            font-size: 11.5px;
        }
        .toggle-row {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 38px;
            margin: 0;
            border: 1px solid var(--fi-border);
            border-radius: 8px;
            background: #fff;
            padding: 7px 10px;
        }
        .toggle-row input { display: none; }
        .toggle-switch {
            width: 36px;
            height: 20px;
            border-radius: 999px;
            background: #cbd5e1;
            position: relative;
            transition: background .15s ease;
            flex-shrink: 0;
        }
        .toggle-switch::after {
            content: "";
            width: 16px;
            height: 16px;
            border-radius: 999px;
            background: #fff;
            position: absolute;
            top: 2px;
            left: 2px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .2);
            transition: transform .15s ease;
        }
        .toggle-row input:checked + .toggle-switch { background: var(--fi-primary); }
        .toggle-row input:checked + .toggle-switch::after { transform: translateX(16px); }

        /* Media Library */
        .media-library { display: grid; gap: 14px; }
        .media-toolbar { margin-bottom: 0; }
        .media-import {
            justify-content: flex-start;
            border: 1px dashed var(--fi-border-strong);
            border-radius: 10px;
            background: #f8fafc;
            padding: 10px 12px;
        }
        .view-toggle {
            min-height: 34px;
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--fi-border-strong);
            border-radius: 8px;
            background: #fff;
            color: #475569;
            padding: 6px 12px;
            text-decoration: none;
            font-size: 12.5px;
            font-weight: 600;
        }
        .view-toggle:hover { background: var(--fi-soft); }
        .view-toggle.active {
            border-color: var(--fi-primary);
            background: color-mix(in srgb, var(--fi-primary) 10%, transparent);
            color: var(--fi-primary);
        }
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 14px;
        }
        .media-card {
            overflow: hidden;
            border: 1px solid var(--fi-border);
            border-radius: 10px;
            background: #fff;
            box-shadow: var(--fi-ring);
        }
        .media-preview {
            display: grid;
            place-items: center;
            aspect-ratio: 4 / 3;
            background-color: #f1f5f9;
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
            min-width: 64px;
            min-height: 64px;
            border-radius: 12px;
            background: var(--fi-navy-dark);
            color: #fff;
            font-weight: 800;
            font-size: 12px;
            letter-spacing: .04em;
        }
        .media-meta {
            display: grid;
            gap: 3px;
            padding: 10px 12px;
        }
        .media-meta strong {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 12.5px;
        }
        .media-meta span {
            color: var(--fi-muted);
            font-size: 11.5px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .media-actions {
            border-top: 1px solid var(--fi-border);
            padding: 6px 8px;
        }
        .media-thumb {
            width: 64px;
            height: 48px;
            overflow: hidden;
            display: grid;
            place-items: center;
            border: 1px solid var(--fi-border);
            border-radius: 6px;
            background: #f8fafc;
            color: var(--fi-muted);
            font-size: 11px;
            font-weight: 700;
        }
        .empty-state { padding: 28px; text-align: center; color: var(--fi-muted); font-size: 13px; }

        /* Pagination */
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
            font-size: 12.5px;
            font-weight: 500;
        }
        .pagination-pages {
            display: flex;
            align-items: center;
            gap: 5px;
            flex-wrap: wrap;
        }
        .pagination-btn {
            min-width: 32px;
            min-height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--fi-border);
            border-radius: 7px;
            background: #fff;
            color: #475569;
            padding: 6px 9px;
            text-decoration: none;
            font-size: 12.5px;
            font-weight: 600;
            line-height: 1;
            transition: background .15s ease, border-color .15s ease;
        }
        .pagination-btn:hover { background: var(--fi-soft); color: var(--fi-text); }
        .pagination-btn.active {
            border-color: var(--fi-primary);
            background: var(--fi-primary);
            color: #ffffff;
        }
        .pagination-btn.disabled {
            color: #cbd5e1;
            background: #f8fafc;
            cursor: not-allowed;
        }

        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after {
                animation-duration: 0.01ms !important;
                transition-duration: 0.01ms !important;
            }
        }

        @media (max-width: 960px) {
            .admin-shell { display: block; }
            .sidebar { position: static; width: auto; height: auto; border-right: 0; border-bottom: 1px solid var(--fi-border); }
            .nav-group { margin: 8px 0; }
            .main { display: block; }
            .topbar { position: static; padding: 12px 16px; }
            .content { padding: 16px; }
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
                    <div class="brand-mark">N</div>
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
