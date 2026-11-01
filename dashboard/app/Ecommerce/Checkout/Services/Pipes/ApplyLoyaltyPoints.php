<?php

namespace App\Ecommerce\Checkout\Services\Pipes;

use App\Ecommerce\Checkout\DTOs\CheckoutResultDTO;
use App\Ecommerce\Checkout\DTOs\CheckoutRequestDTO;
use App\Settings\LoyaltySettings;
use App\Ecommerce\Loyalty\Contracts\LoyaltyRepositoryInterface;
use App\Models\Product;
use Closure;

class ApplyLoyaltyPoints
{
    public function handle(array $passable, Closure $next)
    {
        /** @var CheckoutRequestDTO $request */
        $request = $passable['request'];
        /** @var CheckoutResultDTO $result */
        $result = $passable['result'];

        $policy = app(\App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface::class);

        if (!$policy->isLoyaltyEnabled() || !$request->redeemPoints || $request->redeemPoints <= 0) {
            return $next($passable);
        }

        $loyaltySettings = app(LoyaltySettings::class);
        $minPoints = (int) ($loyaltySettings->minimum_points_to_redeem ?? 0);

        // Ensure points don't exceed current user balance if logged in
        $userId = auth()->id();
        if ($userId) {
            $loyaltyRepo = app(LoyaltyRepositoryInterface::class);
            $pointsRecord = $loyaltyRepo->findOrCreateByUserId($userId);
            $userPoints = $pointsRecord->current_points ?? 0;

            $request->redeemPoints = min($request->redeemPoints, $userPoints);
        }

        if ($request->redeemPoints < $minPoints || $request->redeemPoints <= 0) {
            return $next($passable);
        }

        // Calculate non-redeemable items total (products with is_loyalty_redeemable = false)
        $nonRedeemableTotal = 0;
        $productIds = collect($request->items)->pluck('product_id')->filter()->unique()->all();
        if (!empty($productIds)) {
            $nonRedeemableProductIds = Product::whereIn('id', $productIds)
                ->where('is_loyalty_redeemable', false)
                ->pluck('id')
                ->all();

            if (!empty($nonRedeemableProductIds)) {
                foreach ($request->items as $item) {
                    if (in_array($item['product_id'] ?? null, $nonRedeemableProductIds, true)) {
                        $nonRedeemableTotal += (float) ($item['total'] ?? (($item['price'] ?? 0) * ($item['qty'] ?? 1)));
                    }
                }
            }
        }

        $redeemableMaxBase = max(0, (float) $result->total - $nonRedeemableTotal);
        if ($redeemableMaxBase <= 0) {
            return $next($passable);
        }

        // Conversion rate (1 point = X currency)
        $conversionRate = $loyaltySettings->point_conversion_rate > 0 ? $loyaltySettings->point_conversion_rate : 1000;
        $pointsValue = $request->redeemPoints * $conversionRate;

        // Apply maximum redeemable percentage
        $maxPercentage = (int) ($loyaltySettings->max_redeem_percentage ?? 100);
        if ($maxPercentage <= 0 || $maxPercentage > 100) {
            $maxPercentage = 100;
        }

        $allowedMaxByPercent = (int) round(($redeemableMaxBase * $maxPercentage) / 100);
        $discountAmount = (int) min($pointsValue, $allowedMaxByPercent);

        if ($discountAmount > 0) {
            // Adjust redeemed points to reflect exact discount applied
            $actualRedeemedPoints = (int) ceil($discountAmount / $conversionRate);
            $request->redeemPoints = min($request->redeemPoints, $actualRedeemedPoints);

            $result->loyaltyDiscountTotal = $discountAmount;
            $result->total = max(0, $result->total - $discountAmount);
        }

        return $next($passable);
    }
}
