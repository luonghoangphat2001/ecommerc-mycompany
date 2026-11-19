<?php

namespace App\Ecommerce\Loyalty\Actions;

use App\Ecommerce\Loyalty\Concerns\ProcessesLoyaltyPointAdjustment;
use Illuminate\Support\Facades\DB;

class ClawbackPointsAction
{
    use ProcessesLoyaltyPointAdjustment;

    /**
     * Remove points earned from cancelled orders securely.
     *
     * @throws Exception
     */
    public function execute(int $userId, int $orderId, int $points): bool
    {
        return $this->processPointAdjustment($userId, $orderId, $points, 'refund', -$points, function () use ($userId, $points) {
            DB::table('shop_loyalty_points')
                ->where('user_id', $userId)
                ->update([
                    'current_points' => DB::raw("GREATEST(0, current_points - {$points})"),
                    'updated_at' => now(),
                ]);
        });
    }
}
