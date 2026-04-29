<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

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
