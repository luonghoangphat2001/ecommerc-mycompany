<?php

namespace App\Ecommerce\Checkout\Services\Pipes;

use App\Ecommerce\Checkout\DTOs\CheckoutResultDTO;
use App\Ecommerce\Checkout\DTOs\CheckoutRequestDTO;
use App\Ecommerce\Product\Contracts\TaxRateRepositoryInterface;
use Closure;

class ResolveTaxRatePipe
{
    public function __construct(
        protected TaxRateRepositoryInterface $taxRateRepository
    ) {}

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

        $passable['resolved_tax_rates'] = [];

        foreach ($request->items as $item) {
            $classIds = [];
            if (!empty($item['tax_class_id'])) {
                $classIds[] = $item['tax_class_id'];
            }
            if (!empty($item['tax_breakdown']) && is_array($item['tax_breakdown'])) {
                foreach ($item['tax_breakdown'] as $part) {
                    if (!empty($part['tax_class_id'])) {
                        $classIds[] = $part['tax_class_id'];
                    }
                }
            }

            foreach (array_unique($classIds) as $classId) {
                if (!isset($passable['resolved_tax_rates'][$classId])) {
                    $rate = $this->taxRateRepository->findMatchingRate(
                        $classId,
                        $address->country_code,
                        $address->state_id,
                        $address->city_id
                    );

                    if ($rate) {
                        $passable['resolved_tax_rates'][$classId] = $rate;
                    }
                }
            }
        }

        return $next($passable);
    }
}
