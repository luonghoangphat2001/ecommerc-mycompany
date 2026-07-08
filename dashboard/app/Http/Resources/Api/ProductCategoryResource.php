<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

class ProductCategoryResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return array_merge([
            'id' => $this->id,
            'name' => $this->translate('name'),
            'slug' => $this->slug,
            'description' => $this->translate('description'),
        ], $this->getVisibility(), $this->getTimestamps());
    }
}
