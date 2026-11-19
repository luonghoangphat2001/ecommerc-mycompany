<?php

namespace App\Ecommerce\Workspace\Repositories;

use App\Ecommerce\Workspace\Repositories\Concerns\HasWorkspaceCrudOperations;
use App\Models\DepartmentFinancialProposal;
use App\Models\DepartmentMaterialPrice;
use App\Models\DepartmentPayroll;
use App\Models\Order;
use Carbon\Carbon;

class CfoRepository implements CfoRepositoryInterface
{
    use HasWorkspaceCrudOperations;

    public function getMetrics(string $period = 'all'): array
    {
        $queryProposals = DepartmentFinancialProposal::query();
        $queryPayrolls = DepartmentPayroll::query();

        $this->applyPeriodFilter($queryProposals, 'created_at', $period);
        $this->applyPeriodFilter($queryPayrolls, 'created_at', $period);

        $revenueQuery = Order::query()->whereNotIn('status', ['cancelled', 'refunded']);
        $this->applyPeriodFilter($revenueQuery, 'created_at', $period);
        $revenue = (float) $revenueQuery->sum('total');
        $approvedProposalExpenses = (clone $queryProposals)->where('status', 'approved')->sum('amount');
        $expenses = $queryPayrolls->sum('net_salary') + $approvedProposalExpenses;
        $proposalMetrics = (clone $queryProposals)
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count")
            ->selectRaw("SUM(CASE WHEN status = 'pending' AND is_urgent = 1 THEN 1 ELSE 0 END) as urgent_count")
            ->first();
        $pendingCount = (int) ($proposalMetrics->pending_count ?? 0);
        $urgentCount = (int) ($proposalMetrics->urgent_count ?? 0);
        $cashflow = $revenue - $expenses;

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'pending_proposals' => $pendingCount,
            'urgent_proposals' => $urgentCount,
            'cashflow' => $cashflow,
            'cashflow_status' => $cashflow > 0 ? 'Tốt' : 'Rủi ro',
        ];
    }

    public function getProposals(string $period = 'all')
    {
        $query = DepartmentFinancialProposal::with('user');
        $this->applyPeriodFilter($query, 'created_at', $period);

        return $query->latest()->get();
    }

    public function getPayrolls(string $period = 'all')
    {
        $query = DepartmentPayroll::with('user');
        $this->applyPeriodFilter($query, 'created_at', $period);

        return $query->latest()->get();
    }

    public function getMaterialPrices(string $period = 'all')
    {
        $query = DepartmentMaterialPrice::query();
        $this->applyPeriodFilter($query, 'recorded_at', $period);

        return $query->orderBy('recorded_at', 'asc')->get();
    }

    private function applyPeriodFilter($query, $column, $period)
    {
        $now = Carbon::now();
        if ($period === 'month') {
            $query->whereMonth($column, $now->month)->whereYear($column, $now->year);
        } elseif ($period === 'quarter') {
            $query->whereRaw('QUARTER(' . $column . ') = ?', [$now->quarter])->whereYear($column, $now->year);
        } elseif ($period === 'year') {
            $query->whereYear($column, $now->year);
        }
    }

    public function createProposal(array $data)
    {
        return $this->createWorkspaceModel(DepartmentFinancialProposal::class, $data);
    }

    public function updateProposal($id, array $data)
    {
        return $this->updateWorkspaceModel(DepartmentFinancialProposal::class, $id, $data);
    }

    public function deleteProposal($id)
    {
        return $this->deleteWorkspaceModel(DepartmentFinancialProposal::class, $id);
    }

    public function updateProposalStatus($id, string $status)
    {
        return $this->updateWorkspaceModel(DepartmentFinancialProposal::class, $id, ['status' => $status]);
    }

    public function createPayroll(array $data)
    {
        return $this->createWorkspaceModel(DepartmentPayroll::class, $data);
    }

    public function updatePayroll($id, array $data)
    {
        return $this->updateWorkspaceModel(DepartmentPayroll::class, $id, $data);
    }

    public function deletePayroll($id)
    {
        return $this->deleteWorkspaceModel(DepartmentPayroll::class, $id);
    }
}
