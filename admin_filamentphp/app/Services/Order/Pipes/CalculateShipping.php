<?php

namespace App\Services\Order\Pipes;

use App\DTOs\Checkout\CheckoutResultDTO;
use App\DTOs\Checkout\CheckoutRequestDTO;
use App\Services\Shipping\ShippingManager;
use App\Helpers\PriceHelper;
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
        // In a real scenario, this would lookup the ShippingMethod model's type
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
