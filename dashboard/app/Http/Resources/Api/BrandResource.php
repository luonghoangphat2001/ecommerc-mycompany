<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'BrandResource',
    title: 'Brand',
    description: 'Chi tiết thương hiệu',
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'name', type: 'string', example: 'Nike'),
        new OAT\Property(property: 'slug', type: 'string', example: 'nike'),
        new OAT\Property(property: 'website', type: 'string', nullable: true, example: 'https://nike.com'),
        new OAT\Property(property: 'description', type: 'string', nullable: true, example: 'Nike brand description'),
        new OAT\Property(property: 'is_visible', type: 'boolean', example: true),
        new OAT\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OAT\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
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
