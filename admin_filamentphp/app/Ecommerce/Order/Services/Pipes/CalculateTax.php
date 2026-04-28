<?php

namespace App\Ecommerce\Order\Services\Pipes;

use App\Ecommerce\Order\DTOs\Checkout\CheckoutResultDTO;
use App\Ecommerce\Order\DTOs\Checkout\CheckoutRequestDTO;
use App\Ecommerce\Settings\Contracts\SettingServiceInterface;
use App\Ecommerce\Core\Helpers\PriceHelper;
use App\Settings\CheckoutSettings;
use Closure;

class CalculateTax
{
    protected $settingService;

    public function __construct(
        SettingServiceInterface $settingService
    ) {
        $this->settingService = $settingService;
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

        $settings = app(CheckoutSettings::class);
        $includeTax = $settings->prices_include_tax;
        $taxTotal = 0;
        $resolvedRates = $passable['resolved_tax_rates'] ?? [];

        foreach ($request->items as $item) {
            if (isset($item['tax_class_id'])) {
                $rate = $resolvedRates[$item['tax_class_id']] ?? null;

                if ($rate) {
                    $ratePercent = (float) $rate->rate;
                    $taxAmount = 0;
                    $itemTotal = (int) ($item['total'] ?? 0);

                    if ($includeTax) {
                        // Amount in item total is gross. Tax = Gross - (Gross / (1 + Rate))
                        $taxAmount = (int) ($itemTotal - ($itemTotal / (1 + ($ratePercent / 100))));
                    } else {
                        // Amount in item total is net. Tax = Net * (Rate / 100)
                        $taxAmount = (int) ($itemTotal * ($ratePercent / 100));
                    }

                    $taxTotal += $taxAmount;
                    $result->appliedTaxes[] = [
                        'name' => $rate->name,
                        'amount' => $taxAmount,
                        'rate' => $ratePercent
                    ];
                }
            }
        }

        $result->taxTotal = $taxTotal;
        $result->total += $taxTotal;

        return $next([
            'request' => $request,
            'result' => $result
        ]);
    }
}
