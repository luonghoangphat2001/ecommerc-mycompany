<?php

namespace App\Http\Resources\Api;

use App\Ecommerce\Product\DTOs\TaxCalculationDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxCalculationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        if ($this->resource instanceof TaxCalculationDTO) {
            return $this->resource->toArray();
        }

        return (array) $this->resource;
    }
}
