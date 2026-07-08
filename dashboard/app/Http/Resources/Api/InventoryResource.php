<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

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
