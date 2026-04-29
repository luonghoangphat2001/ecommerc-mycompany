<?php

namespace App\Ecommerce\Checkout\Services\Pipes;

use App\Ecommerce\Checkout\DTOs\CheckoutResultDTO;
use App\Ecommerce\Checkout\DTOs\CheckoutRequestDTO;
use App\Ecommerce\Shipping\Services\ShippingManager;
use Closure;

class CalculateShipping
{
    protected $shippingManager;

    public function __construct(ShippingManager $shippingManager)
    {
        $this->shippingManager = $shippingManager;
    }

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

        if (!$request->shippingMethod) {
            return $next($passable);
        }

        // We assume the shippingMethod passed is the driver name or a structured identifier
        try {
            $driver = $this->shippingManager->driver($request->shippingMethod);

            $shippingCost = $driver->calculateFee(
                $result->subtotal,
                $request->shippingAddress ?? [],
                ['currency' => $request->currency]
            );

            $result->shippingTotal = $shippingCost;
            $result->total += $shippingCost;
        } catch (\Exception $e) {
            // Log error or handle unavailable shipping
        }

        return $next([
            'request' => $request,
            'result' => $result
        ]);
    }
}
