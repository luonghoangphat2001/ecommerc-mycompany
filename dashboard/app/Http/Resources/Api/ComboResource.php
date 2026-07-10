<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: "ComboResource",
    type: "object",
    properties: [
        new OAT\Property(property: "id", type: "integer", example: 1),
        new OAT\Property(property: "name", type: "string", example: "Combo Mùa Hè"),
        new OAT\Property(property: "slug", type: "string", example: "combo-mua-he"),
        new OAT\Property(property: "description", type: "string", nullable: true, example: "Combo giá rẻ"),
        new OAT\Property(property: "combo_price", type: "number", format: "float", example: 1000000),
        new OAT\Property(property: "original_price", type: "number", format: "float", example: 1200000),
        new OAT\Property(property: "discount_percent", type: "number", format: "float", nullable: true, example: 16.67),
        new OAT\Property(property: "is_active", type: "boolean", example: true),
        new OAT\Property(property: "start_date", type: "string", format: "date-time", nullable: true),
        new OAT\Property(property: "end_date", type: "string", format: "date-time", nullable: true),
        new OAT\Property(
            property: "items",
            type: "array",
            items: new OAT\Items(
                type: "object",
                properties: [
                    new OAT\Property(property: "id", type: "integer", example: 1),
                    new OAT\Property(property: "product", ref: "#/components/schemas/ProductResource"),
                    new OAT\Property(property: "quantity", type: "integer", example: 1)
                ]
            )
        )
    ]
)]
class ComboResource extends BaseResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'combo_price' => $this->combo_price,
            'original_price' => $this->original_price,
            'discount_percent' => $this->discount_percent,
            'is_active' => $this->is_active,
            'start_date' => $this->start_date?->toDateTimeString(),
            'end_date' => $this->end_date?->toDateTimeString(),
            'items' => $this->whenLoaded('items', fn() => $this->items->map(fn($item) => [
                'id' => $item->id,
                'product' => new ProductResource($item->product),
                'quantity' => $item->quantity,
            ])),
        ];
    }
}
