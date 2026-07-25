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

    public function getMetrics(string $period = 'today'): array
    {
        $now = now();
        [$from, $until] = match ($period) {
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'quarter' => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
        };
        $orders = Order::query()
            ->whereBetween('created_at', [$from, $until])
            ->whereNotIn('status', ['cancelled', 'refunded']);

        return [
            'period' => in_array($period, ['today', 'month', 'quarter', 'year'], true) ? $period : 'today',
            'from' => $from->toIso8601String(),
            'until' => $until->toIso8601String(),
            'orders' => (clone $orders)->count(),
            'revenue' => (int) (clone $orders)->sum('total'),
            'currency' => (string) (Order::query()->whereNotNull('currency')->value('currency') ?? 'VND'),
            'products' => Product::query()->count(),
        ];
    }
}
