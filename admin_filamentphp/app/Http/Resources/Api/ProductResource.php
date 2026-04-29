<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Awcodes\Curator\Models\Media;
use App\Http\Resources\Api\MediaResource;
use App\Http\Resources\Api\BrandResource;
use App\Http\Resources\Api\ProductCategoryResource;
use App\Http\Resources\Api\InventoryResource;

class ProductResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return array_merge([
            'id' => $this->id,
            'name' => $this->translate('name'),
            'description' => $this->translate('description'),
            'slug' => $this->slug,
            'image' => new MediaResource($this->whenLoaded('featuredImage')),
            'price' => $this->price,
            'stock' => $this->stock,
            'featured' => $this->featured,
            'is_available' => $this->qty > 0 && $this->is_visible,
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'categories' => ProductCategoryResource::collection($this->whenLoaded('categories')),
            'inventories' => InventoryResource::collection($this->whenLoaded('inventories')),
            'meta_seo' => [
                'title' => $this->seo_title ?? $this->translate('name'),
                'description' => $this->seo_description ?? $this->translate('description'),
            ],
            'og_image' => $this->relationLoaded('featuredImage') && $this->featuredImage 
                ? \Illuminate\Support\Facades\Storage::url($this->featuredImage->path) 
                : null,
        ], $this->getVisibility(), $this->getTimestamps());
    }
}
