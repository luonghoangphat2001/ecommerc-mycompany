<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'items' => $this->resource['items'] ?? [],
            'summary' => $this->resource['summary'] ?? [],
            'notifications' => $this->resource['notifications'] ?? [],
        ];
    }
}
