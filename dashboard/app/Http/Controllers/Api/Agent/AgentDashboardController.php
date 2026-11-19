<?php

namespace App\Http\Controllers\Api\Agent;

use App\Ecommerce\Agent\Contracts\AgentDashboardServiceInterface;
use App\Enums\MetricPeriod;
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

        return $this->integrationResponse($this->service->getConnectionStatus($agent));
    }

    public function metrics(Request $request): JsonResponse
    {
        /** @var DepartmentAgent $agent */
        $agent = $request->attributes->get('agent');
        $period = (string) $request->query('period', MetricPeriod::Today->value);
        $metricPeriod = MetricPeriod::tryFrom($period);

        if (! $metricPeriod) {
            return response()->json([
                'ok' => false,
                'error' => 'period must be today, month, quarter, or year',
            ], 422);
        }

        return $this->integrationResponse($this->service->getMetrics(
            $agent,
            $metricPeriod->value,
        ));
    }

    private function integrationResponse(mixed $integration): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'integration' => $integration,
        ]);
    }
}
