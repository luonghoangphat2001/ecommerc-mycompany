<?php

namespace App\Ecommerce\Loyalty\Actions;

use App\Ecommerce\Loyalty\Concerns\ProcessesLoyaltyPointAdjustment;
use Illuminate\Support\Facades\DB;

class AwardPointsAction
{
    use ProcessesLoyaltyPointAdjustment;

    /**
     * Increment accumulated token allocations on purchase completions safely.
     *
     * @throws Exception
     */
    public function execute(int $userId, int $orderId, int $points): bool
    {
        return $this->processPointAdjustment($userId, $orderId, $points, 'earn', $points, function () use ($userId, $points) {
            DB::table('shop_loyalty_points')
                ->updateOrInsert(
                    ['user_id' => $userId],
                    [
                        'current_points' => DB::raw("current_points + {$points}"),
                        'lifetime_points' => DB::raw("lifetime_points + {$points}"),
                        'updated_at' => now(),
                    ]
                );
        });
    }
}
