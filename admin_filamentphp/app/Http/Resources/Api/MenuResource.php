<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'menu_items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'label' => $item->label,
                    'url' => $item->url,
                    'parent_id' => $item->parent_id,
                    'order' => $item->order,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'children' => $item->children ? $item->children->map(fn($child) => [
                        'id' => $child->id,
                        'label' => $child->label,
                        'url' => $child->url,
                        'parent_id' => $child->parent_id,
                        'order' => $child->order,
                        'created_at' => $child->created_at,
                        'updated_at' => $child->updated_at,
                    ]) : []
                ];
            }),
        ];
    }
}
