<?php

namespace App\Ecommerce\Checkout\Services\Pipes;

use App\Ecommerce\Checkout\DTOs\CheckoutResultDTO;
use App\Ecommerce\Checkout\DTOs\CheckoutRequestDTO;
use App\Settings\LoyaltySettings;
use App\Ecommerce\Loyalty\Contracts\LoyaltyRepositoryInterface;
use Closure;

class ApplyLoyaltyPoints
{
    public function handle(array $passable, Closure $next)
    {
        /** @var CheckoutRequestDTO $request */
        $request = $passable['request'];
        /** @var CheckoutResultDTO $result */
        $result = $passable['result'];

        $loyaltySettings = app(LoyaltySettings::class);

        if (!$loyaltySettings->enabled || !$request->redeemPoints || $request->redeemPoints <= 0) {
            return $next($passable);
        }

        // Ensure points don't exceed current user balance if logged in
        $userId = auth()->id();
        if ($userId) {
            $loyaltyRepo = app(LoyaltyRepositoryInterface::class);
            $pointsRecord = $loyaltyRepo->findOrCreateByUserId($userId);
            $userPoints = $pointsRecord->current_points ?? 0;

            $request->redeemPoints = min($request->redeemPoints, $userPoints);
        }

        // Calculate monetary discount
        $pointsValue = $request->redeemPoints * $loyaltySettings->point_conversion_rate;

        // Cap discount to maximum order subtotal
        $discountAmount = (int) min($pointsValue, $result->total);

        $result->loyaltyDiscountTotal = $discountAmount;
        $result->total = max(0, $result->total - $discountAmount);

        return $next($passable);
    }
}
