<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'AttributeResource',
    title: 'Attribute',
    description: 'Chi tiết thuộc tính sản phẩm dùng chung',
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'name', type: 'string', example: 'Size'),
        new OAT\Property(property: 'slug', type: 'string', example: 'size'),
        new OAT\Property(property: 'type', type: 'string', example: 'button'),
        new OAT\Property(property: 'values', type: 'array', items: new OAT\Items(type: 'string'), example: ['S', 'M', 'L', 'XL']),
        new OAT\Property(property: 'is_enabled', type: 'boolean', example: true),
        new OAT\Property(property: 'products_count', type: 'integer', nullable: true, example: 5),
        new OAT\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OAT\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class AttributeResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return array_merge([
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'values' => is_array($this->values) ? array_values($this->values) : [],
            'is_enabled' => (bool) $this->is_enabled,
            'products_count' => $this->product_attributes_count ?? null,
        ], $this->getTimestamps());
    }
}
