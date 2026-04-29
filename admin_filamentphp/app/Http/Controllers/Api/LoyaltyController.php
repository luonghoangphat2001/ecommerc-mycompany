<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Settings\LoyaltySettings;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Loyalty
 *
 * APIs for managing user loyalty points
 */
class LoyaltyController extends Controller
{
    use ApiResponse;

    /**
     * Get current user's loyalty points and summary.
     *
     * @authenticated
     *
     * @response 200 {
     *   "data": {
     *     "current_points": 1000,
     *     "lifetime_points": 5000,
     *     "conversion_rate": 1000,
     *     "enabled": true
     *   }
     * }
     */
    public function getPoints(Request $request): JsonResponse
    {
        $settings = app(LoyaltySettings::class);

        if (!$settings->enabled) {
            return $this->ok([
                'enabled' => false,
                'current_points' => 0,
                'lifetime_points' => 0,
            ]);
        }

        $user = $request->user();

        $loyaltyPoint = $user->loyaltyPoint;

        return $this->ok([
            'enabled' => true,
            'current_points' => $loyaltyPoint?->current_points ?? 0,
            'lifetime_points' => $loyaltyPoint?->lifetime_points ?? 0,
            'points_per_currency' => $settings->points_per_currency,
            'point_conversion_rate' => $settings->point_conversion_rate,
        ]);
    }

    /**
     * Get current user's loyalty points history.
     *
     * @authenticated
     *
     * @queryParam per_page integer Number of items per page. Example: 15
     * @queryParam page integer Page number. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "type": "earn",
     *       "points": 100,
     *       "description": "Order #123",
     *       "created_at": "2026-04-29 10:00:00"
     *     }
     *   ],
     *   "links": {},
     *   "meta": {}
     * }
     */
    public function getHistory(Request $request): JsonResponse
    {
        $settings = app(LoyaltySettings::class);

        if (!$settings->enabled) {
            return $this->ok([]);
        }

        $validated = $request->validate([
            'per_page' => 'integer|min:1|max:100',
            'page' => 'integer|min:1',
        ]);

        $user = $request->user();

        $logs = $user->loyaltyLogs()
            ->orderBy('created_at', 'desc')
            ->paginate($validated['per_page'] ?? 15);

        return $this->ok($logs->map(fn($log) => [
            'id' => $log->id,
            'action_type' => $log->action_type,
            'points_changed' => $log->points_changed,
            'order_id' => $log->order_id,
            'expired_at' => $log->expired_at?->toDateTimeString(),
            'created_at' => $log->created_at->toDateTimeString(),
        ]));
    }
}
