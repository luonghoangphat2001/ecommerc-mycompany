<?php

namespace App\Ecommerce\Workspace\Repositories;

use App\Ecommerce\Workspace\Repositories\Concerns\HasWorkspaceCrudOperations;
use App\Models\DepartmentEmployeeContract;
use App\Models\User;
use Carbon\Carbon;

class HrRepository implements HrRepositoryInterface
{
    use HasWorkspaceCrudOperations;

    public function getMetrics(string $period = 'all'): array
    {
        $totalAgents = User::count();
        $activeAgents = DepartmentEmployeeContract::where('end_date', '>=', now())->count();

        return [
            'total_agents' => $totalAgents,
            'active_agents' => $activeAgents,
            'blocked_actions' => null,
            'risk_level' => 'N/A',
        ];
    }

    public function getContracts(string $period = 'all')
    {
        return DepartmentEmployeeContract::with(['user', 'department'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getActiveContracts(string $period = 'all')
    {
        return DepartmentEmployeeContract::with('user')
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', Carbon::today());
            })
            ->get();
    }

    public function createContract(array $data)
    {
        return $this->createWorkspaceModel(DepartmentEmployeeContract::class, $data);
    }

    public function updateContract($id, array $data)
    {
        return $this->updateWorkspaceModel(DepartmentEmployeeContract::class, $id, $data);
    }

    public function deleteContract($id)
    {
        return $this->deleteWorkspaceModel(DepartmentEmployeeContract::class, $id);
    }
}
