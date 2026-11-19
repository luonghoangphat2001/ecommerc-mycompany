<?php

namespace App\Ecommerce\Checkout\Services\Pipes;

use App\Ecommerce\Checkout\DTOs\CheckoutRequestDTO;
use App\Ecommerce\Checkout\DTOs\CheckoutResultDTO;
use App\Ecommerce\Core\Helpers\PriceHelper;
use App\Settings\DBSettings;
use Closure;

class ApplyExchangeRate
{
    public function __construct(protected DBSettings $settings) {}

    /**
     * Handle the pipe.
     *
     * @param  array  $passable  ['request' => CheckoutRequestDTO, 'result' => CheckoutResultDTO]
     * @return mixed
     */
    public function handle(array $passable, Closure $next)
    {
        /** @var CheckoutRequestDTO $request */
        $request = $passable['request'];
        /** @var CheckoutResultDTO $result */
        $result = $passable['result'];

        $baseCurrency = $this->settings->currency ?? 'VND';
        $targetCurrency = $request->currency;

        $rate = (float) ($this->settings->exchange_rates[$targetCurrency] ?? 1.0);

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
