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
}
