<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

class OrderItemResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $product = $this->product;
        $imageUrl = null;
        
        if ($product) {
            // Try Curator Media (Awcodes) first - used by this project
            if (!$product->relationLoaded('featuredImage')) {
                $product->load('featuredImage');
            }
            if ($product->featuredImage) {
                $imageUrl = $product->featuredImage->url;
            }
            
            // Fallback to Spatie MediaLibrary
            if (!$imageUrl) {
                if (!$product->relationLoaded('media')) {
                    $product->load('media');
                }
                $imageUrl = $product->getFirstMediaUrl('images');
            }
            
            // Fallback to product_images field (media ID - integer in DB)
            if (!$imageUrl && $product->product_images) {
                $mediaId = null;
                
                if (is_array($product->product_images)) {
                    $mediaId = $product->product_images[0] ?? null;
                } elseif (is_numeric($product->product_images)) {
                    $mediaId = (int) $product->product_images;
                }
                
                if ($mediaId) {
                    // Try Curator Media first
                    $media = \Awcodes\Curator\Models\Media::find($mediaId);
                    if ($media) {
                        $imageUrl = $media->url;
                    } else {
                        // Fallback to Spatie MediaLibrary
                        $spatieMedia = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId);
                        if ($spatieMedia) {
                            $imageUrl = $spatieMedia->getUrl();
                        }
                    }
                }
            }
        }
        
        return [
            'id' => $this->id,
            'product_id' => $this->shop_product_id,
            'product' => [
                'id' => $product?->id,
                'name' => $product?->name,
                'image' => $imageUrl ? [
                    'url' => $imageUrl,
                ] : null,
            ],
            'qty' => $this->qty,
            'unit_price' => $this->unit_price,
            'total' => $this->total,
            'type' => $this->type,
        ];
    }
}
