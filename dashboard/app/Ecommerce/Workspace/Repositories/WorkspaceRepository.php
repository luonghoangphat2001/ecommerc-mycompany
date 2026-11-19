<?php

namespace App\Ecommerce\Workspace\Repositories;

use App\Models\Department;
use App\Models\Product;

class WorkspaceRepository implements WorkspaceRepositoryInterface
{
    public function __construct(
        private readonly CfoRepositoryInterface $cfoRepository,
        private readonly LogisticsRepositoryInterface $logisticsRepository,
        private readonly OpsRepositoryInterface $opsRepository,
        private readonly CskhRepositoryInterface $cskhRepository,
        private readonly HrRepositoryInterface $hrRepository
    ) {}

    public function getCfoMetrics(): array
    {
        return $this->cfoRepository->getMetrics();
    }

    public function getLogisticsMetrics(): array
    {
        return $this->logisticsRepository->getMetrics();
    }

    public function getRndMetrics(): array
    {
        return [
            'new_products' => Product::where('created_at', '>=', now()->subDays(30))->count(),
            'pending_boms' => null,
            'active_experiments' => null,
            'innovation_index' => 'N/A',
        ];
    }

    public function getOpsMetrics(): array
    {
        return $this->opsRepository->getMetrics();
    }

    public function getCskhMetrics(): array
    {
        return $this->cskhRepository->getMetrics();
    }

    public function getHrMetrics(): array
    {
        return $this->hrRepository->getMetrics();
    }

    public function getDefaultWorkspaceData(string $departmentCode): array
    {
        $department = Department::where('code', $departmentCode)->firstOrFail();
        $agents = $department->agents()->get();

        return [
            'metrics' => [
                'total_agents' => $agents->count(),
                'active_agents' => $agents->where('status', 'active')->count(),
            ],
            'agents' => $agents,
            'department' => $department,
        ];
    }
}
