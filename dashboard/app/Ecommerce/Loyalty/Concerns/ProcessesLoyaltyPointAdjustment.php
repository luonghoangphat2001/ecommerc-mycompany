<?php

namespace App\Ecommerce\Loyalty\Concerns;

use Closure;
use Illuminate\Support\Facades\DB;

trait ProcessesLoyaltyPointAdjustment
{
    protected function processPointAdjustment(
        int $userId,
        int $orderId,
        int $points,
        string $actionType,
        int $pointsChanged,
        Closure $updatePoints,
    ): bool {
        if ($points <= 0) {
            return false;
        }

        return DB::transaction(function () use ($userId, $orderId, $actionType, $updatePoints) {
            $updatePoints();
            $timestamp = now();

            DB::table('shop_loyalty_logs')->insert([
                'user_id' => $userId,
                'points_changed' => $pointsChanged,
                'action_type' => $actionType,
                'order_id' => $orderId,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            return true;
        }, 3);
    }
}
