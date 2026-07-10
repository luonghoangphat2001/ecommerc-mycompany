<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Awcodes\Curator\Models\Media;
use App\Http\Resources\Api\MediaResource;
use App\Http\Resources\Api\BrandResource;
use App\Http\Resources\Api\ProductCategoryResource;
use App\Http\Resources\Api\InventoryResource;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'ProductResource',
    title: 'Product',
    description: 'Chi tiết sản phẩm',
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'name', type: 'string', example: 'Product Name'),
        new OAT\Property(property: 'description', type: 'string', nullable: true, example: 'Product Description'),
        new OAT\Property(property: 'slug', type: 'string', example: 'product-slug'),
        new OAT\Property(property: 'image', ref: '#/components/schemas/MediaResource', nullable: true),
        new OAT\Property(property: 'price', type: 'number', format: 'float', example: 100000),
        new OAT\Property(property: 'qty', type: 'integer', example: 100),
        new OAT\Property(property: 'stock', type: 'integer', example: 100),
        new OAT\Property(property: 'featured', type: 'boolean', example: false),
        new OAT\Property(property: 'is_available', type: 'boolean', example: true),
        new OAT\Property(property: 'brand', ref: '#/components/schemas/BrandResource', nullable: true),
        new OAT\Property(property: 'categories', type: 'array', items: new OAT\Items(ref: '#/components/schemas/ProductCategoryResource')),
        new OAT\Property(property: 'inventories', type: 'array', items: new OAT\Items(ref: '#/components/schemas/InventoryResource')),
        new OAT\Property(property: 'meta_seo', type: 'object', properties: [
            new OAT\Property(property: 'title', type: 'string'),
            new OAT\Property(property: 'description', type: 'string')
        ]),
        new OAT\Property(property: 'og_image', type: 'string', nullable: true, example: '/storage/media/image.jpg'),
        new OAT\Property(property: 'is_visible', type: 'boolean', example: true),
        new OAT\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OAT\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
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
            'qty' => $this->qty,
            'stock' => $this->qty, // Keep backward compatibility
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
