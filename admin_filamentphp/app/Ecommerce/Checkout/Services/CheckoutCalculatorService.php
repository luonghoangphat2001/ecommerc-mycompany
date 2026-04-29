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

use Illuminate\Pipeline\Pipeline;

class CheckoutCalculatorService
{
    /**
     * Calculate order totals using a pure pipeline.
     *
     * @param CheckoutRequestDTO $request
     * @return CheckoutResultDTO
     */
    public function calculate(CheckoutRequestDTO $request): CheckoutResultDTO
    {
        $pipes = [
            ResolveTaxRatePipe::class,
            CalculateSubtotal::class,
            ApplyExchangeRate::class,
            ApplyDiscount::class,
            ApplyLoyaltyPoints::class,
            ApplyMarketing::class,
            CalculateTax::class,
            ResolveShippingFeePipe::class,
        ];

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
