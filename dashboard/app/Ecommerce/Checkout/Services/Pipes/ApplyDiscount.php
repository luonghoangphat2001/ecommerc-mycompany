<?php

namespace App\Ecommerce\Checkout\Services\Pipes;

use App\Ecommerce\Checkout\DTOs\CheckoutResultDTO;
use App\Ecommerce\Checkout\DTOs\CheckoutRequestDTO;
use Closure;

class ApplyDiscount
{
    public function __construct(
        protected \App\Ecommerce\Coupon\Contracts\CouponServiceInterface $couponService
    ) {}

    /**
     * Handle the pipe.
     *
     * @param array $passable ['request' => CheckoutRequestDTO, 'result' => CheckoutResultDTO]
     * @param Closure $next
     * @return mixed
     */
    public function handle(array $passable, Closure $next)
    {
        /** @var CheckoutRequestDTO $request */
        $request = $passable['request'];
        /** @var CheckoutResultDTO $result */
        $result = $passable['result'];

        if (!$request->couponCode) {
            return $next($passable);
        }

        try {
            // 1. Validate without incrementing usage count
            $dto = $this->couponService->validateCoupon(
                $request->couponCode,
                $request->items,
                (float)$result->subtotal
            );

            // 2. Calculate discount
            $discountAmount = $this->couponService->calculateDiscount(
                $dto->coupon,
                $request->items,
                (float)$result->subtotal
            );
        } catch (\App\Exceptions\CouponValidationException $e) {
            // If validation fails during checkout calc, just ignore the discount
            $discountAmount = 0;
        }

        $result->discountTotal = $discountAmount;
        $result->total -= $discountAmount;

        return $next($passable);
    }
}
