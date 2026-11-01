<?php

namespace App\Ecommerce\Checkout\Services\Pipes;

use App\Ecommerce\Checkout\DTOs\CheckoutRequestDTO;
use App\Ecommerce\Checkout\DTOs\CheckoutResultDTO;
use App\Settings\CheckoutSettings;
use App\Settings\CouponSettings;
use Closure;

class CalculateTax
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

        $checkoutSettings = app(CheckoutSettings::class);
        $couponSettings = app(CouponSettings::class);

        $policy = app(\App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface::class);

        if (!$policy->isTaxCalculationEnabled()) {
            $result->taxTotal = 0;
            return $next($passable);
        }

        $includeTax = (bool) ($checkoutSettings->prices_include_tax ?? false);
        $taxAfterCoupon = (bool) ($couponSettings->calculate_tax_after_coupon ?? true);
        $resolvedRates = $passable['resolved_tax_rates'] ?? [];

        $totalEligibleSubtotal = collect($request->items)->sum(fn($i) => (int) ($i['total'] ?? 0));
        $totalDiscount = (int) ($result->discountTotal ?? 0);

        $taxTotal = 0;
        foreach ($request->items as $item) {
            $taxTotal += $this->calculateItemTax(
                $item,
                $resolvedRates,
                $includeTax,
                $taxAfterCoupon,
                $totalDiscount,
                $totalEligibleSubtotal,
                $result
            );
        }

        $result->taxTotal = $taxTotal;
        if (!$includeTax) {
            $result->total += $taxTotal;
        }

        return $next($passable);
    }

    /**
     * Calculate tax for an individual cart/order item (single or combo).
     */
    private function calculateItemTax(
        array $item,
        array $resolvedRates,
        bool $includeTax,
        bool $taxAfterCoupon,
        int $totalDiscount,
        int $totalEligibleSubtotal,
        CheckoutResultDTO $result
    ): int {
        $itemTotal = (int) ($item['total'] ?? 0);
        if ($itemTotal <= 0) {
            return 0;
        }

        // 1. Combo breakdown with mixed tax classes
        if (!empty($item['tax_breakdown']) && is_array($item['tax_breakdown'])) {
            return $this->calculateComboBreakdownTax(
                $item['tax_breakdown'],
                $resolvedRates,
                $includeTax,
                $taxAfterCoupon,
                $totalDiscount,
                $totalEligibleSubtotal,
                $result
            );
        }

        // 2. Standard item tax
        return $this->calculateStandardItemTax(
            $item,
            $resolvedRates,
            $includeTax,
            $taxAfterCoupon,
            $totalDiscount,
            $totalEligibleSubtotal,
            $result
        );
    }

    /**
     * Calculate tax across combo component breakdown.
     */
    private function calculateComboBreakdownTax(
        array $breakdown,
        array $resolvedRates,
        bool $includeTax,
        bool $taxAfterCoupon,
        int $totalDiscount,
        int $totalSubtotal,
        CheckoutResultDTO $result
    ): int {
        $totalComboTax = 0;
        $discountRatio = ($taxAfterCoupon && $totalDiscount > 0 && $totalSubtotal > 0)
            ? ($totalDiscount / $totalSubtotal)
            : 0;

        foreach ($breakdown as $part) {
            $classId = $part['tax_class_id'] ?? null;
            $rate = $classId ? ($resolvedRates[$classId] ?? null) : null;
            if (!$rate) {
                continue;
            }

            $partAmount = (int) ($part['allocated_amount'] ?? 0);
            if ($discountRatio > 0) {
                $partAmount = max(0, (int) round($partAmount * (1 - $discountRatio)));
            }

            $ratePercent = (float) $rate->rate;
            $partTax = $this->computeTaxFromAmount($partAmount, $ratePercent, $includeTax);

            $totalComboTax += $partTax;
            $result->appliedTaxes[] = [
                'name' => ($part['name'] ?? 'Combo Item') . " ({$rate->name})",
                'amount' => $partTax,
                'rate' => $ratePercent,
            ];
        }

        return $totalComboTax;
    }

    /**
     * Calculate tax for standard product item.
     */
    private function calculateStandardItemTax(
        array $item,
        array $resolvedRates,
        bool $includeTax,
        bool $taxAfterCoupon,
        int $totalDiscount,
        int $totalSubtotal,
        CheckoutResultDTO $result
    ): int {
        $classId = $item['tax_class_id'] ?? null;
        if (!$classId) {
            return 0;
        }

        $rate = $resolvedRates[$classId] ?? null;
        if (!$rate) {
            return 0;
        }

        $taxableBase = (int) ($item['total'] ?? 0);
        if ($taxAfterCoupon && $totalDiscount > 0 && $totalSubtotal > 0) {
            $proportionalDiscount = ($taxableBase / $totalSubtotal) * $totalDiscount;
            $taxableBase = max(0, (int) round($taxableBase - $proportionalDiscount));
        }

        $ratePercent = (float) $rate->rate;
        $taxAmount = $this->computeTaxFromAmount($taxableBase, $ratePercent, $includeTax);

        $result->appliedTaxes[] = [
            'name' => $rate->name,
            'amount' => $taxAmount,
            'rate' => $ratePercent,
        ];

        return $taxAmount;
    }

    /**
     * Compute tax from amount based on tax inclusive or exclusive settings.
     */
    private function computeTaxFromAmount(int $amount, float $ratePercent, bool $includeTax): int
    {
        if ($amount <= 0 || $ratePercent <= 0) {
            return 0;
        }

        if ($includeTax) {
            return (int) round($amount - ($amount / (1 + ($ratePercent / 100))));
        }

        return (int) round($amount * ($ratePercent / 100));
    }
}
