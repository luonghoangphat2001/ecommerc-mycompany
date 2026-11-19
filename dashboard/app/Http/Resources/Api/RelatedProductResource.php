<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

abstract class RelatedProductResource extends BaseResource
{
    abstract protected function productRelation(): string;

    public function toArray(Request $request): array
    {
        $relation = $this->productRelation();

        return [
            'id' => $this->id,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'product' => $this->whenLoaded($relation, fn () => new ProductResource($this->{$relation})),
        ];
    }
}
