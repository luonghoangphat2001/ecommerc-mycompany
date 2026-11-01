@php
    $navGroups = [
        [
            'label' => 'admin.sidebar.group.dashboard',
            'icon' => 'dashboard',
            'items' => [
                ['label' => 'admin.sidebar.dashboard', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'home'],
            ],
        ],
        [
            'label' => 'admin.sidebar.group.orders',
            'icon' => 'shopping-bag',
            'items' => [
                ['label' => 'admin.sidebar.orders', 'route' => 'admin.orders.index', 'match' => 'admin.orders.*', 'icon' => 'receipt'],
                ['label' => 'admin.sidebar.refunds', 'route' => 'admin.refunds.index', 'match' => 'admin.refunds.*', 'icon' => 'rotate-ccw'],
                ['label' => 'admin.sidebar.payments', 'route' => 'admin.payments.index', 'match' => 'admin.payments.*', 'icon' => 'credit-card'],
            ],
        ],
        [
            'label' => 'admin.sidebar.group.products',
            'icon' => 'package',
            'items' => [
                ['label' => 'admin.sidebar.products', 'route' => 'admin.products.index', 'match' => 'admin.products.*', 'icon' => 'package'],
                ['label' => 'admin.sidebar.product_categories', 'route' => 'admin.product-categories.index', 'match' => 'admin.product-categories.*', 'icon' => 'folder-tree'],
                ['label' => 'admin.sidebar.brands', 'route' => 'admin.brands.index', 'match' => 'admin.brands.*', 'icon' => 'award'],
                [
                    'label' => 'admin.sidebar.inventory_management',
                    'route' => 'admin.inventories.index',
                    'match' => ['admin.inventories.*', 'admin.inventory-records.*', 'admin.inventory-movements.*'],
                    'icon' => 'warehouse',
                    'permission' => ['view_inventories', 'view_inventory-records', 'view_inventory-movements'],
                    'routes_by_permission' => [
                        'view_inventories' => 'admin.inventories.index',
                        'view_inventory-records' => 'admin.inventory-records.index',
                        'view_inventory-movements' => 'admin.inventory-movements.index',
                    ],
                ],
            ],
        ],
        [
            'label' => 'admin.sidebar.group.customers',
            'icon' => 'users',
            'items' => [
                ['label' => 'admin.sidebar.customers', 'route' => 'admin.users.index', 'match' => 'admin.users.*', 'icon' => 'users'],
            ],
        ],
    ];

    $marketingSettings = app(\App\Settings\MarketingSettings::class);
    $marketingItems = [];
    if ($marketingSettings->enable_coupons) {
        $marketingItems[] = ['label' => 'admin.sidebar.coupons', 'route' => 'admin.coupons.index', 'match' => 'admin.coupons.*', 'icon' => 'tag'];
    }
    if ($marketingSettings->loyalty_enabled) {
        $marketingItems[] = ['label' => 'admin.sidebar.loyalty', 'route' => 'admin.loyalty-points.index', 'match' => 'admin.loyalty-points.*', 'icon' => 'star'];
    }
    if ($marketingSettings->upsell_enabled) {
        $marketingItems[] = ['label' => 'admin.sidebar.upsell_products', 'route' => 'admin.upsell-products.index', 'match' => 'admin.upsell-products.*', 'icon' => 'trending-up'];
    }
    if ($marketingSettings->cross_sell_enabled) {
        $marketingItems[] = ['label' => 'admin.sidebar.cross_sell_products', 'route' => 'admin.cross-sell-products.index', 'match' => 'admin.cross-sell-products.*', 'icon' => 'shuffle'];
    }
    if ($marketingSettings->combo_enabled) {
        $marketingItems[] = ['label' => 'admin.sidebar.combo_products', 'route' => 'admin.combo-products.index', 'match' => 'admin.combo-products.*', 'icon' => 'layers'];
    }

    if (!empty($marketingItems)) {
        $navGroups[] = [
            'label' => 'admin.sidebar.group.marketing',
            'icon' => 'megaphone',
            'items' => $marketingItems,
        ];
    }

    $navGroups[] = [
        'label' => 'admin.sidebar.group.online_store',
        'icon' => 'store',
        'items' => [
            ['label' => 'admin.sidebar.pages', 'route' => 'admin.pages.index', 'match' => 'admin.pages.*', 'icon' => 'layout'],
            ['label' => 'admin.sidebar.posts', 'route' => 'admin.posts.index', 'match' => 'admin.posts.*', 'icon' => 'pen-tool'],
            ['label' => 'admin.sidebar.post_categories', 'route' => 'admin.post-categories.index', 'match' => 'admin.post-categories.*', 'icon' => 'folder-tree'],
            ['label' => 'admin.sidebar.menus', 'route' => 'admin.menus.index', 'match' => 'admin.menu*', 'icon' => 'menu'],
            ['label' => 'admin.sidebar.media', 'route' => 'admin.media.index', 'match' => 'admin.media.*', 'icon' => 'image'],
            ['label' => 'admin.sidebar.comments', 'route' => 'admin.comments.index', 'match' => 'admin.comments.*', 'icon' => 'message-square'],
        ],
    ];

    $departmentItems = \Illuminate\Support\Facades\Cache::remember('workspace_sidebar_items', 3600, function () {
        $items = [];
        $departments = \App\Models\Department::where('is_active', true)->get();
        foreach ($departments as $dept) {
            $items[] = [
                'label' => __("workspace.sidebar.{$dept->code}") === "workspace.sidebar.{$dept->code}" ? $dept->name : "workspace.sidebar.{$dept->code}",
                'route' => 'admin.workspace.show',
                'query' => ['code' => $dept->code],
                'match' => 'admin.workspace.show',
                'icon' => 'grid',
            ];
        }
        return $items;
    });

    if (!empty($departmentItems)) {
        $navGroups[] = [
            'label' => 'department.sidebar.title',
            'icon' => 'briefcase',
            'items' => array_merge([
                ['label' => 'department.sidebar.departments_agents', 'route' => 'admin.departments.index', 'match' => 'admin.departments.*', 'icon' => 'briefcase'],
            ], $departmentItems),
        ];
    }

    $navGroups[] = [
        'label' => 'admin.executive.sidebar_group',
        'icon' => 'shield',
        'items' => [
            ['label' => 'admin.executive.ceo_center', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'activity'],
            ['label' => 'admin.executive.financial_proposals', 'route' => 'admin.financial-proposals.index', 'match' => 'admin.financial-proposals.*', 'icon' => 'credit-card'],
            ['label' => 'admin.executive.incidents', 'route' => 'admin.incidents.index', 'match' => 'admin.incidents.*', 'icon' => 'alert-triangle'],
            ['label' => 'admin.executive.department_agents', 'route' => 'admin.department-agents.index', 'match' => 'admin.department-agents.*', 'icon' => 'cpu'],
            ['label' => 'admin.executive.audit_logs', 'route' => 'admin.department-audit-logs.index', 'match' => 'admin.department-audit-logs.*', 'icon' => 'terminal'],
        ],
    ];

    $webhookSettings = app(\App\Settings\WebhookSettings::class);
    $webhookEnabled = (bool) ($webhookSettings->enabled ?? false);

    $settingItems = [
        ['label' => 'admin.sidebar.general_settings', 'route' => 'admin.settings.index', 'match' => 'admin.settings.*', 'query' => ['tab' => 'general'], 'icon' => 'sliders', 'permission' => 'view_settings'],
        ['label' => 'admin.sidebar.storefront_settings', 'route' => 'admin.settings.index', 'match' => 'admin.settings.*', 'query' => ['tab' => 'settings'], 'icon' => 'palette', 'permission' => 'view_settings'],
        ['label' => 'admin.sidebar.checkout_settings', 'route' => 'admin.settings.index', 'match' => 'admin.settings.*', 'query' => ['tab' => 'checkout'], 'icon' => 'credit-card', 'permission' => 'view_settings'],
        ['label' => 'admin.sidebar.shipping_zones', 'route' => 'admin.shipping-zones.index', 'match' => 'admin.shipping-zones.*', 'icon' => 'truck', 'permission' => 'view_shipping-zones'],
        ['label' => 'admin.sidebar.shipping_methods', 'route' => 'admin.shipping-methods.index', 'match' => 'admin.shipping-methods.*', 'icon' => 'truck', 'permission' => 'view_shipping-methods'],
        ['label' => 'admin.sidebar.tax_classes', 'route' => 'admin.tax-classes.index', 'match' => 'admin.tax-classes.*', 'icon' => 'percent', 'permission' => 'view_tax-classes'],
        ['label' => 'admin.sidebar.tax_rates', 'route' => 'admin.tax-rates.index', 'match' => 'admin.tax-rates.*', 'icon' => 'percent', 'permission' => 'view_tax-rates'],
        ['label' => 'admin.sidebar.roles', 'route' => 'admin.roles.index', 'match' => 'admin.roles.*', 'icon' => 'lock', 'permission' => 'view_roles'],
        ['label' => 'admin.sidebar.permissions', 'route' => 'admin.permissions.index', 'match' => 'admin.permissions.*', 'icon' => 'shield', 'permission' => 'view_permissions'],
        ['label' => 'admin.sidebar.mail_settings', 'route' => 'admin.settings.index', 'match' => 'admin.settings.*', 'query' => ['tab' => 'mail'], 'icon' => 'mail', 'permission' => 'view_settings'],
        ['label' => 'admin.sidebar.mail_logs', 'route' => 'admin.mail-logs.index', 'match' => 'admin.mail-logs.*', 'icon' => 'mail', 'permission' => 'view_settings'],
    ];

    if ($webhookEnabled) {
        $settingItems[] = ['label' => 'admin.sidebar.webhooks', 'route' => 'admin.webhooks.index', 'match' => 'admin.webhooks.*', 'icon' => 'webhook', 'permission' => 'view_webhooks'];
        $settingItems[] = ['label' => 'admin.sidebar.webhook_logs', 'route' => 'admin.webhook-logs.index', 'match' => 'admin.webhook-logs.*', 'icon' => 'terminal', 'permission' => 'view_webhook-logs'];
    } else {
        $settingItems[] = ['label' => 'admin.sidebar.integrations', 'route' => 'admin.settings.index', 'match' => 'admin.settings.*', 'query' => ['tab' => 'webhook'], 'icon' => 'webhook', 'permission' => 'view_settings'];
    }

    $settingItems[] = ['label' => 'admin.sidebar.api_settings', 'route' => 'admin.settings.index', 'match' => 'admin.settings.*', 'query' => ['tab' => 'api'], 'icon' => 'code', 'permission' => 'view_settings'];
    $settingItems[] = ['label' => 'admin.sidebar.advanced_settings', 'route' => 'admin.settings.index', 'match' => 'admin.settings.*', 'query' => ['tab' => 'advanced'], 'icon' => 'terminal', 'permission' => 'view_settings'];

    $navGroups[] = [
        'label' => 'admin.sidebar.group.settings',
        'icon' => 'settings',
        'items' => $settingItems,
    ];

    if (auth()->check() && !auth()->user()->hasRole('super_admin')) {
        foreach ($navGroups as $groupIndex => &$group) {
            foreach ($group['items'] as $itemIndex => &$item) {
                if (isset($item['children'])) {
                    foreach ($item['children'] as $childIndex => $child) {
                        $matchFirst = is_array($child['match']) ? $child['match'][0] : $child['match'];
                        $childPerms = isset($child['permission'])
                            ? (is_array($child['permission']) ? $child['permission'] : [$child['permission']])
                            : ['view_' . str_replace(['admin.', '.*'], '', $matchFirst)];

                        $hasChildPerm = false;
                        foreach ($childPerms as $perm) {
                            try {
                                if (auth()->user()->hasPermissionTo($perm)) {
                                    $hasChildPerm = true;
                                    break;
                                }
                            } catch (\Exception $e) {}
                        }
                        if (!$hasChildPerm) {
                            unset($item['children'][$childIndex]);
                        }
                    }
                    if (empty($item['children'])) {
                        unset($group['items'][$itemIndex]);
                    }
                } else {
                    $matchFirst = is_array($item['match']) ? $item['match'][0] : $item['match'];
                    $itemPerms = isset($item['permission'])
                        ? (is_array($item['permission']) ? $item['permission'] : [$item['permission']])
                        : ['view_' . str_replace(['admin.', '.*'], '', $matchFirst)];

                    $hasItemPerm = false;
                    foreach ($itemPerms as $perm) {
                        try {
                            if (auth()->user()->hasPermissionTo($perm)) {
                                $hasItemPerm = true;
                                break;
                            }
                        } catch (\Exception $e) {}
                    }
                    if (!$hasItemPerm) {
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
        $route = $item['route'];
        if (isset($item['routes_by_permission']) && auth()->check() && !auth()->user()->hasRole('super_admin')) {
            foreach ($item['routes_by_permission'] as $perm => $targetRoute) {
                try {
                    if (auth()->user()->hasPermissionTo($perm)) {
                        $route = $targetRoute;
                        break;
                    }
                } catch (\Exception $e) {}
            }
        }
        return isset($item['query'])
            ? route($route, $item['query'])
            : route($route);
    };

    $isItemActive = function (array $item): bool {
        $matches = is_array($item['match']) ? $item['match'] : [$item['match']];
        if (!request()->routeIs(...$matches)) return false;
        if (isset($item['query'])) {
            foreach ($item['query'] as $k => $v) {
                $actual = request()->route($k) ?? request()->query($k);
                if ($k === 'tab' && $actual === null && request()->routeIs('admin.settings.*')) {
                    $actual = 'general';
                }
                if ($actual !== $v) return false;
            }
        } elseif (request()->routeIs('admin.settings.*') && request()->query('tab')) {
            return false;
        }
        return true;
    };

    $renderIcon = function (string $name): string {
        $icons = [
            'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>',
            'home' => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
            'box' => '<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
            'package' => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
            'folder-tree' => '<path d="M20 10a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1h-2.5a1 1 0 0 1-.8-.4l-.9-1.2A1 1 0 0 0 15 3h-2a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1Z"/><path d="M20 21a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1h-2.9a1 1 0 0 1-.88-.55l-.42-.85a1 1 0 0 0-.92-.6H13a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1Z"/><path d="M3 5a2 2 0 0 0 2 2h3"/><path d="M3 3v13a2 2 0 0 0 2 2h3"/>',
            'award' => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>',
            'shopping-bag' => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
            'receipt' => '<path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/>',
            'rotate-ccw' => '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>',
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'file-text' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>',
            'layers' => '<path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/>',
            'layout' => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/>',
            'menu' => '<line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>',
            'list' => '<line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/>',
            'message-square' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
            'image' => '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>',
            'briefcase' => '<rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
            'grid' => '<rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>',
            'shield' => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>',
            'activity' => '<path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"/>',
            'credit-card' => '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>',
            'alert-triangle' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
            'cpu' => '<rect width="16" height="16" x="4" y="4" rx="2"/><rect width="6" height="6" x="9" y="9" rx="1"/><path d="M15 2v2"/><path d="M15 20v2"/><path d="M2 15h2"/><path d="M2 9h2"/><path d="M20 15h2"/><path d="M20 9h2"/><path d="M9 2v2"/><path d="M9 20v2"/>',
            'terminal' => '<polyline points="4 17 10 11 4 5"/><line x1="12" x2="20" y1="19" y2="19"/>',
            'settings' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
            'sliders' => '<line x1="4" x2="4" y1="21" y2="14"/><line x1="4" x2="4" y1="10" y2="3"/><line x1="12" x2="12" y1="21" y2="12"/><line x1="12" x2="12" y1="8" y2="3"/><line x1="20" x2="20" y1="21" y2="16"/><line x1="20" x2="20" y1="12" y2="3"/><line x1="2" x2="6" y1="14" y2="14"/><line x1="10" x2="14" y1="8" y2="8"/><line x1="18" x2="22" y1="16" y2="16"/>',
            'megaphone' => '<path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
            'trending-up' => '<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>',
            'shuffle' => '<path d="M2 18h1.4c1.3 0 2.5-.6 3.3-1.7l6.1-8.6c.7-1.1 2-1.7 3.3-1.7H22"/><path d="m18 2 4 4-4 4"/><path d="M2 6h1.9c1.5 0 2.9.9 3.6 2.2"/><path d="M22 18h-5.9c-1.3 0-2.6-.7-3.3-1.8l-.5-.8"/><path d="m18 14 4 4-4 4"/>',
            'star' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
            'tag' => '<path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/>',
            'warehouse' => '<path d="M22 8.35V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8.35A2 2 0 0 1 3.26 6.5l8-3.2a2 2 0 0 1 1.48 0l8 3.2A2 2 0 0 1 22 8.35Z"/><path d="M6 18h12"/><path d="M6 14h12"/>',
            'clipboard-list' => '<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>',
            'store' => '<path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/>',
            'pen-tool' => '<path d="m12 19 7-7 3 3-7 7-3-3z"/><path d="m18 13-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="m2 2 7.586 7.586"/><circle cx="11" cy="11" r="2"/>',
            'palette' => '<circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/>',
            'truck' => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18.5" r="2.5"/><circle cx="7" cy="18.5" r="2.5"/>',
            'percent' => '<line x1="19" x2="5" y1="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>',
            'lock' => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
            'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
            'webhook' => '<path d="M18 16.98h-5.99c-1.1 0-1.95.94-2.48 1.9A4 4 0 0 1 2 17c0-2.21 1.79-4 4-4h5.5"/><circle cx="12" cy="5" r="3"/><circle cx="20" cy="17" r="2"/><circle cx="4" cy="17" r="2"/>',
            'code' => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
        ];
        $svgBody = $icons[$name] ?? $icons['grid'];
        return '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $svgBody . '</svg>';
    };

    $configuredColor = app(\App\Settings\DBSettings::class)->primary_color ?? '#004687';
    $resolvedPrimaryColor = ($configuredColor === '#d97706' || empty($configuredColor)) ? '#004687' : $configuredColor;

    // Resolve active section for breadcrumb
    $activeGroupLabel = null;
    foreach ($navGroups as $g) {
        foreach ($g['items'] as $it) {
            if ($isItemActive($it)) {
                $activeGroupLabel = __($g['label']);
                break 2;
            }
        }
    }
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? __('admin.brand.title') }} — NovaStore Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            /* Surface & Canvas Tokens (Shopify / Linear / Stripe Enterprise Palette) */
            --fi-bg: #f4f6f8;
            --fi-panel: #ffffff;
            --fi-soft: #f8fafc;
            --fi-soft-hover: #f1f5f9;
            --fi-border: #e2e8f0;
            --fi-border-subtle: #edf2f7;
            --fi-border-strong: #cbd5e1;

            /* Typography & Ink Tokens */
            --fi-text: #0f172a;
            --fi-text-secondary: #334155;
            --fi-muted: #64748b;
            --fi-faint: #94a3b8;

            /* Brand & Accent Tokens */
            --fi-navy-dark: #091e34;
            --fi-sidebar-bg: #0b1727;
            --fi-sidebar-surface: #132238;
            --fi-sidebar-border: rgba(255, 255, 255, 0.07);
            --fi-sidebar-text: #94a3b8;
            --fi-sidebar-text-active: #ffffff;
            --fi-gold: #fbae17;
            --fi-primary: {{ $resolvedPrimaryColor }};
            --fi-primary-strong: color-mix(in srgb, var(--fi-primary) 82%, black);
            --fi-primary-soft: color-mix(in srgb, var(--fi-primary) 8%, #ffffff);
            --fi-primary-border: color-mix(in srgb, var(--fi-primary) 24%, transparent);

            /* Semantic Status Tokens */
            --fi-danger: #dc2626;
            --fi-danger-bg: #fef2f2;
            --fi-danger-border: #fecaca;
            --fi-success: #059669;
            --fi-success-bg: #ecfdf5;
            --fi-success-border: #a7f3d0;
            --fi-warning: #d97706;
            --fi-warning-bg: #fffbeb;
            --fi-warning-border: #fde68a;
            --fi-info: #0284c7;
            --fi-info-bg: #f0f9ff;
            --fi-info-border: #bae6fd;

            /* Elevation & Radii */
            --fi-radius-sm: 6px;
            --fi-radius-md: 8px;
            --fi-radius-lg: 10px;
            --fi-radius-xl: 12px;
            --fi-ring: 0 1px 2px 0 rgba(15, 23, 42, 0.04);
            --fi-shadow-sm: 0 1px 3px 0 rgba(15, 23, 42, 0.06), 0 1px 2px -1px rgba(15, 23, 42, 0.04);
            --fi-shadow-md: 0 4px 12px -2px rgba(15, 23, 42, 0.08), 0 2px 4px -2px rgba(15, 23, 42, 0.04);
            --fi-font-sans: "Be Vietnam Pro", "DM Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            --fi-font-mono: "JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        [x-cloak] { display: none !important; }
        * { box-sizing: border-box; }
        ::selection {
            background: var(--fi-primary);
            color: #ffffff;
        }
        html, body {
            margin: 0;
            padding: 0;
            background: var(--fi-bg);
            color: var(--fi-text);
            font-family: var(--fi-font-sans);
            font-size: 13px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        a { color: inherit; text-decoration: none; }
        .tabular-nums, .value, .stat, table td, .badge, .pagination, .mono {
            font-variant-numeric: tabular-nums;
        }
        .mono, code {
            font-family: var(--fi-font-mono);
        }

        /* =========================================================
           1. APPLICATION SHELL & COLLAPSIBLE ENTERPRISE SIDEBAR
           ========================================================= */
        .admin-shell {
            min-height: 100vh;
            display: flex;
            background: var(--fi-bg);
        }
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(9, 30, 52, 0.45);
            backdrop-filter: blur(2px);
            z-index: 45;
        }
        .sidebar {
            width: 256px;
            flex: 0 0 256px;
            background: var(--fi-sidebar-bg);
            color: var(--fi-sidebar-text);
            border-right: 1px solid #091220;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 50;
            transition: width 0.2s cubic-bezier(0.16, 1, 0.3, 1), flex-basis 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }
        .admin-shell.sidebar-collapsed .sidebar {
            width: 68px;
            flex: 0 0 68px;
        }

        /* Brand Header inside Sidebar */
        .brand {
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 0 14px;
            border-bottom: 1px solid var(--fi-sidebar-border);
            flex-shrink: 0;
        }
        .brand-identity {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            overflow: hidden;
        }
        .brand-mark {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: linear-gradient(180deg, #163354 0%, #0d223a 100%);
            border: 1px solid rgba(251, 174, 23, 0.35);
            color: var(--fi-gold);
            font-weight: 800;
            font-size: 14px;
            flex-shrink: 0;
            letter-spacing: -0.02em;
        }
        .brand-text {
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            transition: opacity 0.15s ease;
        }
        .admin-shell.sidebar-collapsed .brand-text {
            opacity: 0;
            width: 0;
            pointer-events: none;
        }
        .brand-title {
            font-weight: 700;
            font-size: 13.5px;
            color: #ffffff;
            letter-spacing: -0.01em;
            line-height: 1.2;
        }
        .brand-subtitle {
            color: #64748b;
            font-size: 11px;
            font-weight: 500;
            margin-top: 1px;
        }
        .brand-badge {
            font-family: var(--fi-font-mono);
            font-size: 10px;
            font-weight: 600;
            color: var(--fi-gold);
            background: rgba(251, 174, 23, 0.12);
            border: 1px solid rgba(251, 174, 23, 0.24);
            padding: 2px 6px;
            border-radius: 4px;
            flex-shrink: 0;
        }
        .admin-shell.sidebar-collapsed .brand-badge {
            display: none;
        }

        /* Sidebar Scrollable Nav Body */
        .sidebar-nav-scroll {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 12px 10px;
        }
        .sidebar-nav-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-nav-scroll::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.14); border-radius: 4px; }

        .sidebar-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.08);
            margin: 10px 4px;
        }

        .nav-group {
            margin-bottom: 4px;
        }
        .nav-group-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 10px 5px;
            color: #64748b;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .admin-shell.sidebar-collapsed .nav-group-label {
            justify-content: center;
            padding: 4px 0;
            font-size: 0;
        }
        .admin-shell.sidebar-collapsed .nav-group-label::after {
            content: "";
            width: 18px;
            height: 1px;
            background: var(--fi-sidebar-border);
        }

        .nav-section {
            margin: 0;
        }
        .nav-summary {
            list-style: none;
            cursor: pointer;
            user-select: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 9px;
            min-height: 34px;
            padding: 6px 10px;
            border-radius: 7px;
            color: #94a3b8;
            font-size: 12.5px;
            font-weight: 600;
            transition: background 0.14s ease, color 0.14s ease;
            white-space: nowrap;
        }
        .nav-summary::-webkit-details-marker { display: none; }
        .nav-summary:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
        }
        .nav-summary-left {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }
        .nav-icon {
            width: 16px;
            height: 16px;
            flex: 0 0 16px;
            color: #64748b;
            transition: color 0.14s ease;
        }
        .nav-summary:hover .nav-icon,
        .nav-link:hover .nav-icon {
            color: #cbd5e1;
        }
        .nav-caret {
            width: 5px;
            height: 5px;
            border-right: 1.5px solid #64748b;
            border-bottom: 1.5px solid #64748b;
            transform: rotate(-45deg);
            transition: transform 0.16s ease, border-color 0.16s ease;
            flex: 0 0 auto;
            margin-right: 2px;
        }
        .nav-section[open] > .nav-summary {
            color: #e2e8f0;
        }
        .nav-section[open] > .nav-summary .nav-caret {
            transform: rotate(45deg);
            border-color: #cbd5e1;
        }
        .nav-subitems {
            display: grid;
            gap: 2px;
            padding-left: 12px;
            margin-left: 17px;
            margin-top: 2px;
            border-left: 1px solid rgba(255, 255, 255, 0.08);
        }
        .admin-shell.sidebar-collapsed .nav-subitems {
            padding-left: 0;
            margin-left: 0;
            border-left: 0;
        }
        .admin-shell.sidebar-collapsed .nav-summary span:not(.nav-summary-left),
        .admin-shell.sidebar-collapsed .nav-summary-left span,
        .admin-shell.sidebar-collapsed .nav-caret,
        .admin-shell.sidebar-collapsed .nav-link-label,
        .admin-shell.sidebar-collapsed .nav-dot {
            display: none;
        }
        .admin-shell.sidebar-collapsed .nav-summary,
        .admin-shell.sidebar-collapsed .nav-link {
            justify-content: center;
            padding: 8px;
        }

        .nav-link {
            min-height: 33px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 9px;
            padding: 5px 10px;
            border-radius: 7px;
            text-decoration: none;
            color: #94a3b8;
            font-size: 12.5px;
            font-weight: 500;
            transition: background 0.14s ease, color 0.14s ease;
            position: relative;
            white-space: nowrap;
        }
        .nav-link-left {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
            overflow: hidden;
        }
        .nav-link-label {
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .nav-link:hover {
            background: rgba(255, 255, 255, 0.06);
            color: #f8fafc;
        }
        .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            font-weight: 600;
            box-shadow: inset 2px 0 0 0 var(--fi-gold);
        }
        .nav-link.active .nav-icon {
            color: var(--fi-gold);
        }
        .nav-dot {
            width: 5px;
            height: 5px;
            border-radius: 999px;
            background: transparent;
            flex: 0 0 auto;
        }
        .nav-link.active .nav-dot {
            background: var(--fi-gold);
        }

        /* Sidebar Footer Profile Card */
        .sidebar-footer {
            padding: 12px 14px;
            border-top: 1px solid var(--fi-sidebar-border);
            background: rgba(0, 0, 0, 0.18);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-shrink: 0;
        }
        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }
        .sidebar-avatar {
            width: 30px;
            height: 30px;
            border-radius: 7px;
            background: #1e3a5f;
            border: 1px solid rgba(255, 255, 255, 0.14);
            color: #ffffff;
            font-weight: 700;
            font-size: 12px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }
        .sidebar-user-meta {
            min-width: 0;
            overflow: hidden;
        }
        .sidebar-user-name {
            font-size: 12px;
            font-weight: 600;
            color: #f8fafc;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-user-role {
            font-size: 10.5px;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .admin-shell.sidebar-collapsed .sidebar-user-meta {
            display: none;
        }

        /* =========================================================
           2. TOPBAR & BREADCRUMB NAVIGATION
           ========================================================= */
        .main {
            min-width: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            height: 56px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 0 28px;
            background: #ffffff;
            border-bottom: 1px solid var(--fi-border-strong);
            position: sticky;
            top: 0;
            z-index: 30;
        }
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .sidebar-toggle-btn {
            width: 32px;
            height: 32px;
            border-radius: 7px;
            border: 1px solid var(--fi-border-strong);
            background: #ffffff;
            color: #334155;
            display: inline-grid;
            place-items: center;
            cursor: pointer;
            transition: background 0.14s ease, color 0.14s ease, border-color 0.14s ease;
            flex-shrink: 0;
        }
        .sidebar-toggle-btn:hover {
            background: var(--fi-soft);
            color: var(--fi-text);
            border-color: #94a3b8;
        }
        .breadcrumb-trail {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 12.5px;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
        }
        .breadcrumb-root {
            color: var(--fi-muted);
            font-weight: 500;
            transition: color 0.14s ease;
        }
        .breadcrumb-root:hover {
            color: var(--fi-text);
        }
        .breadcrumb-sep {
            color: #cbd5e1;
            font-size: 12px;
        }
        .breadcrumb-current {
            font-weight: 700;
            color: var(--fi-text);
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .lang-segmented {
            display: inline-flex;
            align-items: center;
            background: #f1f5f9;
            padding: 2px;
            border-radius: 7px;
            border: 1px solid var(--fi-border-strong);
        }
        .lang-segmented a {
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            line-height: 1;
            transition: background 0.14s ease, color 0.14s ease;
        }
        .lang-segmented a.active {
            background: #ffffff;
            color: var(--fi-text);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }
        .topbar-user-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 4px 10px 4px 5px;
            border-radius: 999px;
            border: 1px solid var(--fi-border);
            background: #ffffff;
            font-size: 12px;
            color: #334155;
            font-weight: 500;
        }
        .topbar-user-dot {
            width: 22px;
            height: 22px;
            border-radius: 999px;
            background: var(--fi-navy-dark);
            color: var(--fi-gold);
            font-weight: 700;
            font-size: 11px;
            display: grid;
            place-items: center;
        }

        /* Symmetrical Full-Width Page Container (Equal Left & Right Alignment) */
        .content {
            width: 100%;
            max-width: none;
            padding: 24px 28px 40px;
            margin: 0;
        }

        /* =========================================================
           3. PAGE HEADERS & ACTION BARS
           ========================================================= */
        .page-header, .actions-bar {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }
        .page-title {
            margin: 0;
            font-size: 19px;
            font-weight: 700;
            color: var(--fi-text);
            line-height: 1.25;
            letter-spacing: -0.02em;
        }
        .page-description {
            margin: 3px 0 0;
            color: var(--fi-muted);
            font-size: 12.5px;
            font-weight: 400;
        }
        .actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* =========================================================
           4. CARDS, PANELS & FLUSH TABLE CONTAINERS
           ========================================================= */
        .card {
            width: 100%;
            background: var(--fi-panel);
            border: 1px solid var(--fi-border-strong);
            border-radius: var(--fi-radius-lg);
            box-shadow: var(--fi-ring);
            padding: 18px 20px;
            margin-bottom: 18px;
        }
        .card:last-child {
            margin-bottom: 0;
        }
        .card-header {
            padding: 13px 20px;
            border-bottom: 1px solid var(--fi-border);
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: -18px -20px 16px -20px;
            border-radius: var(--fi-radius-lg) var(--fi-radius-lg) 0 0;
        }
        .card-title, .card-header h3 {
            margin: 0;
            font-size: 13.5px;
            font-weight: 700;
            color: var(--fi-text);
            letter-spacing: -0.01em;
        }
        .card-body {
            padding: 0;
        }
        .card-header + .card-body {
            padding-top: 2px;
        }

        /* Flush Table Panel (Shopify / Linear Data Table Container) */
        .table-panel, .table-panel.card {
            width: 100%;
            background: var(--fi-panel);
            border: 1px solid var(--fi-border-strong);
            border-radius: var(--fi-radius-lg);
            box-shadow: var(--fi-ring);
            padding: 0;
            overflow: hidden;
            margin-bottom: 18px;
        }

        /* =========================================================
           5. BUTTONS & INTERACTIVE CONTROLS
           ========================================================= */
        .btn {
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 1px solid transparent;
            border-radius: 7px;
            padding: 6px 14px;
            background: var(--fi-primary);
            color: #ffffff;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            font-weight: 600;
            font-size: 12.5px;
            line-height: 1.2;
            white-space: nowrap;
            transition: background 0.14s ease, border-color 0.14s ease, color 0.14s ease, box-shadow 0.14s ease, transform 0.08s ease;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        }
        .btn:hover {
            background: var(--fi-primary-strong);
        }
        .btn:active {
            transform: translateY(0.5px);
        }
        .btn-primary {
            background: var(--fi-primary);
            color: #ffffff;
        }
        .btn-primary:hover {
            background: var(--fi-primary-strong);
        }
        .btn-secondary {
            background: #ffffff;
            color: #334155;
            border-color: var(--fi-border-strong);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .btn-secondary:hover {
            background: var(--fi-soft);
            color: var(--fi-text);
            border-color: #94a3b8;
        }
        .btn-danger {
            background: var(--fi-danger);
            color: #ffffff;
        }
        .btn-danger:hover {
            background: #b91c1c;
        }
        .btn-sm {
            min-height: 32px;
            padding: 5px 11px;
            font-size: 12px;
            border-radius: 6px;
        }

        .link-action {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            min-height: 28px;
            padding: 3px 9px;
            border-radius: 6px;
            color: #334155;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid var(--fi-border-strong);
            background: #ffffff;
            cursor: pointer;
            transition: background 0.14s ease, color 0.14s ease, border-color 0.14s ease;
        }
        .link-action:hover {
            background: var(--fi-soft);
            color: var(--fi-primary);
            border-color: var(--fi-primary);
        }
        .link-danger {
            color: var(--fi-danger);
            border-color: #fecaca;
            background: #fff;
        }
        .link-danger:hover {
            background: var(--fi-danger-bg);
            color: #b91c1c;
            border-color: #fca5a5;
        }

        /* =========================================================
           6. SEMANTIC STATUS BADGES
           ========================================================= */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 600;
            line-height: 1.35;
            border: 1px solid transparent;
            white-space: nowrap;
        }
        .badge::before {
            content: "";
            width: 5px;
            height: 5px;
            border-radius: 999px;
            background: currentColor;
            opacity: 0.8;
            flex-shrink: 0;
        }
        .badge-pending, .badge-warning, .bg-yellow {
            background: var(--fi-warning-bg);
            color: #b45309;
            border-color: var(--fi-warning-border);
        }
        .badge-new, .badge-processing, .badge-shipping, .badge-delivering, .badge-info, .bg-blue {
            background: var(--fi-info-bg);
            color: #0369a1;
            border-color: var(--fi-info-border);
        }
        .badge-completed, .badge-paid, .badge-success, .bg-green {
            background: var(--fi-success-bg);
            color: #047857;
            border-color: var(--fi-success-border);
        }
        .badge-cancelled, .badge-failed, .badge-danger, .bg-red {
            background: var(--fi-danger-bg);
            color: #b91c1c;
            border-color: var(--fi-danger-border);
        }
        .badge-refunded, .badge-secondary, .bg-gray {
            background: #f1f5f9;
            color: #475569;
            border-color: #cbd5e1;
        }

        /* =========================================================
           7. COMPACT SEARCH, DROPDOWN FILTERS & TABLE TOOLBARS
           ========================================================= */
        .list-filter-panel,
        .admin-table-toolbar {
            background: #f8fafc;
            border-bottom: 1px solid var(--fi-border-strong);
            padding: 12px 16px;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
        }
        .list-filter-grid,
        .admin-toolbar-filters {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 10px;
            flex: 1 1 auto;
        }
        .list-filter-grid > div,
        .admin-filter-item {
            display: flex;
            flex-direction: column;
            min-width: 165px;
            max-width: 220px;
        }
        .list-filter-grid label,
        .admin-filter-item label,
        .admin-search-wrap label {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 4px;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .list-filter-actions,
        .admin-toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
            margin: 0;
            padding: 0;
            border: 0;
        }

        /* Compact Search Input with Built-in Magnifying Glass Icon */
        .admin-search-wrap,
        .searchbar {
            position: relative;
            width: 250px;
            max-width: 100%;
            flex: 0 0 250px;
            margin: 0;
        }
        .admin-search-wrap input,
        .admin-search-wrap input[type="text"],
        .admin-search-wrap input[type="search"],
        .searchbar input {
            width: 100%;
            height: 36px;
            min-height: 36px;
            padding-left: 36px !important;
            padding-right: 10px;
            background-color: #ffffff;
            border: 1px solid var(--fi-border-strong);
            border-radius: 7px;
            font-size: 12.5px;
        }
        .admin-search-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            width: 15px;
            height: 15px;
            color: #64748b;
            pointer-events: none;
            z-index: 2;
        }
        .searchbar::before {
            content: "";
            width: 12px;
            height: 12px;
            border: 1.75px solid #64748b;
            border-radius: 999px;
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-55%);
            pointer-events: none;
        }
        .searchbar::after {
            content: "";
            width: 5px;
            height: 1.75px;
            background: #64748b;
            position: absolute;
            left: 21px;
            top: 50%;
            margin-top: 4px;
            transform: rotate(45deg);
            pointer-events: none;
        }

        /* Separated Import CSV Bar (Distinct from Export CSV & Image Upload) */
        .crud-import-row,
        .admin-import-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 9px 16px;
            background: #ffffff;
            border-bottom: 1px solid var(--fi-border);
            flex-wrap: wrap;
        }
        .admin-import-title {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
        }
        .admin-import-badge {
            font-family: var(--fi-font-mono);
            font-size: 10.5px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }
        .import-form {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            margin: 0;
        }
        .custom-file-picker {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 32px;
            padding: 0 11px;
            border: 1px dashed #94a3b8;
            border-radius: 6px;
            background: #f8fafc;
            color: #334155;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: border-color 0.14s ease, background 0.14s ease;
            margin: 0;
        }
        .custom-file-picker:hover {
            border-color: var(--fi-primary);
            background: #f0f7ff;
            color: var(--fi-primary);
        }
        .custom-file-picker input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            margin: 0;
            padding: 0;
        }
        .import-error { margin: 8px 16px; }

        /* =========================================================
           8. FORM CONTROLS, DROPDOWNS & FILE UPLOAD DROPZONE
           ========================================================= */
        label, .form-label {
            display: block;
            color: #334155;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
        select,
        textarea,
        .input,
        .form-control {
            width: 100%;
            min-height: 36px;
            border: 1px solid var(--fi-border-strong);
            border-radius: 7px;
            background-color: #ffffff;
            color: var(--fi-text);
            padding: 6px 11px;
            font: inherit;
            font-size: 13px;
            outline: none;
            transition: border-color 0.14s ease, box-shadow 0.14s ease, background-color 0.14s ease;
        }
        /* Explicit Chevron Dropdown Icon on all Select Controls */
        select:not([multiple]) {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            height: 36px;
            padding-right: 34px;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23334155' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 15px 15px;
        }
        select:not([multiple]):hover {
            border-color: #94a3b8;
            background-color: #fcfcfd;
        }
        /* Sensible widths for dropdowns and date inputs so they don't stretch indefinitely */
        .form-row select:not([multiple]):not(.form-control-full) {
            max-width: 380px;
        }
        .form-row input[type="date"],
        .form-row input[type="datetime-local"] {
            max-width: 240px;
        }
        .show-sidebar .form-row select:not([multiple]),
        .show-sidebar .form-row input[type="date"],
        .show-sidebar .form-row input[type="datetime-local"] {
            max-width: 100%;
        }
        input[type="date"] {
            height: 36px;
            min-width: 145px;
        }
        input[type="checkbox"], input[type="radio"] {
            width: 15px;
            height: 15px;
            min-height: 15px;
            padding: 0;
            accent-color: var(--fi-primary);
            cursor: pointer;
        }
        input:focus, select:focus, textarea:focus, .input:focus, .form-control:focus {
            border-color: var(--fi-primary);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--fi-primary) 14%, transparent);
        }
        textarea, textarea.form-control { min-height: 100px; resize: vertical; padding: 8px 11px; }
        .input-sm { min-height: 32px; height: 32px; padding: 4px 9px; font-size: 12px; }

        /* Dedicated File Upload Dropzone Component */
        .admin-upload-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 16px;
            border: 1.5px dashed #94a3b8;
            border-radius: 8px;
            background: #f8fafc;
            transition: border-color 0.15s ease, background 0.15s ease;
            flex-wrap: wrap;
        }
        .admin-upload-box:hover {
            border-color: var(--fi-primary);
            background: #f0f7ff;
        }
        .admin-upload-info {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .admin-upload-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: #ffffff;
            border: 1px solid var(--fi-border-strong);
            color: var(--fi-primary);
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }
        .admin-upload-text strong {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--fi-text);
        }
        .admin-upload-text span {
            display: block;
            font-size: 11.5px;
            color: var(--fi-muted);
            margin-top: 2px;
        }
        .admin-upload-trigger {
            position: relative;
            overflow: hidden;
        }
        .admin-upload-trigger input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        /* CKEditor 5 Editorial Studio Styling */
        .ck.ck-editor {
            width: 100% !important;
        }
        .ck.ck-toolbar {
            background: #f8fafc !important;
            border: 1px solid var(--fi-border-strong) !important;
            border-bottom: 1px solid var(--fi-border) !important;
            border-radius: 8px 8px 0 0 !important;
            padding: 4px 8px !important;
        }
        .ck.ck-editor__main > .ck-editor__editable {
            min-height: 320px !important;
            max-height: 640px;
            padding: 14px 18px !important;
            background: #ffffff !important;
            border: 1px solid var(--fi-border-strong) !important;
            border-top: 0 !important;
            border-radius: 0 0 8px 8px !important;
            font-size: 13.5px;
            line-height: 1.65;
            color: var(--fi-text);
            box-shadow: none !important;
        }
        .ck.ck-editor__main > .ck-editor__editable.ck-focused {
            border-color: var(--fi-primary) !important;
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--fi-primary) 12%, transparent) !important;
        }

        /* Featured Image Sidebar Studio Card */
        .featured-media-card {
            border: 1.5px dashed #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            padding: 12px;
            text-align: center;
            transition: border-color 0.15s ease, background 0.15s ease;
        }
        .featured-media-card:hover {
            border-color: var(--fi-primary);
            background: #f0f7ff;
        }
        .featured-media-frame {
            width: 100%;
            height: 180px;
            border-radius: 7px;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid var(--fi-border);
            display: grid;
            place-items: center;
            margin-bottom: 10px;
            position: relative;
        }
        .featured-media-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .featured-media-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            color: #64748b;
            padding: 16px;
        }

        /* Tag Pill Selector & Tree Selector */
        .tag-pills-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            padding: 12px;
            border: 1px solid var(--fi-border-strong);
            border-radius: 8px;
            background: #f8fafc;
            max-height: 240px;
            overflow-y: auto;
        }
        .tag-pill-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 11px;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            margin: 0;
            user-select: none;
            transition: all 0.14s ease;
        }
        .tag-pill-label:hover {
            border-color: var(--fi-primary);
            color: var(--fi-primary);
        }
        .tag-pill-label:has(input:checked) {
            background: var(--fi-primary);
            border-color: var(--fi-primary);
            color: #ffffff;
            box-shadow: 0 1px 2px rgba(0, 70, 135, 0.2);
        }
        .tag-pill-label input[type="checkbox"] {
            width: 13px;
            height: 13px;
            min-height: 13px;
            margin: 0;
            accent-color: #ffffff;
        }

        .tree-select-wrapper {
            max-height: 260px;
            overflow-y: auto;
            border: 1px solid var(--fi-border-strong);
            padding: 10px 12px;
            border-radius: 8px;
            background: #f8fafc;
        }
        .seo-snippet-preview {
            padding: 12px 14px;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid var(--fi-border);
            margin-bottom: 14px;
        }
        .seo-snippet-url {
            font-size: 11.5px;
            color: #0f766e;
            font-family: var(--fi-font-mono);
            margin-bottom: 2px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .seo-snippet-title {
            font-size: 15px;
            font-weight: 700;
            color: #1d4ed8;
            line-height: 1.3;
            margin-bottom: 3px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .seo-snippet-desc {
            font-size: 12px;
            color: #475569;
            line-height: 1.45;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .form-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .form-row { margin-bottom: 0; }
        .form-row-wide { grid-column: 1 / -1; }
        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 8px;
            border: 1px solid var(--fi-border);
            border-radius: 8px;
            background: #f8fafc;
            padding: 12px;
            max-height: 360px;
            overflow: auto;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            border: 1px solid var(--fi-border);
            border-radius: 7px;
            background: #ffffff;
            color: #334155;
            padding: 7px 10px;
            font-size: 12.5px;
            font-weight: 500;
            cursor: pointer;
        }
        .checkbox-item:hover {
            border-color: var(--fi-border-strong);
            background: #fcfcfd;
        }
        .form-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            border-top: 1px solid var(--fi-border);
            margin-top: 20px;
            padding-top: 16px;
        }
        .error {
            color: var(--fi-danger);
            font-size: 12px;
            margin-top: 4px;
            font-weight: 500;
        }
        .status-message {
            border: 1px solid var(--fi-success-border);
            background: var(--fi-success-bg);
            color: #047857;
            padding: 10px 14px;
            border-radius: 8px;
            margin: 0 0 16px;
            font-size: 12.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Show & Edit 2-Column Enterprise Layouts */
        .show-layout, .edit-layout {
            width: 100%;
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 20px;
        }
        @media (min-width: 1024px) {
            .show-layout, .edit-layout {
                grid-template-columns: minmax(0, 2fr) minmax(340px, 1fr);
                align-items: start;
            }
        }
        .split-grid-2col {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            align-items: start;
        }
        @media (max-width: 1024px) {
            .split-grid-2col {
                grid-template-columns: 1fr;
            }
        }
        .field-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0;
        }
        @media (min-width: 640px) {
            .field-list-2col {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px 20px;
            }
        }
        .field-group {
            padding: 11px 0;
            border-bottom: 1px solid var(--fi-border-subtle);
        }
        .field-group:last-child { border-bottom: none; }
        .field-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--fi-muted);
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .field-value {
            font-size: 13.5px;
            color: var(--fi-text);
            line-height: 1.5;
            word-break: break-word;
        }
        .preview-img {
            max-height: 140px;
            border-radius: 7px;
            object-fit: contain;
            background: #ffffff;
            padding: 4px;
            border: 1px solid var(--fi-border-strong);
            max-width: 100%;
        }
        .val-empty { color: #94a3b8; font-style: italic; font-size: 12.5px; }
        .val-html {
            background: #f8fafc;
            padding: 12px 14px;
            border-radius: 8px;
            border: 1px solid var(--fi-border);
            overflow-x: auto;
            margin-top: 6px;
            font-size: 13px;
        }

        /* =========================================================
           9. ENTERPRISE DATA TABLES (HIGH CLARITY & SYMMETRIC WIDTH)
           ========================================================= */
        .table-wrap {
            width: 100%;
            overflow-x: auto;
            border-radius: var(--fi-radius-md);
            border: 1px solid var(--fi-border-strong);
            background: #ffffff;
        }
        .table-panel > .table-wrap,
        .card > .table-wrap:last-child {
            border: 0;
            border-radius: 0;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            text-align: left;
        }
        thead th {
            background: #f1f5f9;
            color: #334155;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 700;
            text-align: left;
            border-bottom: 1px solid var(--fi-border-strong);
            border-right: 1px solid #e2e8f0;
            padding: 11px 16px;
            white-space: nowrap;
            user-select: none;
        }
        thead th:last-child {
            border-right: 0;
        }
        tbody td, tbody th {
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #f1f5f9;
            padding: 11px 16px;
            vertical-align: middle;
            font-size: 13px;
            color: var(--fi-text-secondary);
            transition: background 0.1s ease;
        }
        tbody td:last-child, tbody th:last-child {
            border-right: 0;
        }
        tbody tr:nth-child(even) td {
            background: #fafbfc;
        }
        tbody tr:last-child td, tbody tr:last-child th { border-bottom: 0; }
        tbody tr:hover td { background: #f0f7ff; }
        .cell-muted { color: var(--fi-muted); font-size: 12px; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .font-bold { font-weight: 600 !important; color: var(--fi-text); }

        /* =========================================================
           10. KPI METRICS, CHARTS & STRUCTURED TABS ("TAB RA TAB")
           ========================================================= */
        .stats {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 18px;
        }
        .stat {
            border: 1px solid var(--fi-border-strong);
            border-radius: var(--fi-radius-lg);
            background: #ffffff;
            padding: 16px 18px;
            box-shadow: var(--fi-ring);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .stat .label {
            color: var(--fi-muted);
            font-size: 11.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .stat .value {
            font-size: 22px;
            line-height: 1.2;
            font-weight: 800;
            color: var(--fi-text);
            margin-top: 6px;
            letter-spacing: -0.025em;
        }
        .dashboard-grid {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-top: 18px;
        }
        .metric-list { display: grid; gap: 7px; }
        .metric-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border: 1px solid var(--fi-border);
            border-radius: 8px;
            padding: 8px 12px;
            background: #ffffff;
        }
        .metric-row strong { font-size: 13.5px; color: var(--fi-text); }

        /* Structured Enterprise Tabs ("Tab ra Tab") */
        .tabs-header,
        .admin-tabs-bar {
            display: flex;
            align-items: flex-end;
            gap: 6px;
            padding: 10px 16px 0;
            border-bottom: 1px solid var(--fi-border-strong);
            background: #f1f5f9;
            border-radius: var(--fi-radius-lg) var(--fi-radius-lg) 0 0;
            overflow-x: auto;
        }
        .workspace-tab-btn,
        .admin-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            border: 1px solid transparent;
            border-bottom: none;
            background: rgba(255, 255, 255, 0.55);
            cursor: pointer;
            font-family: inherit;
            font-size: 12.5px;
            font-weight: 600;
            color: #475569;
            white-space: nowrap;
            border-radius: 8px 8px 0 0;
            position: relative;
            bottom: -1px;
            transition: color 0.14s ease, background 0.14s ease, border-color 0.14s ease;
            text-decoration: none;
        }
        .workspace-tab-btn:hover,
        .admin-tab-btn:hover {
            color: var(--fi-text);
            background: rgba(255, 255, 255, 0.9);
        }
        .workspace-tab-btn.active,
        .admin-tab-btn.active {
            color: var(--fi-primary);
            font-weight: 700;
            background: #ffffff;
            border-color: var(--fi-border-strong);
            border-bottom: 1px solid #ffffff;
            box-shadow: inset 0 2.5px 0 0 var(--fi-primary);
        }
        .admin-tab-badge {
            display: inline-grid;
            place-items: center;
            min-width: 20px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            font-size: 10.5px;
            font-weight: 700;
            font-family: var(--fi-font-mono);
            background: #e2e8f0;
            color: #475569;
        }
        .workspace-tab-btn.active .admin-tab-badge,
        .admin-tab-btn.active .admin-tab-badge {
            background: var(--fi-primary);
            color: #ffffff;
        }

        /* Segmented View Switcher (e.g., Grid / Table) */
        .segmented-tabs {
            display: inline-flex;
            align-items: center;
            background: #e2e8f0;
            padding: 3px;
            border-radius: 8px;
            gap: 2px;
        }
        .segmented-tab {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            text-decoration: none;
            transition: background 0.14s ease, color 0.14s ease, box-shadow 0.14s ease;
        }
        .segmented-tab:hover {
            color: var(--fi-text);
        }
        .segmented-tab.active {
            background: #ffffff;
            color: var(--fi-primary);
            font-weight: 700;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.1);
        }

        /* =========================================================
           11. AUTH LOGIN, SHIELD ROLES, SETTINGS & MEDIA
           ========================================================= */
        .login-wrap {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: radial-gradient(circle at 50% 0%, #e2e8f0 0%, #f4f6f8 65%);
        }
        .login-card { width: min(100%, 420px); padding: 28px; }
        .section-title { margin: 0 0 12px; font-size: 14.5px; font-weight: 700; color: var(--fi-text); }
        .stack { display: grid; gap: 12px; }
        .inline-form { display: grid; gap: 10px; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); align-items: end; }

        .panel-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 14px;
        }
        .panel-heading h2 {
            margin: 0;
            font-size: 14.5px;
            font-weight: 700;
            color: var(--fi-text);
            line-height: 1.35;
            letter-spacing: -0.01em;
        }
        .panel-heading p { margin: 2px 0 0; color: var(--fi-muted); font-size: 12px; }

        /* Shield / Roles & Permissions */
        .shield-form { display: grid; gap: 16px; }
        .shield-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 260px;
            gap: 16px;
            align-items: stretch;
        }
        .shield-card { min-height: 100%; margin-bottom: 0; }
        .shield-summary {
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: var(--fi-navy-dark);
            color: #ffffff;
        }
        .shield-summary .value { color: var(--fi-gold); font-size: 32px; font-weight: 800; }
        .shield-summary .label, .shield-summary .page-description { color: rgba(255, 255, 255, 0.72); }
        .compact-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .shield-permissions { padding: 0; overflow: hidden; }
        .shield-heading {
            padding: 14px 18px;
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
            padding: 10px 18px;
            background: #f8fafc;
            color: var(--fi-muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .shield-resource-title {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            color: var(--fi-text);
            font-size: 12.5px;
            cursor: pointer;
        }
        .shield-action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 10px;
            padding: 14px 18px;
        }
        .shield-action-cell {
            display: grid;
            gap: 6px;
            align-content: start;
            border: 1px solid var(--fi-border);
            border-radius: 8px;
            background: #ffffff;
            padding: 10px;
        }
        .shield-action-name {
            color: var(--fi-primary);
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .shield-permission-pill {
            display: flex;
            align-items: center;
            gap: 7px;
            margin: 0;
            border: 1px solid transparent;
            border-radius: 6px;
            padding: 5px 8px;
            color: #334155;
            font-size: 12px;
            font-weight: 500;
            background: #f8fafc;
            cursor: pointer;
            overflow-wrap: anywhere;
        }
        .shield-permission-pill:has(input:checked) {
            border-color: var(--fi-primary-border);
            background: var(--fi-primary-soft);
            color: var(--fi-primary-strong);
            font-weight: 600;
        }
        .shield-action-tag {
            font-weight: 600;
            font-size: 12px;
            color: #0f172a;
        }
        .shield-perm-code {
            font-family: var(--fi-font-mono);
            font-size: 11px;
            color: #64748b;
            margin-left: auto;
        }
        .user-role-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(165px, 1fr));
            gap: 8px;
            padding: 10px;
            border: 1px solid var(--fi-border);
            border-radius: 8px;
            background: #f8fafc;
        }
        .sticky-footer {
            position: sticky;
            bottom: 12px;
            z-index: 20;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            border: 1px solid var(--fi-border);
            border-radius: 10px;
            padding: 12px 16px;
            margin-top: 0;
            box-shadow: var(--fi-shadow-md);
        }

        /* Settings Shell (Shopify 2-Column Layout) */
        .settings-shell {
            display: grid;
            grid-template-columns: 240px minmax(0, 1fr);
            gap: 20px;
            align-items: start;
            min-width: 0;
            width: 100%;
        }
        .settings-tabs {
            position: sticky;
            top: 74px;
            display: grid;
            gap: 3px;
            padding: 8px;
            margin-bottom: 0;
            background: #ffffff;
            border-radius: var(--fi-radius-xl);
            border: 1px solid var(--fi-border);
            box-shadow: var(--fi-shadow-sm);
            min-width: 0;
            max-height: calc(100vh - 90px);
            overflow-y: auto;
        }
        .settings-nav-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 4px 8px 10px;
            border-bottom: 1px solid var(--fi-border-subtle);
            margin-bottom: 4px;
        }
        .settings-nav-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--fi-muted);
        }
        .settings-nav-total {
            font-family: var(--fi-font-mono);
            font-size: 10.5px;
            font-weight: 700;
            color: var(--fi-primary);
            background: var(--fi-primary-soft);
            padding: 1px 7px;
            border-radius: 999px;
        }
        .settings-tab {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border-radius: 8px;
            padding: 8px 10px;
            color: #334155;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.14s ease;
        }
        .settings-tab-left {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }
        .settings-tab-icon {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            color: #64748b;
            transition: color 0.14s ease;
        }
        .settings-tab-label {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .settings-tab small {
            color: var(--fi-muted);
            font-size: 10.5px;
            font-weight: 700;
            background: #f1f5f9;
            padding: 2px 7px;
            border-radius: 999px;
        }
        .settings-tab small.badge-action {
            background: #e0f2fe;
            color: #0369a1;
        }
        .settings-tab:hover {
            background: var(--fi-soft);
            color: var(--fi-text);
        }
        .settings-tab:hover .settings-tab-icon {
            color: var(--fi-text);
        }
        .settings-tab.active {
            background: var(--fi-primary-soft);
            color: var(--fi-primary);
            font-weight: 700;
            box-shadow: inset 2px 0 0 0 var(--fi-primary);
        }
        .settings-tab.active .settings-tab-icon {
            color: var(--fi-primary);
        }
        .settings-tab.active small {
            background: var(--fi-primary);
            color: #ffffff;
        }
        .settings-panel {
            padding: 0;
            overflow: hidden;
            margin-bottom: 0;
            background: #ffffff;
            border-radius: var(--fi-radius-xl);
            border: 1px solid var(--fi-border);
            box-shadow: var(--fi-shadow-sm);
        }
        .settings-heading {
            padding: 16px 20px;
            margin: 0;
            border-bottom: 1px solid var(--fi-border);
            background: #fcfcfd;
        }
        .settings-chip {
            border: 1px solid var(--fi-primary-border);
            border-radius: 6px;
            background: var(--fi-primary-soft);
            color: var(--fi-primary);
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 700;
            font-family: var(--fi-font-mono);
        }
        .settings-field-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            padding: 18px 20px;
        }
        .settings-actions-panel {
            border-top: 1px solid var(--fi-border);
            padding: 0 20px 20px;
        }
        .settings-subheading {
            margin: 0;
            padding: 16px 0 12px;
        }
        .settings-subheading h3 {
            margin: 0;
            font-size: 14px;
        }
        .settings-action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 10px;
        }
        .settings-action-card {
            display: grid;
            gap: 3px;
            border: 1px solid var(--fi-border);
            border-radius: 8px;
            background: #ffffff;
            padding: 12px 14px;
            text-decoration: none;
            transition: border-color 0.14s ease, background 0.14s ease;
        }
        .settings-action-card:hover {
            border-color: var(--fi-primary);
            background: #f8fafc;
            color: var(--fi-primary);
        }
        .settings-action-card span {
            color: var(--fi-muted);
            font-size: 11.5px;
            font-weight: 500;
        }
        .settings-field-wide { grid-column: 1 / -1; }
        .field-hint {
            display: block;
            margin-top: 4px;
            color: var(--fi-muted);
            font-size: 11.5px;
        }
        .toggle-row {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 34px;
            margin: 0;
            border: 1px solid var(--fi-border);
            border-radius: 7px;
            background: #ffffff;
            padding: 5px 10px;
            cursor: pointer;
        }
        .toggle-row input { display: none; }
        .toggle-switch {
            width: 34px;
            height: 18px;
            border-radius: 999px;
            background: #cbd5e1;
            position: relative;
            transition: background 0.15s ease;
            flex-shrink: 0;
        }
        .toggle-switch::after {
            content: "";
            width: 14px;
            height: 14px;
            border-radius: 999px;
            background: #ffffff;
            position: absolute;
            top: 2px;
            left: 2px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.2);
            transition: transform 0.15s ease;
        }
        .toggle-row input:checked + .toggle-switch { background: var(--fi-primary); }
        .toggle-row input:checked + .toggle-switch::after { transform: translateX(16px); }

        /* Media Library */
        .media-library { display: grid; gap: 14px; }
        .media-toolbar { margin-bottom: 0; }
        .media-import {
            justify-content: flex-start;
            border: 1px dashed var(--fi-border-strong);
            border-radius: 8px;
            background: #f8fafc;
            padding: 10px 12px;
        }
        .view-toggle {
            min-height: 32px;
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--fi-border-strong);
            border-radius: 7px;
            background: #ffffff;
            color: #475569;
            padding: 5px 11px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
        }
        .view-toggle:hover { background: var(--fi-soft); }
        .view-toggle.active {
            border-color: var(--fi-primary);
            background: var(--fi-primary-soft);
            color: var(--fi-primary);
        }
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
            gap: 12px;
        }
        .media-card {
            overflow: hidden;
            border: 1px solid var(--fi-border);
            border-radius: 9px;
            background: #ffffff;
            box-shadow: var(--fi-ring);
            transition: border-color 0.14s ease, box-shadow 0.14s ease;
        }
        .media-card:hover {
            border-color: var(--fi-border-strong);
            box-shadow: var(--fi-shadow-sm);
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
            min-width: 56px;
            min-height: 56px;
            border-radius: 10px;
            background: var(--fi-navy-dark);
            color: var(--fi-gold);
            font-weight: 800;
            font-size: 11px;
            letter-spacing: 0.04em;
            font-family: var(--fi-font-mono);
        }
        .media-meta {
            display: grid;
            gap: 2px;
            padding: 10px 12px;
        }
        .media-meta strong {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 12.5px;
            color: var(--fi-text);
        }
        .media-meta span {
            color: var(--fi-muted);
            font-size: 11px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .media-actions {
            border-top: 1px solid var(--fi-border);
            padding: 6px 10px;
            background: #fcfcfd;
        }
        .media-thumb {
            width: 56px;
            height: 42px;
            overflow: hidden;
            display: grid;
            place-items: center;
            border: 1px solid var(--fi-border);
            border-radius: 6px;
            background: #f8fafc;
            color: var(--fi-muted);
            font-size: 10.5px;
            font-weight: 700;
            font-family: var(--fi-font-mono);
        }
        .empty-state {
            padding: 32px 20px;
            text-align: center;
            color: var(--fi-muted);
            font-size: 13px;
        }

        /* =========================================================
           12. PAGINATION FOOTER
           ========================================================= */
        .pagination {
            margin: 0;
        }
        .table-panel .pagination {
            padding: 12px 18px;
            border-top: 1px solid var(--fi-border);
            background: #fcfcfd;
        }
        .pagination-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .pagination-summary {
            color: var(--fi-muted);
            font-size: 12px;
            font-weight: 500;
        }
        .pagination-pages {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }
        .pagination-btn {
            min-width: 30px;
            min-height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--fi-border);
            border-radius: 6px;
            background: #ffffff;
            color: #475569;
            padding: 4px 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            line-height: 1;
            transition: background 0.14s ease, border-color 0.14s ease, color 0.14s ease;
        }
        .pagination-btn:hover { background: var(--fi-soft); color: var(--fi-text); border-color: var(--fi-border-strong); }
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

        /* =========================================================
           13. RESPONSIVE BREAKPOINTS & MOBILE DRAWER
           ========================================================= */
        @media (max-width: 1024px) {
            .dashboard-grid { grid-template-columns: 1fr; }
            .shield-layout { grid-template-columns: 1fr; }
        }
        @media (max-width: 992px) {
            .settings-shell { grid-template-columns: 1fr; gap: 16px; }
            .settings-tabs {
                position: static;
                display: flex;
                flex-wrap: nowrap;
                overflow-x: auto;
                padding: 6px;
                gap: 6px;
                max-height: none;
                -webkit-overflow-scrolling: touch;
            }
            .settings-nav-header { display: none; }
            .settings-tab { flex: 0 0 auto; white-space: nowrap; }
        }
        @media (max-width: 1200px) {
            .settings-field-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 900px) {
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                width: 264px !important;
                flex: 0 0 264px !important;
                transform: translateX(-100%);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
            }
            .admin-shell.mobile-nav-open .sidebar {
                transform: translateX(0);
            }
            .admin-shell.mobile-nav-open .sidebar-backdrop {
                display: block;
            }
            .topbar { padding: 0 16px; }
            .content { padding: 16px; }
            .form-grid, .settings-field-grid, .compact-grid { grid-template-columns: 1fr; }
            .toolbar-row, .list-filter-grid { grid-template-columns: 1fr; }
            .topbar-user-pill span { display: none; }
        }

        /* =========================================================
           14. ENTERPRISE PERMISSIONS MATRIX (Linear / Shopify ACL Standard)
           ========================================================= */
        .perm-matrix-wrapper {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .perm-top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            background: #ffffff;
            border: 1px solid var(--fi-border);
            border-radius: var(--fi-radius-xl);
            padding: 12px 18px;
            box-shadow: var(--fi-shadow-sm);
        }
        .perm-search-box {
            position: relative;
            flex: 1;
            min-width: 260px;
            max-width: 440px;
        }
        .perm-search-box svg {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
        }
        .perm-search-input {
            width: 100%;
            height: 38px;
            padding-left: 38px !important;
            padding-right: 48px !important;
            background: #f8fafc;
            border: 1px solid var(--fi-border);
            border-radius: 8px;
            font-size: 13px;
            color: var(--fi-text);
            transition: all 0.14s ease;
        }
        .perm-search-input:focus {
            background: #ffffff;
            border-color: var(--fi-primary);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--fi-primary) 15%, transparent);
        }
        .perm-kbd {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-family: var(--fi-font-mono);
            font-size: 11px;
            color: #94a3b8;
            background: #ffffff;
            border: 1px solid var(--fi-border);
            border-radius: 4px;
            padding: 1px 5px;
            pointer-events: none;
        }
        .perm-filter-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .perm-role-select {
            height: 38px;
            border-radius: 8px;
            border: 1px solid var(--fi-border);
            background-color: #f8fafc;
            font-size: 13px;
            font-weight: 500;
            color: #334155;
            padding: 0 32px 0 12px;
            min-width: 180px;
            cursor: pointer;
        }
        .perm-role-select:focus {
            border-color: var(--fi-primary);
            background-color: #ffffff;
        }
        .perm-telemetry-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #64748b;
            background: #f1f5f9;
            padding: 6px 12px;
            border-radius: 999px;
            font-weight: 500;
            white-space: nowrap;
        }
        .perm-telemetry-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
        }

        .perm-tabs-strip {
            display: flex;
            align-items: center;
            gap: 4px;
            overflow-x: auto;
            border-bottom: 1px solid var(--fi-border);
            padding: 0 4px;
            background: #ffffff;
            border-radius: var(--fi-radius-xl) var(--fi-radius-xl) 0 0;
            -webkit-overflow-scrolling: touch;
        }
        .perm-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 12px 14px;
            background: transparent;
            border: 0;
            border-bottom: 2px solid transparent;
            color: #64748b;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.12s ease;
            white-space: nowrap;
        }
        .perm-tab-btn:hover {
            color: #0f172a;
            border-bottom-color: #cbd5e1;
        }
        .perm-tab-btn.active {
            color: var(--fi-primary);
            font-weight: 600;
            border-bottom-color: var(--fi-primary);
        }
        .perm-tab-counter {
            font-family: var(--fi-font-mono);
            font-size: 11px;
            background: #f1f5f9;
            color: #475569;
            padding: 1px 6px;
            border-radius: 999px;
        }
        .perm-tab-btn.active .perm-tab-counter {
            background: var(--fi-primary-soft);
            color: var(--fi-primary);
        }

        .perm-matrix-container {
            background: #ffffff;
            border: 1px solid var(--fi-border);
            border-radius: var(--fi-radius-xl);
            box-shadow: var(--fi-shadow-sm);
            overflow: hidden;
        }
        .perm-matrix-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
        }
        .perm-matrix-table th {
            background: #fcfcfd;
            border-bottom: 1px solid var(--fi-border);
            color: #475569;
            font-size: 11.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 11px 16px;
            white-space: nowrap;
        }
        .perm-matrix-table td {
            border-bottom: 1px solid var(--fi-border-subtle);
            padding: 12px 16px;
            vertical-align: middle;
            color: var(--fi-text);
            transition: background 0.1s ease;
        }
        .perm-matrix-table tr.perm-row:hover td {
            background: #f8fafc;
        }
        .perm-matrix-table tr.perm-row.audit-highlighted td {
            background: #f0fdf4 !important;
        }
        .perm-matrix-table tr.perm-row.audit-dimmed td {
            opacity: 0.35;
        }
        .perm-module-cell {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 180px;
        }
        .perm-module-name {
            font-weight: 600;
            font-size: 13.5px;
            color: #0f172a;
        }
        .perm-module-sub {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .perm-slug-code {
            font-family: var(--fi-font-mono);
            font-size: 10.5px;
            color: #64748b;
            background: #f1f5f9;
            padding: 1px 6px;
            border-radius: 4px;
        }
        .perm-action-count {
            font-size: 11px;
            color: #94a3b8;
        }

        /* Action Badges in Matrix Cells */
        .acl-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            border-radius: 6px;
            font-family: var(--fi-font-mono);
            font-size: 11px;
            font-weight: 600;
            background: #f8fafc;
            color: #334155;
            border: 1px solid #e2e8f0;
            white-space: nowrap;
            transition: all 0.14s ease;
            cursor: default;
        }
        .acl-badge-icon {
            width: 11px;
            height: 11px;
            flex-shrink: 0;
            color: #64748b;
        }
        .acl-badge.audit-has-role {
            background: #ecfdf5 !important;
            border-color: #a7f3d0 !important;
            color: #065f46 !important;
        }
        .acl-badge.audit-has-role .acl-badge-icon {
            color: #059669 !important;
        }
        .acl-badge.audit-no-role {
            opacity: 0.35;
            border-style: dashed;
            background: transparent !important;
        }
        .acl-empty-dash {
            color: #cbd5e1;
            font-size: 14px;
            user-select: none;
        }
        .acl-advanced-group {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            max-width: 260px;
        }
        .acl-roles-group {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            max-width: 240px;
        }
        .acl-role-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 500;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        .acl-role-pill.super {
            background: #fef3c7;
            color: #92400e;
            border-color: #fde68a;
            font-weight: 600;
        }
        .perm-empty-row td {
            text-align: center;
            padding: 48px 16px;
            color: #64748b;
        }
    </style>
</head>
<body>
    @auth
        <div class="admin-shell" id="admin-shell">
            <div class="sidebar-backdrop" onclick="toggleAdminSidebar()"></div>

            <aside class="sidebar" aria-label="Admin Sidebar Navigation">
                <div class="brand">
                    <a href="{{ route('admin.dashboard') }}" class="brand-identity">
                        <div class="brand-mark">N</div>
                        <div class="brand-text">
                            <div class="brand-title">{{ __('admin.brand.title') }}</div>
                            <div class="brand-subtitle">{{ __('admin.brand.subtitle') }}</div>
                        </div>
                    </a>
                    <span class="brand-badge">ERP</span>
                </div>

                <div class="sidebar-nav-scroll">
                    @foreach ($navGroups as $section)
                        @php
                            $groupIcon = $section['icon'] ?? 'grid';
                            $isSingle = ($section['label'] === 'admin.sidebar.group.dashboard' || (count($section['items']) === 1 && !isset($section['items'][0]['children'])));
                            $isSettings = ($section['label'] === 'admin.sidebar.group.settings');
                        @endphp
                        @if ($isSettings)
                            <div class="sidebar-divider" role="separator" aria-hidden="true"></div>
                        @endif
                        <nav class="nav-group {{ $isSettings ? 'nav-group-settings' : '' }}" aria-label="{{ __($section['label']) }}">
                            @if ($isSingle)
                                @foreach ($section['items'] as $item)
                                    <a class="nav-link {{ $isItemActive($item) ? 'active' : '' }}" href="{{ $navUrl($item) }}" title="{{ __($item['label']) }}">
                                        <span class="nav-link-left">
                                            {!! $renderIcon($item['icon'] ?? $groupIcon) !!}
                                            <span class="nav-link-label">{{ __($item['label']) }}</span>
                                        </span>
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
                                    <summary class="nav-summary" title="{{ __($section['label']) }}">
                                        <span class="nav-summary-left">
                                            {!! $renderIcon($groupIcon) !!}
                                            <span>{{ __($section['label']) }}</span>
                                        </span>
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
                                                            <a class="nav-link nav-link-child {{ $isItemActive($child) ? 'active' : '' }}" href="{{ $navUrl($child) }}" title="{{ __($child['label']) }}">
                                                                <span class="nav-link-left">
                                                                    {!! $renderIcon($child['icon'] ?? 'grid') !!}
                                                                    <span class="nav-link-label">{{ __($child['label']) }}</span>
                                                                </span>
                                                                <span class="nav-dot"></span>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                <a class="nav-link {{ $isItemActive($item) ? 'active' : '' }}" href="{{ $navUrl($item) }}" title="{{ __($item['label']) }}">
                                                    <span class="nav-link-left">
                                                        {!! $renderIcon($item['icon'] ?? 'grid') !!}
                                                        <span class="nav-link-label">{{ __($item['label']) }}</span>
                                                    </span>
                                                    <span class="nav-dot"></span>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </details>
                            @endif
                        </nav>
                    @endforeach
                </div>

                <div class="sidebar-footer">
                    <div class="sidebar-user">
                        <div class="sidebar-avatar">
                            {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->email ?? 'A', 0, 1)) }}
                        </div>
                        <div class="sidebar-user-meta">
                            <div class="sidebar-user-name">{{ auth()->user()->name ?? auth()->user()->email }}</div>
                            <div class="sidebar-user-role">
                                {{ auth()->user()->roles->pluck('name')->map(fn($r) => \App\Support\AdminLabel::role($r))->join(', ') ?: 'Administrator' }}
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="main">
                <header class="topbar">
                    <div class="topbar-left">
                        <button type="button" class="sidebar-toggle-btn" onclick="toggleAdminSidebar()" title="Đóng/Mở thanh điều hướng" aria-label="Toggle navigation">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/></svg>
                        </button>

                        <nav class="breadcrumb-trail" aria-label="Breadcrumb">
                            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-root">{{ __('admin.brand.title') }}</a>
                            @if ($activeGroupLabel)
                                <span class="breadcrumb-sep">/</span>
                                <span class="breadcrumb-root">{{ $activeGroupLabel }}</span>
                            @endif
                            <span class="breadcrumb-sep">/</span>
                            <span class="breadcrumb-current">{{ $title ?? __('admin.brand.title') }}</span>
                        </nav>
                    </div>

                    <div class="topbar-right">
                        <div class="lang-segmented" role="group" aria-label="Language switcher">
                            <a class="{{ app()->getLocale() === 'vi' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['lang' => 'vi']) }}">VI</a>
                            <a class="{{ app()->getLocale() === 'en' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}">EN</a>
                        </div>

                        <div class="topbar-user-pill">
                            <span class="topbar-user-dot">{{ strtoupper(substr(auth()->user()->email ?? 'A', 0, 1)) }}</span>
                            <span>{{ auth()->user()->email }}</span>
                        </div>

                        <form action="{{ route('admin.logout') }}" method="post" style="margin: 0;">
                            @csrf
                            <button class="btn btn-secondary btn-sm" type="submit" title="{{ __('admin.auth.logout') }}">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                                <span>{{ __('admin.auth.logout') }}</span>
                            </button>
                        </form>
                    </div>
                </header>

                <main class="content">
                    @yield('content')
                </main>
            </div>
        </div>

        <script>
            (function() {
                const shell = document.getElementById('admin-shell');
                if (shell && window.innerWidth > 900 && localStorage.getItem('novastore_sidebar_collapsed') === '1') {
                    shell.classList.add('sidebar-collapsed');
                }
                window.toggleAdminSidebar = function() {
                    if (!shell) return;
                    if (window.innerWidth <= 900) {
                        shell.classList.toggle('mobile-nav-open');
                    } else {
                        shell.classList.toggle('sidebar-collapsed');
                        localStorage.setItem('novastore_sidebar_collapsed', shell.classList.contains('sidebar-collapsed') ? '1' : '0');
                    }
                };
            })();
        </script>
    @else
        <main class="login-wrap">
            @yield('content')
        </main>
    @endauth
    @stack('scripts')
</body>
</html>
