<?php

namespace App\Http\Controllers\Api\Agent;

use App\Ecommerce\Agent\Contracts\AgentDashboardServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\DepartmentAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentDashboardController extends Controller
{
    public function __construct(
        private readonly AgentDashboardServiceInterface $service,
    ) {}

    public function health(Request $request): JsonResponse
    {
        /** @var DepartmentAgent $agent */
        $agent = $request->attributes->get('agent');

        return response()->json([
            'ok' => true,
            'integration' => $this->service->getConnectionStatus($agent),
        ]);
    }

    public function metrics(Request $request): JsonResponse
    {
        /** @var DepartmentAgent $agent */
        $agent = $request->attributes->get('agent');
        $period = (string) $request->query('period', 'today');
        if (!in_array($period, ['today', 'month', 'quarter', 'year'], true)) {
            return response()->json([
                'ok' => false,
                'error' => 'period must be today, month, quarter, or year',
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'integration' => $this->service->getMetrics(
                $agent,
                $period,
            ),
        ]);
    }
}
