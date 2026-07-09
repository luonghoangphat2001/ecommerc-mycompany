<?php

namespace App\Ecommerce\Checkout\Services\Pipes;

use App\Ecommerce\Checkout\DTOs\CheckoutResultDTO;
use App\Ecommerce\Checkout\DTOs\CheckoutRequestDTO;
use App\Settings\CheckoutSettings;
use Closure;

class CalculateSubtotal
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

        $subtotal = 0;
        $settings = app(CheckoutSettings::class);
        $resolvedRates = $passable['resolved_tax_rates'] ?? [];

        foreach ($request->items as $item) {
            $itemTotal = (int) ($item['total'] ?? 0);
            
            if ($settings->prices_include_tax && isset($item['tax_class_id'])) {
                $rate = $resolvedRates[$item['tax_class_id']]->rate ?? 0;
                // net = total / (1 + rate / 100)
                $itemNet = (int) ($itemTotal / (1 + ($rate / 100)));
                $subtotal += $itemNet;
            } else {
                $subtotal += $itemTotal;
            }
        }

        $result->subtotal = $subtotal;
        $result->total = $subtotal;

        return $next($passable);
    }
}
