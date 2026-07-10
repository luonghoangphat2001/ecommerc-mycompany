<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Settings\LoyaltySettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;
use App\Swagger\Attributes\ApiGet;
use App\Swagger\Attributes\ApiList;

class LoyaltyController extends BaseApiController
{

    #[ApiGet(
        path: '/user/loyalty/points',
        summary: 'Get User Loyalty Points',
        tags: 'Storefront - Loyalty',
        requiresAuth: true,
        responseData: [
            new OAT\Property(property: 'enabled', type: 'boolean', example: true),
            new OAT\Property(property: 'current_points', type: 'integer', example: 100),
            new OAT\Property(property: 'lifetime_points', type: 'integer', example: 500),
            new OAT\Property(property: 'points_per_currency', type: 'integer', example: 1),
            new OAT\Property(property: 'point_conversion_rate', type: 'number', format: 'float', example: 1000)
        ]
    )]
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

    #[ApiList(
        path: '/user/loyalty/history',
        summary: 'Loyalty Points History',
        tags: 'Storefront - Loyalty',
        requiresAuth: true,
        responseData: [
            new OAT\Property(property: 'id', type: 'integer', example: 1),
            new OAT\Property(property: 'action_type', type: 'string', example: 'earn'),
            new OAT\Property(property: 'points_changed', type: 'integer', example: 10),
            new OAT\Property(property: 'order_id', type: 'integer', nullable: true, example: 123),
            new OAT\Property(property: 'expired_at', type: 'string', format: 'date-time', nullable: true),
            new OAT\Property(property: 'created_at', type: 'string', format: 'date-time')
        ]
    )]
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
