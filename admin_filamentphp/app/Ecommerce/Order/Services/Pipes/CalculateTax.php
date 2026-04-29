<?php

namespace App\Ecommerce\Order\Services\Pipes;

use App\Ecommerce\Order\DTOs\Checkout\CheckoutResultDTO;
use App\Ecommerce\Order\DTOs\Checkout\CheckoutRequestDTO;
use App\Ecommerce\Settings\Contracts\SettingServiceInterface;
use App\Ecommerce\Core\Helpers\PriceHelper;
use App\Settings\CheckoutSettings;
use App\Settings\CouponSettings;
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

        $checkoutSettings = app(CheckoutSettings::class);
        $couponSettings = app(CouponSettings::class);

        $includeTax = $checkoutSettings->prices_include_tax;
        $taxAfterCoupon = $couponSettings->calculate_tax_after_coupon ?? true;
        
        $taxTotal = 0;
        $resolvedRates = $passable['resolved_tax_rates'] ?? [];

        // Calculate total subtotal to distribute discount proportionally
        $totalEligibleSubtotal = collect($request->items)->sum(fn($i) => (int)($i['total'] ?? 0));
        $totalDiscount = (int)($result->discountTotal ?? 0);

        foreach ($request->items as $item) {
            if (isset($item['tax_class_id'])) {
                $rate = $resolvedRates[$item['tax_class_id']] ?? null;

                if ($rate) {
                    $ratePercent = (float) $rate->rate;
                    $taxAmount = 0;
                    $itemTotal = (int) ($item['total'] ?? 0);

                    // If tax is calculated after coupon, deduct proportional discount
                    if ($taxAfterCoupon && $totalDiscount > 0 && $totalEligibleSubtotal > 0) {
                        $itemProportionalDiscount = ($itemTotal / $totalEligibleSubtotal) * $totalDiscount;
                        $itemTotal = max(0, (int)($itemTotal - $itemProportionalDiscount));
                    }

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
