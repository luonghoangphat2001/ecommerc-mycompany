<?php

namespace App\Ecommerce\Order\Services;

use App\Ecommerce\Order\DTOs\Checkout\CheckoutResultDTO;
use App\Ecommerce\Order\DTOs\Checkout\CheckoutRequestDTO;
use App\Ecommerce\Order\Services\Pipes\ApplyDiscount;
use App\Ecommerce\Order\Services\Pipes\ApplyExchangeRate;
use App\Ecommerce\Order\Services\Pipes\CalculateSubtotal;
use App\Ecommerce\Order\Services\Pipes\CalculateTax;
use App\Ecommerce\Order\Services\Pipes\ResolveShippingFeePipe;
use App\Ecommerce\Order\Services\Pipes\ResolveTaxRatePipe;
use Illuminate\Pipeline\Pipeline;


class OrderCalculatorService
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
