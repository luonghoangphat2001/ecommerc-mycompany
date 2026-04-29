<?php

namespace App\Ecommerce\Loyalty\Actions;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class AwardPointsAction
{
    /**
     * Increment accumulated token allocations on purchase completions safely.
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
            // 1. Update current points
            $affected = DB::table('shop_loyalty_points')
                ->updateOrInsert(
                    ['user_id' => $userId],
                    [
                        'current_points' => DB::raw("current_points + {$points}"),
                        'lifetime_points' => DB::raw("lifetime_points + {$points}"),
                        'updated_at' => Carbon::now(),
                    ]
                );

            // 2. Write explicit audit logs
            DB::table('shop_loyalty_logs')->insert([
                'user_id' => $userId,
                'points_changed' => $points,
                'action_type' => 'earn',
                'order_id' => $orderId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return true;
        }, 3);
    }
}
