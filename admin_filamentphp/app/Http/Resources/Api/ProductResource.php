<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Awcodes\Curator\Models\Media;
use App\Http\Resources\Api\MediaResource;
use App\Http\Resources\Api\BrandResource;
use App\Http\Resources\Api\ProductCategoryResource;

class ProductResource extends BaseResource
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
            'name' => $this->translate('name'),
            'description' => $this->translate('description'),
            'slug' => $this->slug,
            'image' => new MediaResource($this->whenLoaded('featuredImage')),
            'price' => $this->price,
            'stock' => $this->stock,
            'featured' => $this->featured,
            'is_visible' => $this->is_visible,
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'categories' => ProductCategoryResource::collection($this->whenLoaded('categories')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
