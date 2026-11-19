<?php

namespace App\Http\Resources\Api;

use App\Support\Localization;
use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{
    protected function currentLocale(): string
    {
        return app()->getLocale() ?: 'vi';
    }

    protected function asArray(mixed $value): array
    {
        return is_array($value) ? $value : ($this->decodeArray($value) ?? []);
    }

    protected function decodeArray(mixed $value): ?array
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : null;
    }

    protected function mediaUrl(mixed $media): ?string
    {
        return Media::url($media);
    }

    /**
     * Get translated value for a field based on request locale.
     */
    protected function translate(string $field): mixed
    {
        return $this->translateField($this->resource, $field);
    }

    /**
     * Get common timestamp fields.
     */
    protected function getTimestamps(): array
    {
        return [
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get visibility field.
     */
    protected function getVisibility(): array
    {
        return [
            'is_visible' => $this->is_visible,
        ];
    }

    /**
     * Translate a field from an external model (not $this->resource).
     */
    protected function translateField($model, string $field): mixed
    {
        if (! $model) {
            return null;
        }

        $locale = $this->currentLocale();

        if (method_exists($model, 'isTranslatableAttribute') && $model->isTranslatableAttribute($field)) {
            $value = $model->getTranslation($field, $locale, false);
            if ($value !== '' && $value !== null) {
                return $value;
            }
            $translations = method_exists($model, 'getTranslations') ? $model->getTranslations($field) : [];

            return $translations[$locale] ?? $translations['vi'] ?? $translations['en'] ?? (! empty($translations) ? reset($translations) : null);
        }

        $raw = $model->{$field} ?? null;

        if (is_array($raw)) {
            return Localization::value($raw, $locale);
        }

        if (is_string($raw) && str_starts_with(trim($raw), '{')) {
            $decoded = $this->decodeArray($raw);
            if ($decoded !== null) {
                return Localization::value($decoded, $locale) ?? $raw;
            }
        }

        return $raw;
    }
}
