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
        new OAT\Property(property: 'type', type: 'string', example: 'simple'),
        new OAT\Property(property: 'product_type', type: 'string', example: 'simple'),
        new OAT\Property(property: 'image', ref: '#/components/schemas/MediaResource', nullable: true),
        new OAT\Property(property: 'price', type: 'number', format: 'float', example: 100000),
        new OAT\Property(property: 'sale_price', type: 'number', format: 'float', nullable: true, example: 90000),
        new OAT\Property(property: 'effective_price', type: 'number', format: 'float', example: 90000),
        new OAT\Property(property: 'is_on_sale', type: 'boolean', example: true),
        new OAT\Property(property: 'sale_start_at', type: 'string', format: 'date-time', nullable: true),
        new OAT\Property(property: 'sale_end_at', type: 'string', format: 'date-time', nullable: true),
        new OAT\Property(property: 'apply_tax', type: 'boolean', example: true),
        new OAT\Property(property: 'tax_class_id', type: 'integer', nullable: true, example: 1),
        new OAT\Property(property: 'qty', type: 'integer', example: 100),
        new OAT\Property(property: 'stock', type: 'integer', example: 100),
        new OAT\Property(property: 'featured', type: 'boolean', example: false),
        new OAT\Property(property: 'is_available', type: 'boolean', example: true),
        new OAT\Property(property: 'brand', ref: '#/components/schemas/BrandResource', nullable: true),
        new OAT\Property(property: 'categories', type: 'array', items: new OAT\Items(ref: '#/components/schemas/ProductCategoryResource')),
        new OAT\Property(property: 'inventories', type: 'array', items: new OAT\Items(ref: '#/components/schemas/InventoryResource')),
        new OAT\Property(property: 'attributes', type: 'array', items: new OAT\Items(type: 'object')),
        new OAT\Property(property: 'variants', type: 'array', items: new OAT\Items(type: 'object')),
        new OAT\Property(property: 'combo_components', type: 'array', items: new OAT\Items(type: 'object')),
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
        $featuredMedia = $this->relationLoaded('featuredImage') ? $this->featuredImage : null;
        $gallery = $featuredMedia ? [new MediaResource($featuredMedia)] : [];
        $stock = $this->available_stock;
        $productType = $this->isVariable() ? 'variable' : ($this->isCombo() ? 'combo' : 'simple');

        $policy = app(\App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface::class);
        $trackStock = $policy->isInventoryTrackingEnabled();

        $attributesData = [];
        if ($this->relationLoaded('productAttributes')) {
            $attributesData = $this->productAttributes->map(fn($attr) => [
                'id' => $attr->id,
                'name' => $attr->name,
                'options' => is_array($attr->options) ? $attr->options : (json_decode($attr->options, true) ?: []),
                'is_variation' => (bool) $attr->is_variation,
            ]);
        }

        $variantsData = [];
        if ($this->relationLoaded('variants')) {
            $variantsData = $this->variants->map(fn($v) => [
                'id' => $v->id,
                'sku' => $v->sku,
                'price' => (int) $v->price,
                'sale_price' => $v->sale_price ? (int) $v->sale_price : null,
                'effective_price' => (int) $v->effective_price,
                'is_on_sale' => (bool) $v->is_on_sale,
                'qty' => (int) $v->qty,
                'stock' => (int) $v->available_stock,
                'is_available' => (bool) ($v->is_active && (!$trackStock || $v->available_stock > 0)),
                'attributes' => is_array($v->attributes) ? $v->attributes : (json_decode($v->attributes, true) ?: []),
                'image' => $v->image,
                'weight' => $v->weight,
                'dimensions' => $v->dimensions,
                'is_active' => (bool) $v->is_active,
                'tax_class_id' => $v->tax_class_id ?? $this->tax_class_id,
            ]);
        }

        $comboComponentsData = [];
        if ($this->relationLoaded('comboComponents')) {
            $comboComponentsData = $this->comboComponents->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->shop_product_id,
                    'variant_id' => $item->product_variant_id,
                    'name' => $item->product?->name ?? 'Component',
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (int) $item->unit_price,
                    'subtotal' => (int) $item->subtotal,
                    'product' => $item->product ? [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'slug' => $item->product->slug,
                        'sku' => $item->product->sku,
                        'price' => (int) $item->product->price,
                        'effective_price' => (int) $item->product->effective_price,
                        'stock' => (int) $item->product->available_stock,
                        'image' => $item->product->featuredImage ? \Illuminate\Support\Facades\Storage::url($item->product->featuredImage->path) : null,
                        'tax_class_id' => $item->product->tax_class_id,
                    ] : null,
                    'variant' => $item->variant ? [
                        'id' => $item->variant->id,
                        'sku' => $item->variant->sku,
                        'price' => (int) $item->variant->price,
                        'effective_price' => (int) $item->variant->effective_price,
                        'stock' => (int) $item->variant->available_stock,
                        'attributes' => $item->variant->attributes,
                        'tax_class_id' => $item->variant->tax_class_id,
                    ] : null,
                ];
            });
        }

        return array_merge([
            'id' => $this->id,
            'name' => $this->translate('name'),
            'description' => $this->translate('description'),
            'slug' => $this->slug,
            'sku' => $this->sku,
            'type' => $this->type?->value ?? 'deliverable',
            'product_type' => $productType,
            'image' => new MediaResource($this->whenLoaded('featuredImage')),
            'images' => $gallery,
            'gallery' => $gallery,
            'price' => $this->price,
            'old_price' => $this->old_price,
            'sale_price' => $this->sale_price,
            'effective_price' => $this->effective_price,
            'is_on_sale' => $this->is_on_sale,
            'sale_start_at' => $this->sale_start_date ? $this->sale_start_date->toIso8601String() : null,
            'sale_end_at' => $this->sale_end_date ? $this->sale_end_date->toIso8601String() : null,
            'apply_tax' => (bool)$this->apply_tax,
            'is_loyalty_earnable' => (bool) ($this->is_loyalty_earnable ?? true),
            'is_loyalty_redeemable' => (bool) ($this->is_loyalty_redeemable ?? true),
            'loyalty_points_reward' => $this->loyalty_points_reward ? (int) $this->loyalty_points_reward : null,
            'tax_class_id' => $this->tax_class_id,
            'qty' => $stock,
            'stock' => $stock,
            'featured' => $this->featured,
            'is_available' => (bool) ($this->is_visible && (!$trackStock || $stock > 0)),
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'categories' => ProductCategoryResource::collection($this->whenLoaded('categories')),
            'inventories' => InventoryResource::collection($this->whenLoaded('inventories')),
            'attributes' => $attributesData,
            'variants' => $variantsData,
            'combo_components' => $comboComponentsData,
            'meta_seo' => [
                'title' => $this->translate('seo_title') ?: $this->translate('name'),
                'description' => $this->translate('seo_description') ?: $this->translate('description'),
            ],
            'og_image' => $this->relationLoaded('featuredImage') && $this->featuredImage 
                ? \Illuminate\Support\Facades\Storage::url($this->featuredImage->path) 
                : null,
        ], $this->getVisibility(), $this->getTimestamps());
    }
}
