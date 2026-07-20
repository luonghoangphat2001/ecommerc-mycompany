<?php

namespace App\Ecommerce\Workspace\Repositories;

use App\Models\DepartmentFinancialProposal;
use App\Models\DepartmentPayroll;
use App\Models\DepartmentMaterialPrice;
use Carbon\Carbon;

class CfoRepository implements CfoRepositoryInterface
{
    public function getMetrics(string $period = 'all'): array
    {
        $queryProposals = DepartmentFinancialProposal::query();
        $queryPayrolls = DepartmentPayroll::query();
        
        $this->applyPeriodFilter($queryProposals, 'created_at', $period);
        $this->applyPeriodFilter($queryPayrolls, 'created_at', $period);

        $revenue = 500000000; // Mock revenue as we don't have sales income tables in this context
        $expenses = $queryPayrolls->sum('net_salary') + $queryProposals->where('status', 'approved')->sum('amount');
        $pendingCount = DepartmentFinancialProposal::where('status', 'pending')->count();
        $urgentCount = DepartmentFinancialProposal::where('status', 'pending')->where('is_urgent', true)->count();
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
        return DepartmentFinancialProposal::create($data);
    }

    public function updateProposal($id, array $data)
    {
        $proposal = DepartmentFinancialProposal::findOrFail($id);
        $proposal->update($data);
        return $proposal;
    }

    public function deleteProposal($id)
    {
        $proposal = DepartmentFinancialProposal::findOrFail($id);
        return $proposal->delete();
    }

    public function createPayroll(array $data)
    {
        return DepartmentPayroll::create($data);
    }

    public function updatePayroll($id, array $data)
    {
        $payroll = DepartmentPayroll::findOrFail($id);
        $payroll->update($data);
        return $payroll;
    }

    public function deletePayroll($id)
    {
        $payroll = DepartmentPayroll::findOrFail($id);
        return $payroll->delete();
    }
}
