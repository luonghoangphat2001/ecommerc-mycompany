<?php

namespace App\Ecommerce\Loyalty\Repositories;

use App\Ecommerce\Loyalty\Contracts\LoyaltyRepositoryInterface;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyLog;
use Illuminate\Support\Facades\DB;

class EloquentLoyaltyRepository implements LoyaltyRepositoryInterface
{
    public function findOrCreateByUserId(int $userId)
    {
        $record = LoyaltyPoint::where('user_id', $userId)->first();
        if (!$record) {
            $record = new LoyaltyPoint();
            $record->user_id = $userId;
            $record->current_points = 0;
            $record->lifetime_points = 0;
            $record->save();
        }
        return $record;

    }

    public function incrementPoints(int $userId, int $points, int $lifetime): bool
    {
        $pointsRecord = $this->findOrCreateByUserId($userId);
        
        $pointsRecord->increment('current_points', $points);
        if ($lifetime > 0) {
            $pointsRecord->increment('lifetime_points', $lifetime);
        }

        return true;
    }

    public function recordLog(int $userId, int $points, string $actionType, ?int $orderId = null): bool
    {
        $log = new LoyaltyLog();
        $log->user_id = $userId;
        $log->points_changed = $points;
        $log->action_type = $actionType;
        $log->order_id = $orderId;
        $log->save();


        return true;
    }

    public function getPointsEarnedForOrder(int $orderId): int
    {
        return (int) DB::table('shop_loyalty_logs')
            ->where('order_id', $orderId)
            ->where('action_type', 'accrual')
            ->sum('points_changed');
    }
}

