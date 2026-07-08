<?php

namespace App\Ecommerce\Coupon\Services;

use App\Ecommerce\Coupon\Contracts\CouponRepositoryInterface;
use App\Ecommerce\Coupon\Contracts\CouponServiceInterface;
use App\Ecommerce\Coupon\DTOs\CouponValidationDTO;
use App\Ecommerce\Coupon\Services\Checkers\ExpiryChecker;
use App\Ecommerce\Coupon\Services\Checkers\RequirementChecker;
use App\Ecommerce\Coupon\Services\Checkers\RestrictionChecker;
use App\Ecommerce\Coupon\Services\Checkers\UsageChecker;
use App\Ecommerce\Product\Contracts\ProductRepositoryInterface;
use App\Exceptions\CouponValidationException;
use App\Models\Coupon;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Cache;

class CouponService implements CouponServiceInterface
{
    public function __construct(
        protected CouponRepositoryInterface $couponRepository,
        protected ProductRepositoryInterface $productRepository
    ) {}

    /**
     * @inheritDoc
     */
    public function validateCoupon(string $code, array $items, float $subtotal, ?int $userId = null): CouponValidationDTO
    {
        $coupon = $this->couponRepository->findByCode($code);

        if (!$coupon) {
            throw new CouponValidationException(trans('messages.coupon_not_found'));
        }

        $dto = new CouponValidationDTO($coupon, $items, $subtotal, $userId);

        $checkers = [
            ExpiryChecker::class,
            UsageChecker::class,
            RequirementChecker::class,
            RestrictionChecker::class,
        ];

        return app(Pipeline::class)
            ->send($dto)
            ->through($checkers)
            ->thenReturn();
    }

    /**
     * @inheritDoc
     */
    public function calculateDiscount(Coupon $coupon, array $items, float $subtotal): float
    {
        $discountAmount = 0.0;

        // 1. Fixed Cart discount
        if ($coupon->type === 'fixed_cart') {
            $discountAmount = (float)$coupon->amount;
            return min($discountAmount, $subtotal);
        }

        // 2. Percentage & Fixed Product discount (Iterate over valid items)
        $validSubtotal = 0.0;

        foreach ($items as $item) {
            $productId = $item['id'] ?? $item['product_id'] ?? null;
            if (!$productId) continue;

            $product = $this->productRepository->find($productId, ['*'], ['categories']);
            if (!$product) continue;

            // Apply restrictions logic again for exact discount mapping
            if ($coupon->exclude_sale_items && $product->old_price > $product->price) continue;
            if (!empty($coupon->product_ids) && !in_array($productId, $coupon->product_ids)) continue;
            if (!empty($coupon->excluded_product_ids) && in_array($productId, $coupon->excluded_product_ids)) continue;

            if (!empty($coupon->category_ids)) {
                $productCategoryIds = $product->categories->pluck('id')->toArray();
                if (empty(array_intersect($productCategoryIds, $coupon->category_ids))) continue;
            }

            if (!empty($coupon->excluded_category_ids)) {
                $productCategoryIds = $product->categories->pluck('id')->toArray();
                if (!empty(array_intersect($productCategoryIds, $coupon->excluded_category_ids))) continue;
            }

            $itemQty = $item['quantity'] ?? 1;
            $itemPrice = (float)$product->price;

            if ($coupon->type === 'percentage') {
                $itemDiscount = ($itemPrice * $itemQty) * ($coupon->amount / 100);
                $discountAmount += $itemDiscount;
            } elseif ($coupon->type === 'fixed_product') {
                // Limit usage to X items if applicable
                $allowedQty = $itemQty;
                if ($coupon->limit_usage_to_x_items !== null) {
                    $allowedQty = min($allowedQty, $coupon->limit_usage_to_x_items);
                }
                $discountAmount += ((float)$coupon->amount * $allowedQty);
            }
        }

        return min($discountAmount, $subtotal);
    }

    /**
     * @inheritDoc
     */
    public function applyCoupon(string $code, array $items, float $subtotal, ?int $userId = null): array
    {
        // First validate
        $dto = $this->validateCoupon($code, $items, $subtotal, $userId);
        $coupon = $dto->coupon;

        // Setup Atomic Lock against Race Condition
        $lock = Cache::lock('apply_coupon_' . $coupon->id, 10);

        try {
            $lock->block(5); // Wait up to 5 seconds

            // Refresh instance data to check limits again
            $coupon->refresh();

            if ($coupon->usage_limit !== null && $coupon->usage_count >= $coupon->usage_limit) {
                throw new CouponValidationException(trans('messages.coupon_usage_limit_reached'));
            }

            // Calculate final amount
            $discountAmount = $this->calculateDiscount($coupon, $items, $subtotal);

            // Increment safely
            $coupon->increment('usage_count');

            return [
                'code' => $coupon->code,
                'discount_amount' => $discountAmount,
                'type' => $coupon->type
            ];
        } finally {
            $lock->release();
        }
    }
}
