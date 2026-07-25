<?php

namespace App\Ecommerce\Agent\Repositories;

use App\Ecommerce\Agent\Contracts\AgentDashboardRepositoryInterface;
use App\Ecommerce\Department\Enums\AgentStatus;
use App\Models\DepartmentAgent;
use App\Models\Order;
use App\Models\Product;

class EloquentAgentDashboardRepository implements AgentDashboardRepositoryInterface
{
    public function getOperationalSummary(): array
    {
        return [
            'products' => Product::query()->count(),
            'orders' => Order::query()->count(),
            'active_agents' => DepartmentAgent::query()
                ->where('status', AgentStatus::ACTIVE)
                ->count(),
        ];
    }
}
