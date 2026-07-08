<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{
    /**
     * Get translated value for a field based on request locale.
     */
    protected function translate(string $field): mixed
    {
        if (method_exists($this->resource, 'getTranslation')) {
            $locale = app()->getLocale();
            return $this->resource->getTranslation($field, $locale);
        }

        return $this->resource->{$field} ?? null;
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
        if (method_exists($model, 'getTranslation')) {
            $locale = app()->getLocale();
            return $model->getTranslation($field, $locale);
        }

        return $model->{$field} ?? null;
    }
}
