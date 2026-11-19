<?php

namespace App\Ecommerce\Core\Repositories\Concerns;

use Illuminate\Support\Collection;

trait LoadsRelatedProducts
{
    protected function relatedProducts(string $modelClass, string $relation, int $productId): Collection
    {
        return $modelClass::with([
            "{$relation}.featuredImage",
            "{$relation}.categories",
            "{$relation}.brand",
        ])
            ->where('shop_product_id', $productId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}
