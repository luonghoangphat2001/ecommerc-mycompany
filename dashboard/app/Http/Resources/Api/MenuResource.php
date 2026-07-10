<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: "MenuResource",
    type: "object",
    properties: [
        new OAT\Property(property: "id", type: "integer", example: 1),
        new OAT\Property(property: "name", type: "string", example: "Main Menu"),
        new OAT\Property(property: "slug", type: "string", example: "main-menu"),
        new OAT\Property(
            property: "menu_items",
            type: "array",
            items: new OAT\Items(
                properties: [
                    new OAT\Property(property: "id", type: "integer", example: 1),
                    new OAT\Property(property: "label", type: "string", example: "Trang chủ"),
                    new OAT\Property(property: "url", type: "string", example: "/"),
                    new OAT\Property(property: "parent_id", type: "integer", nullable: true, example: null),
                    new OAT\Property(property: "order", type: "integer", example: 0),
                    new OAT\Property(
                        property: "children",
                        type: "array",
                        items: new OAT\Items(type: "object")
                    )
                ]
            )
        ),
        new OAT\Property(property: "created_at", type: "string", format: "date-time"),
        new OAT\Property(property: "updated_at", type: "string", format: "date-time")
    ]
)]
class MenuResource extends BaseResource
{
    /**
     * Format a single menu item recursively.
     *
     * @param mixed $item
     * @return array
     */
    protected function formatMenuItem($item)
    {
        return [
            'id' => $item->id,
            'label' => $item->label,
            'url' => $item->url,
            'parent_id' => $item->parent_id,
            'order' => $item->order,
            'children' => $item->children && $item->children->count() > 0 
                ? $item->children->map(fn($child) => $this->formatMenuItem($child)) 
                : []
        ];
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return array_merge([
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'menu_items' => $this->items->map(fn($item) => $this->formatMenuItem($item)),
        ], $this->getTimestamps());
    }
}
