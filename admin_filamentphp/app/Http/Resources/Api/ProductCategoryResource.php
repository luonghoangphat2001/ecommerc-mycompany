<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

class ProductCategoryResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->translate('name'),
            'slug' => $this->slug,
            'description' => $this->translate('description'),
            'is_visible' => $this->is_visible,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
