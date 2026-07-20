<?php

namespace App\Ecommerce\Workspace\Repositories;

use App\Models\Order;
use App\Models\Product;
use App\Models\WebhookLog;
use Illuminate\Support\Facades\DB;

class WorkspaceRepository implements WorkspaceRepositoryInterface
{
    public function getCfoMetrics(): array
    {
        return [
            'revenue' => Order::where('status', 'completed')->sum('total') ?? 0,
            'pending_payments' => Order::where('status', 'pending')->count(),
            'expenses' => 12500000, // Mock for now
            'cashflow_status' => 'Healthy',
        ];
    }

    public function getLogisticsMetrics(): array
    {
        return [
            'total_stock' => DB::table('shop_inventories')->sum('quantity') ?? 0,
            'low_stock_alerts' => DB::table('shop_inventories')->where('quantity', '<', 10)->count(),
            'pending_pos' => 5, // Mock
            'warehouse_capacity' => '78%',
        ];
    }

    public function getRndMetrics(): array
    {
        return [
            'new_products' => Product::where('created_at', '>=', now()->subDays(30))->count(),
            'pending_boms' => 12, // Mock
            'active_experiments' => 3, // Mock
            'innovation_index' => 'High',
        ];
    }

    public function getOpsMetrics(): array
    {
        return [
            'active_orders' => Order::whereIn('status', ['pending', 'processing'])->count(),
            'avg_processing_time' => '45 mins', // Mock
            'open_issues' => 2, // Mock
            'ops_health' => '98%',
        ];
    }

    public function getCskhMetrics(): array
    {
        return [
            'avg_rating' => 4.8, // Mock
            'open_tickets' => 15, // Mock
            'sentiment_score' => 'Positive',
            'compensation_coupons' => 8, // Mock
        ];
    }

    public function getHrMetrics(): array
    {
        return [
            'total_agents' => DB::table('department_agents')->count(),
            'active_agents' => DB::table('department_agents')->where('is_active', true)->count(),
            'blocked_actions' => WebhookLog::where('status', 'failed')->count(),
            'risk_level' => 'Normal',
        ];
    }

    public function getDefaultWorkspaceData(string $departmentCode): array
    {
        $department = \App\Models\Department::where('code', $departmentCode)->firstOrFail();
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
