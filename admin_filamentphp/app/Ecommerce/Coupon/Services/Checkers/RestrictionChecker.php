<?php

namespace App\Ecommerce\Coupon\Services\Checkers;

use App\Ecommerce\Product\Contracts\ProductRepositoryInterface;
use App\Ecommerce\Coupon\DTOs\CouponValidationDTO;
use App\Exceptions\CouponValidationException;
use Closure;

class RestrictionChecker
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function handle(CouponValidationDTO $dto, Closure $next)
    {
        $coupon = $dto->coupon;
        $items = $dto->items;

        $validItemsCount = 0;

        foreach ($items as $item) {
            $productId = $item['id'] ?? $item['product_id'] ?? null;
            if (!$productId) continue;

            $product = $this->productRepository->find($productId, ['*'], ['categories']);
            if (!$product) continue;

            // 1. Check exclude_sale_items
            if ($coupon->exclude_sale_items && $product->old_price > $product->price) {
                continue;
            }

            // 2. Check product_ids (Allowed)
            if (!empty($coupon->product_ids) && !in_array($productId, $coupon->product_ids)) {
                continue;
            }

            // 3. Check excluded_product_ids
            if (!empty($coupon->excluded_product_ids) && in_array($productId, $coupon->excluded_product_ids)) {
                continue;
            }

            // 4. Check category_ids
            if (!empty($coupon->category_ids)) {
                $productCategoryIds = $product->categories->pluck('id')->toArray();
                $intersect = array_intersect($productCategoryIds, $coupon->category_ids);
                if (empty($intersect)) {
                    continue;
                }
            }

            // 5. Check excluded_category_ids
            if (!empty($coupon->excluded_category_ids)) {
                $productCategoryIds = $product->categories->pluck('id')->toArray();
                $intersect = array_intersect($productCategoryIds, $coupon->excluded_category_ids);
                if (!empty($intersect)) {
                    continue;
                }
            }

            $validItemsCount++;
        }

        if ($validItemsCount === 0) {
            throw new CouponValidationException(trans('messages.coupon_restriction_not_met'));
        }

        return $next($dto);
    }
}
