<?php

namespace App\Ecommerce\Checkout\Services;

use App\Ecommerce\Checkout\DTOs\CheckoutResultDTO;
use App\Ecommerce\Checkout\DTOs\CheckoutRequestDTO;
use App\Ecommerce\Checkout\Services\Pipes\ApplyDiscount;
use App\Ecommerce\Checkout\Services\Pipes\ApplyExchangeRate;
use App\Ecommerce\Checkout\Services\Pipes\CalculateSubtotal;
use App\Ecommerce\Checkout\Services\Pipes\CalculateTax;
use App\Ecommerce\Checkout\Services\Pipes\ResolveShippingFeePipe;
use App\Ecommerce\Checkout\Services\Pipes\ResolveTaxRatePipe;
use App\Ecommerce\Checkout\Services\Pipes\ApplyLoyaltyPoints;
use App\Ecommerce\Checkout\Services\Pipes\ApplyMarketing;

use App\Settings\CheckoutSettings;
use App\Settings\CouponSettings;
use Illuminate\Pipeline\Pipeline;

class CheckoutCalculatorService
{
    protected CheckoutSettings $checkoutSettings;
    protected CouponSettings $couponSettings;

    public function __construct(CheckoutSettings $checkoutSettings, CouponSettings $couponSettings)
    {
        $this->checkoutSettings = $checkoutSettings;
        $this->couponSettings = $couponSettings;
    }

    /**
     * Calculate order totals using a pure pipeline.
     *
     * @param CheckoutRequestDTO $request
     * @return CheckoutResultDTO
     */
    public function calculate(CheckoutRequestDTO $request): CheckoutResultDTO
    {
        $pipes = [
            CalculateSubtotal::class,
            ApplyExchangeRate::class,
            ApplyLoyaltyPoints::class,
            ApplyMarketing::class,
        ];

        // Inject tax pipes conditionally
        if ($this->checkoutSettings->enable_tax) {
            array_unshift($pipes, ResolveTaxRatePipe::class);
            $pipes[] = CalculateTax::class;
        }

        // Inject discount pipe conditionally
        if ($this->couponSettings->enable_coupons) {
            array_splice($pipes, array_search(ApplyExchangeRate::class, $pipes) + 1, 0, ApplyDiscount::class);
        }

        // Inject shipping pipe conditionally
        if ($this->checkoutSettings->enable_shipping) {
            $pipes[] = ResolveShippingFeePipe::class;
        }

        $result = app(Pipeline::class)
            ->send([
                'request' => $request,
                'result' => new CheckoutResultDTO(currency: $request->currency)
            ])
            ->through($pipes)
            ->then(function ($passable) {
                return $passable['result'];
            });

        return $result;
    }
}
