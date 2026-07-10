<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: "CrossSellResource",
    type: "object",
    properties: [
        new OAT\Property(property: "id", type: "integer", example: 1),
        new OAT\Property(property: "sort_order", type: "integer", example: 0),
        new OAT\Property(property: "is_active", type: "boolean", example: true),
        new OAT\Property(property: "product", ref: "#/components/schemas/ProductResource")
    ]
)]
class CrossSellResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'product' => $this->whenLoaded('crossSellProduct', fn() => new ProductResource($this->crossSellProduct)),
        ];
    }
}
