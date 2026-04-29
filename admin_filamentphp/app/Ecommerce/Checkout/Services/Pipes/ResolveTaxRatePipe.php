<?php

namespace App\Ecommerce\Checkout\Services\Pipes;

use App\Ecommerce\Checkout\DTOs\CheckoutResultDTO;
use App\Ecommerce\Checkout\DTOs\CheckoutRequestDTO;
use App\Ecommerce\Product\Contracts\TaxRateRepositoryInterface;
use Closure;

class ResolveTaxRatePipe
{
    protected $taxRateRepository;

    public function __construct(TaxRateRepositoryInterface $taxRateRepository)
    {
        $this->taxRateRepository = $taxRateRepository;
    }

    public function handle(array $passable, Closure $next)
    {
        /** @var CheckoutRequestDTO $request */
        $request = $passable['request'];
        /** @var CheckoutResultDTO $result */
        $result = $passable['result'];

        $address = $request->shippingAddress;

        if (!$address) {
            return $next($passable);
        }

        // Store resolved rates in the result or passable to be used by CalculateTax
        $passable['resolved_tax_rates'] = [];

        foreach ($request->items as $item) {
            if (isset($item['tax_class_id'])) {
                $rate = $this->taxRateRepository->findMatchingRate(
                    $item['tax_class_id'],
                    $address->country_code,
                    $address->state_id,
                    $address->city_id
                );

                if ($rate) {
                    $passable['resolved_tax_rates'][$item['tax_class_id']] = $rate;
                }
            }
        }

        return $next($passable);
    }
}
