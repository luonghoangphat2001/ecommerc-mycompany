<?php

namespace App\Ecommerce\Coupon\Contracts;

use App\Ecommerce\Coupon\DTOs\CouponValidationDTO;
use App\Models\Coupon;

interface CouponServiceInterface
{
    /**
     * Validate a coupon code against cart context.
     *
     * @param string $code
     * @param array $items
     * @param float $subtotal
     * @param int|null $userId
     * @return CouponValidationDTO
     */
    public function validateCoupon(string $code, array $items, float $subtotal, ?int $userId = null): CouponValidationDTO;

    /**
     * Calculate the exact discount amount for a validated coupon.
     *
     * @param Coupon $coupon
     * @param array $items
     * @param float $subtotal
     * @return float
     */
    public function calculateDiscount(Coupon $coupon, array $items, float $subtotal): float;

    /**
     * Apply a coupon, triggering validations and locks.
     *
     * @param string $code
     * @param array $items
     * @param float $subtotal
     * @param int|null $userId
     * @return array [coupon_code, discount_amount]
     */
    public function applyCoupon(string $code, array $items, float $subtotal, ?int $userId = null): array;
}
