<?php

namespace App\Ecommerce\Loyalty\Actions;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class ClawbackPointsAction
{
    /**
     * Remove points earned from cancelled orders securely.
     *
     * @param int $userId
     * @param int $orderId
     * @param int $points
     * @return bool
     * @throws Exception
     */
    public function execute(int $userId, int $orderId, int $points): bool
    {
        if ($points <= 0) {
            return false;
        }

        return DB::transaction(function () use ($userId, $orderId, $points) {
            // 1. Remove awarded rewards
            DB::table('shop_loyalty_points')
                ->where('user_id', $userId)
                ->update([
                    'current_points' => DB::raw("GREATEST(0, current_points - {$points})"),
                    'updated_at' => Carbon::now(),
                ]);

            // 2. Write explicit transaction logs
            DB::table('shop_loyalty_logs')->insert([
                'user_id' => $userId,
                'points_changed' => -$points,
                'action_type' => 'refund',
                'order_id' => $orderId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return true;
        }, 3);
    }
}
