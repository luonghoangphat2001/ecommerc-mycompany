<?php

namespace App\Ecommerce\Workspace\Repositories;

use App\Models\DepartmentEmployeeContract;
use App\Models\User;

class HrRepository implements HrRepositoryInterface
{
    public function getMetrics(string $period = 'all'): array
    {
        $totalAgents = User::count();
        $activeAgents = DepartmentEmployeeContract::where('end_date', '>=', now())->count();
        
        return [
            'total_agents' => $totalAgents,
            'active_agents' => $activeAgents,
            'blocked_actions' => 0, // Mock
            'risk_level' => 'Thấp', // Mock
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
        return DepartmentEmployeeContract::create($data);
    }

    public function updateContract($id, array $data)
    {
        $contract = DepartmentEmployeeContract::findOrFail($id);
        $contract->update($data);
        return $contract;
    }

    public function deleteContract($id)
    {
        $contract = DepartmentEmployeeContract::findOrFail($id);
        return $contract->delete();
    }
}
