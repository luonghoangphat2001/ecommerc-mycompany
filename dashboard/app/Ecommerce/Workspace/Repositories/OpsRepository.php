<?php

namespace App\Ecommerce\Workspace\Repositories;

use App\Models\Order;
use App\Models\DepartmentIncident;

class OpsRepository implements OpsRepositoryInterface
{
    public function getMetrics(string $period = 'all'): array
    {
        $activeOrders = Order::whereNotIn('status', ['completed', 'cancelled', 'refunded'])->count();
        $openIncidents = DepartmentIncident::where('status', 'open')->count();
        $totalIncidents = DepartmentIncident::count();
        
        $health = $totalIncidents > 0 ? max(0, 100 - ($openIncidents / $totalIncidents * 100)) : 100;

        return [
            'active_orders' => $activeOrders,
            'processing_time' => '1.5h', // Mock metric
            'open_issues' => $openIncidents,
            'ops_health' => round($health) . '%',
        ];
    }

    public function getLiveOrders(string $period = 'all')
    {
        return Order::whereNotIn('status', ['completed', 'cancelled', 'refunded'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
    }

    public function createIncident(array $data)
    {
        return DepartmentIncident::create($data);
    }

    public function updateIncident($id, array $data)
    {
        $incident = DepartmentIncident::findOrFail($id);
        $incident->update($data);
        return $incident;
    }

    public function deleteIncident($id)
    {
        $incident = DepartmentIncident::findOrFail($id);
        return $incident->delete();
    }

    public function getIncidents(string $period = 'all')
    {
        return DepartmentIncident::with(['order', 'assignee'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
