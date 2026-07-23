<?php

namespace App\Support;

use BackedEnum;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AdminValue
{
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

        if ($value === null || $value === '') {
            return '—';
        }

        return (string) $value;
    }

    protected static function formatArray(array $value, bool $compact): string
    {
        $value = self::withoutNoise($value);

        if ($value === []) {
            return '—';
        }

        $locale = app()->getLocale();
        if (array_key_exists($locale, $value) || array_key_exists('vi', $value) || array_key_exists('en', $value)) {
            return (string) ($value[$locale] ?? $value['vi'] ?? $value['en'] ?? reset($value));
        }

        if (array_is_list($value)) {
            return self::formatList($value, $compact);
        }

        $lines = [];
        foreach ($value as $key => $item) {
            $lines[] = str($key)->headline() . ': ' . self::format($item, true);
        }

        return implode($compact ? ', ' : "\n", $lines);
    }

    protected static function formatList(array $items, bool $compact): string
    {
        $items = array_values(array_filter($items, fn ($item) => $item !== null && $item !== ''));

        if ($items === []) {
            return '—';
        }

        $formatted = array_map(fn ($item) => self::format($item, true), $items);

        if ($compact) {
            $visible = array_slice($formatted, 0, 3);
            $suffix = count($formatted) > 3 ? ' +' . (count($formatted) - 3) : '';

            return implode(', ', $visible) . $suffix;
        }

        return implode("\n", array_map(fn ($item) => '• ' . $item, $formatted));
    }

    protected static function formatModel(Model $model): string
    {
        if ($model instanceof \Spatie\Permission\Models\Role) {
            return AdminLabel::role($model->name);
        }

        if ($model instanceof \Spatie\Permission\Models\Permission) {
            return AdminLabel::permission($model->name);
        }

        foreach (['name', 'title', 'label', 'email', 'number', 'slug'] as $attribute) {
            if ($model->getAttribute($attribute)) {
                return self::format($model->getAttribute($attribute), true);
            }
        }

        return class_basename($model) . ' #' . $model->getKey();
    }

    protected static function withoutNoise(array $value): array
    {
        unset($value['pivot'], $value['created_at'], $value['updated_at'], $value['deleted_at']);

        return $value;
    }
}
