<?php

namespace App\Ecommerce\Agent\Services;

use App\Ecommerce\Agent\Contracts\AgentDashboardRepositoryInterface;
use App\Ecommerce\Agent\Contracts\AgentDashboardServiceInterface;
use App\Models\DepartmentAgent;

class AgentDashboardService implements AgentDashboardServiceInterface
{
    public function __construct(
        private readonly AgentDashboardRepositoryInterface $repository,
    ) {}

    public function getConnectionStatus(DepartmentAgent $agent): array
    {
        $agent->loadMissing('department');

        return [
            'service' => 'hpdev-company-dashboard',
            'status' => 'UP',
            'api_version' => 'v1',
            'agent' => [
                'code' => $agent->agent_code,
                'name' => $agent->name,
                'department' => $agent->department?->code,
            ],
            'operational_summary' => $this->repository->getOperationalSummary(),
            'checked_at' => now()->toIso8601String(),
        ];
    }
}
