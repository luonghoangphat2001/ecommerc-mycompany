<?php

namespace App\Ecommerce\Coupon\Services\Checkers;

use App\Ecommerce\Coupon\DTOs\CouponValidationDTO;
use App\Exceptions\CouponValidationException;
use Closure;

class ExpiryChecker
{
    public function handle(CouponValidationDTO $dto, Closure $next)
    {
        $coupon = $dto->coupon;

        if ($coupon->expiry_date && $coupon->expiry_date->isPast()) {
            throw new CouponValidationException(trans('messages.coupon_expired'));
        }

        return $next($dto);
    }
}
