<?php

namespace App\Ecommerce\Order\Services\Pipes;

use App\Ecommerce\Order\DTOs\Checkout\CheckoutResultDTO;
use App\Ecommerce\Order\DTOs\Checkout\CheckoutRequestDTO;
use App\Ecommerce\Core\Helpers\PriceHelper;
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

        return $next([
            'request' => $request,
            'result' => $result
        ]);
    }
}
