<?php

namespace App\Ecommerce\Coupon\Services\Checkers;

use App\Ecommerce\Coupon\Contracts\CouponRepositoryInterface;
use App\Ecommerce\Coupon\DTOs\CouponValidationDTO;
use App\Exceptions\CouponValidationException;
use Closure;

class UsageChecker
{
    public function __construct(
        protected CouponRepositoryInterface $couponRepository
    ) {}

    public function handle(CouponValidationDTO $dto, Closure $next)
    {
        $coupon = $dto->coupon;

        // 1. Check overall usage limit
        if ($coupon->usage_limit !== null && $coupon->usage_count >= $coupon->usage_limit) {
            throw new CouponValidationException(trans('messages.coupon_usage_limit_reached'));
        }

        // 2. Check per user usage limit
        if ($coupon->usage_limit_per_user !== null && $dto->userId !== null) {
            $userUsageCount = $this->couponRepository->getUsageCountForUser($coupon->code, $dto->userId);
            if ($userUsageCount >= $coupon->usage_limit_per_user) {
                throw new CouponValidationException(trans('messages.coupon_usage_limit_reached'));
            }
        }

        return $next($dto);
    }
}
