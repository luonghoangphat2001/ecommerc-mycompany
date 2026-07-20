<?php

namespace App\Support;

use Illuminate\Support\Str;

class AdminLabel
{
    public static function role(string $role): string
    {
        return match ($role) {
            'super_admin' => 'Super Admin',
            default => Str::headline(str_replace(['_', '-'], ' ', $role)),
        };
    }

    public static function permission(string $permission): string
    {
        [$action, $resource] = self::splitPermission($permission);

        return trim(self::action($action) . ' ' . self::resource($resource));
    }

    public static function resource(string $resource): string
    {
        return match ($resource) {
            'brands' => 'Thương hiệu',
            'combo-products' => 'Combo sản phẩm',
            'comments' => 'Bình luận',
            'coupons' => 'Mã giảm giá',
            'cross-sell-products' => 'Sản phẩm bán chéo',
            'customer-reviews' => 'Đánh giá khách hàng',
            'department-agents' => 'Nhân sự phòng ban',
            'department-audit-logs' => 'Nhật ký phòng ban',
            'departments' => 'Phòng ban',
            'employee-contracts' => 'Hợp đồng nhân sự',
            'financial-proposals' => 'Đề xuất tài chính',
            'incidents' => 'Sự cố vận hành',
            'inventories' => 'Kho',
            'inventory-movements' => 'Luân chuyển kho',
            'inventory-records' => 'Phiếu kho',
            'language-lines' => 'Dòng ngôn ngữ',
            'loyalty-points' => 'Điểm thành viên',
            'media' => 'Media',
            'menu-items' => 'Mục menu',
            'menus' => 'Menu',
            'orders' => 'Đơn hàng',
            'pages' => 'Trang',
            'payments' => 'Thanh toán',
            'payrolls' => 'Bảng lương',
            'permissions' => 'Quyền',
            'post-categories' => 'Danh mục bài viết',
            'posts' => 'Bài viết',
            'product-categories' => 'Danh mục sản phẩm',
            'products' => 'Sản phẩm',
            'purchase-orders' => 'Đơn mua hàng',
            'refunds' => 'Hoàn tiền',
            'roles' => 'Vai trò',
            'settings' => 'Cài đặt',
            'shipping-methods' => 'Phương thức giao hàng',
            'shipping-zones' => 'Khu vực giao hàng',
            'tax-classes' => 'Nhóm thuế',
            'tax-rates' => 'Thuế suất',
            'upsell-products' => 'Sản phẩm bán thêm',
            'users' => 'Người dùng',
            'webhook-logs' => 'Nhật ký webhook',
            'webhooks' => 'Webhook',
            'dashboard' => 'Bảng điều khiển',
            default => Str::headline(str_replace(['_', '-'], ' ', $resource)),
        };
    }

    public static function action(string $action): string
    {
        return match ($action) {
            'view' => 'Xem',
            'create' => 'Tạo',
            'update' => 'Cập nhật',
            'delete' => 'Xóa',
            default => Str::headline(str_replace(['_', '-'], ' ', $action)),
        };
    }

    public static function splitPermission(string $permission): array
    {
        $normalized = str_replace('.', '_', $permission);
        foreach (['view', 'create', 'update', 'delete'] as $action) {
            if (str_starts_with($normalized, $action . '_')) {
                return [$action, str_replace('_', '-', Str::after($normalized, $action . '_'))];
            }
        }

        return ['', str_replace('_', '-', $normalized)];
    }

    public static function orderStatus(string|\BackedEnum|null $status): string
    {
        return self::translatedEnumLabel('admin.order.statuses', $status);
    }

    public static function paymentStatus(string|\BackedEnum|null $status): string
    {
        return self::translatedEnumLabel('admin.order.payment_statuses', $status);
    }

    public static function refundStatus(string|\BackedEnum|null $status): string
    {
        return self::translatedEnumLabel('admin.order.refund_statuses', $status);
    }

    public static function refundType(string|\BackedEnum|null $type): string
    {
        return self::translatedEnumLabel('admin.order.refund_types', $type);
    }

    private static function translatedEnumLabel(string $prefix, string|\BackedEnum|null $value): string
    {
        $raw = $value instanceof \BackedEnum ? (string) $value->value : (string) $value;
        if ($raw === '') {
            return 'N/A';
        }

        $key = $prefix . '.' . $raw;
        $label = __($key);

        return $label === $key ? Str::headline(str_replace(['_', '-'], ' ', $raw)) : $label;
    }
}
