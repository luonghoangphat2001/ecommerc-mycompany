<?php

namespace App\Support;

use BackedEnum;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class Admin
{
    public static function roleLabel(string $role): string
    {
        return self::translatedLabel('admin.role_labels', $role);
    }

    public static function permissionLabel(string $permission): string
    {
        [$action, $resource] = self::splitPermission($permission);
        $actionLabel = self::actionLabel($action);
        $resourceLabel = self::resourceLabel($resource);

        return $actionLabel !== '' && $resourceLabel !== ''
            ? trim($actionLabel . ' ' . $resourceLabel)
            : self::headline($permission);
    }

    public static function resourceLabel(string $resource): string
    {
        return self::translatedLabel('admin.resource_labels', $resource);
    }

    public static function actionLabel(string $action): string
    {
        return self::translatedLabel('admin.permission_actions', $action);
    }

    public static function orderStatus(string | BackedEnum | null $status): string
    {
        return self::translatedEnumLabel('admin.order.statuses', $status);
    }

    public static function paymentStatus(string | BackedEnum | null $status): string
    {
        return self::translatedEnumLabel('admin.order.payment_statuses', $status);
    }

    public static function refundStatus(string | BackedEnum | null $status): string
    {
        return self::translatedEnumLabel('admin.order.refund_statuses', $status);
    }

    public static function refundType(string | BackedEnum | null $type): string
    {
        return self::translatedEnumLabel('admin.order.refund_types', $type);
    }

    public static function format(mixed $value, bool $compact = false): string
    {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof Carbon) {
            return $value->format('Y-m-d H:i');
        }

        if ($value instanceof Model) {
            return self::formatModel($value);
        }

        if ($value instanceof Collection) {
            return self::formatList($value->all(), $compact);
        }

        if ($value instanceof Arrayable) {
            return self::format($value->toArray(), $compact);
        }

        if (is_array($value)) {
            return self::formatArray($value, $compact);
        }

        if (is_bool($value)) {
            return $value ? 'Có' : 'Không';
        }

        return $value === null || $value === '' ? '—' : (string) $value;
    }

    private static function translatedLabel(string $prefix, string $value): string
    {
        $key = $prefix . '.' . $value;
        $translation = __($key);

        return $translation === $key ? self::headline($value) : $translation;
    }

    private static function translatedEnumLabel(string $prefix, string | BackedEnum | null $value): string
    {
        $raw = $value instanceof BackedEnum ? (string) $value->value : (string) $value;

        return $raw === '' ? 'N/A' : self::translatedLabel($prefix, $raw);
    }

    public static function splitPermission(string $permission): array
    {
        $normalized = str_replace('.', '_', $permission);
        $actions = ['view_any', 'delete_any', 'force_delete', 'view', 'create', 'update', 'delete', 'restore', 'replicate', 'reorder', 'export', 'import'];

        foreach ($actions as $action) {
            if (str_starts_with($normalized, $action . '_')) {
                return [$action, str_replace('_', '-', Str::after($normalized, $action . '_'))];
            }
        }

        return ['', str_replace('_', '-', $normalized)];
    }

    private static function headline(string $value): string
    {
        return Str::headline(str_replace(['_', '-'], ' ', $value));
    }

    private static function formatArray(array $value, bool $compact): string
    {
        unset($value['pivot'], $value['created_at'], $value['updated_at'], $value['deleted_at']);

        if ($value === []) {
            return '—';
        }

        $locale = app()->getLocale();
        if (array_key_exists($locale, $value) || array_key_exists('vi', $value) || array_key_exists('en', $value)) {
            return (string) Localization::value($value, $locale);
        }

        if (array_is_list($value)) {
            return self::formatList($value, $compact);
        }

        $lines = [];
        foreach ($value as $key => $item) {
            $lines[] = Str::headline($key) . ': ' . self::format($item, true);
        }

        return implode($compact ? ', ' : "\n", $lines);
    }

    private static function formatList(array $items, bool $compact): string
    {
        $items = array_values(array_filter($items, fn ($item) => $item !== null && $item !== ''));

        if ($items === []) {
            return '—';
        }

        $formatted = array_map(fn ($item) => self::format($item, true), $items);

        if ($compact) {
            return implode(', ', array_slice($formatted, 0, 3)) . (count($formatted) > 3 ? ' +' . (count($formatted) - 3) : '');
        }

        return implode("\n", array_map(fn ($item) => '• ' . $item, $formatted));
    }

    private static function formatModel(Model $model): string
    {
        if ($model instanceof Role) {
            return self::roleLabel($model->name);
        }

        if ($model instanceof Permission) {
            return self::permissionLabel($model->name);
        }

        foreach (['name', 'title', 'label', 'email', 'number', 'slug'] as $attribute) {
            if ($model->getAttribute($attribute)) {
                return self::format($model->getAttribute($attribute), true);
            }
        }

        return class_basename($model) . ' #' . $model->getKey();
    }
}
