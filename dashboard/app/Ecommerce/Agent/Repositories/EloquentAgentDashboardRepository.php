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
        return \Illuminate\Support\Facades\Cache::remember('agent_dashboard_op_summary', 60, function () {
            return [
                'products' => Product::query()->count(),
                'orders' => Order::query()->count(),
                'active_agents' => DepartmentAgent::query()
                    ->where('status', AgentStatus::ACTIVE)
                    ->count(),
            ];
        });
    }

    public function getMetrics(string $period = 'today'): array
    {
        $cacheKey = 'agent_dashboard_metrics_' . $period;

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($period) {
            $now = now();
            [$from, $until] = match ($period) {
                'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
                'quarter' => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()],
                'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
                default => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            };

            $orders = Order::query()
                ->whereBetween('created_at', [$from, $until])
                ->whereNotIn('status', [
                    \App\Ecommerce\Order\Enums\OrderStatus::Cancelled->value,
                    \App\Ecommerce\Order\Enums\OrderStatus::Refunded->value,
                ]);

            return [
                'period' => $period,
                'from' => $from->toIso8601String(),
                'until' => $until->toIso8601String(),
                'orders' => (clone $orders)->count(),
                'revenue' => (int) (clone $orders)->sum('total'),
                'currency' => (string) (Order::query()->whereNotNull('currency')->value('currency') ?? 'VND'),
                'products' => Product::query()->count(),
            ];
        });
    }
}
