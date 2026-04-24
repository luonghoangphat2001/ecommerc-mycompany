<?php

namespace App\Services\Order\Pipes;

use App\DTOs\Checkout\CheckoutResultDTO;
use App\DTOs\Checkout\CheckoutRequestDTO;
use App\Services\Shipping\ShippingManager;
use Closure;

class ResolveShippingFeePipe
{
    protected $shippingManager;

    public function __construct(ShippingManager $shippingManager)
    {
        $this->shippingManager = $shippingManager;
    }

    public function handle(array $passable, Closure $next)
    {
        /** @var CheckoutRequestDTO $request */
        $request = $passable['request'];
        /** @var CheckoutResultDTO $result */
        $result = $passable['result'];

        if (!$request->shippingMethod || !$request->shippingAddress) {
            return $next($passable);
        }

        try {
            /** @var \App\Models\ShippingMethod $method */
            $method = \App\Models\ShippingMethod::query()->find($request->shippingMethod);

            if (!$method || !$method->is_enabled) {
                return $next($passable);
            }

            $driver = $this->shippingManager->driver($method->type);
            
            $shippingCost = $driver->calculateFee(
                $result->subtotal,
                $request->shippingAddress->toArray(),
                array_merge($method->settings ?? [], ['currency' => $request->currency])
            );

            $result->shippingTotal = (int) $shippingCost;
            $result->total += (int) $shippingCost;

        } catch (\Exception $e) {
            // Log or handle error
        }

        return $next($passable);
    }
}
