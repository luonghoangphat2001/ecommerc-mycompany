<?php

namespace App\Services\Order\Pipes;

use App\DTOs\Checkout\CheckoutResultDTO;
use App\DTOs\Checkout\CheckoutRequestDTO;
use App\Helpers\PriceHelper;
use Closure;

class ApplyDiscount
{
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

        // Placeholder for Coupon logic
        // In a real scenario, this would lookup a Coupon model
        $discountAmount = 0;
        if ($request->couponCode === 'DISCOUNT10') {
            $discountAmount = PriceHelper::round($result->subtotal * 0.1);
        }

        $result->discountTotal = $discountAmount;
        $result->total -= $discountAmount;

        return $next([
            'request' => $request,
            'result' => $result
        ]);
    }
}
