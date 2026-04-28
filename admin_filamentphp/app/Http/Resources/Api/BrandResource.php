<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

class BrandResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return array_merge([
            'id' => $this->id,
            'name' => $this->translate('name'),
            'slug' => $this->slug,
            'website' => $this->website,
            'description' => $this->translate('description'),
        ], $this->getVisibility(), $this->getTimestamps());
    }
}
