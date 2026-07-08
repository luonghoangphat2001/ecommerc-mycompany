<?php

namespace App\Ecommerce\Coupon\Services\Checkers;

use App\Ecommerce\Coupon\DTOs\CouponValidationDTO;
use App\Exceptions\CouponValidationException;
use Closure;

class RequirementChecker
{
    public function handle(CouponValidationDTO $dto, Closure $next)
    {
        $coupon = $dto->coupon;

        if ($coupon->minimum_order_amount !== null && $dto->subtotal < $coupon->minimum_order_amount) {
            throw new CouponValidationException(trans('messages.coupon_min_amount_not_met'));
        }

        return $next($dto);
    }
}
