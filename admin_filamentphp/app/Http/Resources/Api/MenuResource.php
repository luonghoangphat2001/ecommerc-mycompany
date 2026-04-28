<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

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
