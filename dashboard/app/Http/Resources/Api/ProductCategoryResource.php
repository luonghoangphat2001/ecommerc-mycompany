<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'ProductCategoryResource',
    title: 'Product Category',
    description: 'Chi tiết danh mục sản phẩm',
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'name', type: 'string', example: 'Electronics'),
        new OAT\Property(property: 'slug', type: 'string', example: 'electronics'),
        new OAT\Property(property: 'description', type: 'string', nullable: true, example: 'Electronics category'),
        new OAT\Property(property: 'is_visible', type: 'boolean', example: true),
        new OAT\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OAT\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
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
