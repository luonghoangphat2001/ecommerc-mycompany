<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'InventoryResource',
    title: 'Inventory',
    description: 'Kho hàng',
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'name', type: 'string', example: 'Kho Hồ Chí Minh'),
        new OAT\Property(property: 'slug', type: 'string', example: 'kho-ho-chi-minh'),
        new OAT\Property(property: 'location', type: 'string', nullable: true, example: 'Q1, HCM'),
        new OAT\Property(property: 'is_active', type: 'boolean', example: true),
        new OAT\Property(property: 'stock_quantity', type: 'integer', example: 100),
    ]
)]
class InventoryResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'location' => $this->location,
            'is_active' => $this->is_active,
            'stock_quantity' => $this->whenPivotLoaded('shop_product_inventory_stocks', function () {
                return $this->pivot->stock_quantity ?? 0;
            }),
        ];
    }
}
