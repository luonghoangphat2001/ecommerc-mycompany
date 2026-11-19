<?php

namespace App\Support;

use App\Models\Product;
use Awcodes\Curator\Models\Media as CuratorMedia;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

final class Media
{
    public static function path(mixed $value, mixed $featuredMedia = null, mixed $fallback = null): mixed
    {
        $path = is_array($value) ? ($value[0] ?? null) : $value;

        return is_numeric($path) && $featuredMedia ? $featuredMedia->path : ($path ?: $fallback);
    }

    public static function url(mixed $media): ?string
    {
        return self::pathUrl($media?->path);
    }

    public static function pathUrl(?string $path): ?string
    {
        return $path ? Storage::url($path) : null;
    }

    public static function productImage(Product $product): ?string
    {
        if (! $product->relationLoaded('featuredImage')) {
            $product->load('featuredImage');
        }

        if ($product->featuredImage) {
            return $product->featuredImage->url;
        }

        if (! $product->relationLoaded('media')) {
            $product->load('media');
        }

        $mediaUrl = $product->getFirstMediaUrl('images');
        if ($mediaUrl) {
            return $mediaUrl;
        }

        $mediaId = is_array($product->product_images)
            ? ($product->product_images[0] ?? null)
            : (is_numeric($product->product_images) ? (int) $product->product_images : null);

        if (! $mediaId) {
            return null;
        }

        return CuratorMedia::find($mediaId)?->url ?? SpatieMedia::find($mediaId)?->getUrl();
    }
}
