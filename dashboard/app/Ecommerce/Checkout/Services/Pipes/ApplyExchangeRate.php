<?php

namespace App\Ecommerce\Checkout\Services\Pipes;

use App\Ecommerce\Checkout\DTOs\CheckoutResultDTO;
use App\Ecommerce\Checkout\DTOs\CheckoutRequestDTO;
use App\Settings\DBSettings;
use App\Ecommerce\Core\Helpers\PriceHelper;
use Closure;

class ApplyExchangeRate
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

        $settings = app(DBSettings::class);
        $baseCurrency = $settings->currency ?? 'VND';
        $targetCurrency = $request->currency;

        $rate = (float) ($settings->exchange_rates[$targetCurrency] ?? 1.0);

        if ($targetCurrency !== $baseCurrency) {
            // Adjust totals based on exchange rate
            // Note: If storing in cents, we must be careful.
            // Result is ALWAYS in the target currency.
            $result->subtotal = PriceHelper::round($result->subtotal * $rate);
            $result->total = PriceHelper::round($result->total * $rate);
        }

        $result->currency = $targetCurrency;
        $result->exchangeRate = $rate;

        return $next($passable);
    }
}
