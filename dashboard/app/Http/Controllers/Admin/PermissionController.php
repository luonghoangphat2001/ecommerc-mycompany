<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Support\AdminLabel;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return Permission::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.permissions';
    }

    protected function routePrefix(): string
    {
        return 'admin.permissions';
    }

    protected function searchable(): array
    {
        return ['name', 'guard_name'];
    }

    protected function fields(): array
    {
        return [
            'name' => ['label' => 'Permission', 'rules' => ['required', 'string', 'max:255']],
            'guard_name' => ['label' => 'Guard', 'rules' => ['required', 'string', 'max:50']],
        ];
    }

    public function index(Request $request): View
    {
        $categoryMap = [
            'catalog' => [
                'label' => 'Sản phẩm & Kho hàng',
                'items' => ['products', 'product-categories', 'brands', 'inventories', 'inventory-records', 'inventory-movements'],
            ],
            'sales' => [
                'label' => 'Bán hàng & Đơn hàng',
                'items' => ['orders', 'refunds', 'payments', 'purchase-orders'],
            ],
            'customers' => [
                'label' => 'Khách hàng & Người dùng',
                'items' => ['users', 'customer-reviews'],
            ],
            'marketing' => [
                'label' => 'Marketing & Khuyến mãi',
                'items' => ['coupons', 'loyalty-points', 'upsell-products', 'cross-sell-products', 'combo-products'],
            ],
            'content' => [
                'label' => 'Nội dung & Website',
                'items' => ['pages', 'posts', 'post-categories', 'menus', 'menu-items', 'media', 'comments'],
            ],
            'operations' => [
                'label' => 'Phòng ban & Vận hành',
                'items' => ['departments', 'department-agents', 'department-audit-logs', 'employee-contracts', 'payrolls', 'financial-proposals', 'incidents'],
            ],
            'system' => [
                'label' => 'Hệ thống & Cài đặt',
                'items' => ['settings', 'shipping-zones', 'shipping-methods', 'tax-classes', 'tax-rates', 'webhooks', 'webhook-logs', 'language-lines', 'dashboard', 'roles', 'permissions'],
            ],
        ];

        $roles = Role::orderBy('name')->get();
        $allPerms = Permission::with('roles:id,name')->orderBy('name')->get();
        $modules = [];

        foreach ($allPerms as $perm) {
            [$action, $resource] = AdminLabel::splitPermission($perm->name);
            $resource = $resource ?: 'other';

            $catKey = 'system';
            foreach ($categoryMap as $k => $cat) {
                if (in_array($resource, $cat['items'], true)) {
                    $catKey = $k;
                    break;
                }
            }

            if (! isset($modules[$resource])) {
                $modules[$resource] = [
                    'slug' => $resource,
                    'name' => AdminLabel::resource($resource),
                    'category' => $catKey,
                    'category_label' => $categoryMap[$catKey]['label'],
                    'actions' => [
                        'view' => [],
                        'create' => [],
                        'update' => [],
                        'delete' => [],
                        'advanced' => [],
                    ],
                    'roles' => [],
                    'all_permissions' => [],
                ];
            }

            $permRoles = $perm->roles->pluck('name')->toArray();
            $item = [
                'id' => $perm->id,
                'name' => $perm->name,
                'action' => $action ?: 'custom',
                'action_label' => AdminLabel::action($action),
                'roles' => $permRoles,
            ];

            if (in_array($action, ['view', 'view_any'], true)) {
                $modules[$resource]['actions']['view'][] = $item;
            } elseif ($action === 'create') {
                $modules[$resource]['actions']['create'][] = $item;
            } elseif (in_array($action, ['update', 'reorder', 'replicate'], true)) {
                $modules[$resource]['actions']['update'][] = $item;
            } elseif (in_array($action, ['delete', 'delete_any', 'force_delete'], true)) {
                $modules[$resource]['actions']['delete'][] = $item;
            } else {
                $modules[$resource]['actions']['advanced'][] = $item;
            }

            $modules[$resource]['all_permissions'][] = $item;

            foreach ($permRoles as $r) {
                if (! in_array($r, $modules[$resource]['roles'], true)) {
                    $modules[$resource]['roles'][] = $r;
                }
            }
        }

        ksort($modules);

        $categoryCounts = [];
        foreach ($modules as $mod) {
            $c = $mod['category'];
            $categoryCounts[$c] = ($categoryCounts[$c] ?? 0) + 1;
        }

        $stats = [
            'total_permissions' => $allPerms->count(),
            'total_modules' => count($modules),
            'total_roles' => $roles->count(),
            'guard' => 'web',
        ];

        return view('admin.permissions.index', [
            'title' => __('admin.sidebar.permissions'),
            'stats' => $stats,
            'roles' => $roles,
            'categories' => $categoryMap,
            'categoryCounts' => $categoryCounts,
            'modules' => $modules,
            'routePrefix' => $this->routePrefix(),
        ]);
    }
}
