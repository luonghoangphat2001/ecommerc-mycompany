<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

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
