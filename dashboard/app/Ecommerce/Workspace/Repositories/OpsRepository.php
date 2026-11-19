<?php

namespace App\Ecommerce\Workspace\Repositories;

use App\Ecommerce\Workspace\Repositories\Concerns\HasWorkspaceCrudOperations;
use App\Models\DepartmentIncident;
use App\Models\Order;

class OpsRepository implements OpsRepositoryInterface
{
    use HasWorkspaceCrudOperations;

    public function getMetrics(string $period = 'all'): array
    {
        $activeOrders = $this->activeOrdersQuery()->count();
        $incidentMetrics = DepartmentIncident::query()
            ->selectRaw('COUNT(*) as total_incidents')
            ->selectRaw("SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_incidents")
            ->first();
        $openIncidents = (int) ($incidentMetrics->open_incidents ?? 0);
        $totalIncidents = (int) ($incidentMetrics->total_incidents ?? 0);

        $health = $totalIncidents > 0 ? max(0, 100 - ($openIncidents / $totalIncidents * 100)) : 100;

        return [
            'active_orders' => $activeOrders,
            'processing_time' => 'N/A',
            'open_issues' => $openIncidents,
            'ops_health' => round($health) . '%',
        ];
    }

    public function getLiveOrders(string $period = 'all')
    {
        return $this->activeOrdersQuery()
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
    }

    private function activeOrdersQuery()
    {
        return Order::query()->whereNotIn('status', ['completed', 'cancelled', 'refunded']);
    }

    public function createIncident(array $data)
    {
        return $this->createWorkspaceModel(DepartmentIncident::class, $data);
    }

    public function updateIncident($id, array $data)
    {
        return $this->updateWorkspaceModel(DepartmentIncident::class, $id, $data);
    }

    public function deleteIncident($id)
    {
        return $this->deleteWorkspaceModel(DepartmentIncident::class, $id);
    }

    public function updateIncidentStatus($id, string $status)
    {
        return $this->updateWorkspaceModel(DepartmentIncident::class, $id, ['status' => $status]);
    }

    public function getIncidents(string $period = 'all')
    {
        return DepartmentIncident::with(['order', 'assignee'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
