<?php

namespace App\Services\Order;

use App\DTOs\Checkout\CheckoutResultDTO;
use App\DTOs\Checkout\CheckoutRequestDTO;
use App\Services\Order\Pipes\ApplyDiscount;
use App\Services\Order\Pipes\ApplyExchangeRate;
use App\Services\Order\Pipes\CalculateSubtotal;
use App\Services\Order\Pipes\CalculateTax;
use App\Services\Order\Pipes\ResolveShippingFeePipe;
use App\Services\Order\Pipes\ResolveTaxRatePipe;
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
