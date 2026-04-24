<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{
    /**
     * Get translated value for a field.
     */
    protected function translate(string $field): mixed
    {
        if (method_exists($this->resource, 'getTranslation')) {
            return $this->resource->getTranslation($field, app()->getLocale());
        }

        return $this->resource->{$field} ?? null;
    }
}
