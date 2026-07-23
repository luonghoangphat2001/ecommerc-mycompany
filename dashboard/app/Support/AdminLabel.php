<?php

namespace App\Support;

use Illuminate\Support\Str;

class AdminLabel
{
    public static function role(string $role): string
    {
        $key = 'admin.role_labels.' . $role;
        $translation = __($key);

        if ($translation !== $key) {
            return $translation;
        }

        return Str::headline(str_replace(['_', '-'], ' ', $role));
    }

    public static function permission(string $permission): string
    {
        [$action, $resource] = self::splitPermission($permission);

        $actionLabel = self::action($action);
        $resourceLabel = self::resource($resource);

        if ($actionLabel !== '' && $resourceLabel !== '') {
            return trim($actionLabel . ' ' . $resourceLabel);
        }

        return Str::headline(str_replace(['_', '-'], ' ', $permission));
    }

    public static function resource(string $resource): string
    {
        $key = 'admin.resource_labels.' . $resource;
        $translation = __($key);

        if ($translation !== $key) {
            return $translation;
        }

        return Str::headline(str_replace(['_', '-'], ' ', $resource));
    }

    public static function action(string $action): string
    {
        $key = 'admin.permission_actions.' . $action;
        $translation = __($key);

        if ($translation !== $key) {
            return $translation;
        }

        return Str::headline(str_replace(['_', '-'], ' ', $action));
    }

    public static function splitPermission(string $permission): array
    {
        $normalized = str_replace('.', '_', $permission);
        $actions = [
            'view_any', 'delete_any', 'force_delete',
            'view', 'create', 'update', 'delete',
            'restore', 'replicate', 'reorder', 'export', 'import'
        ];

        foreach ($actions as $action) {
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
