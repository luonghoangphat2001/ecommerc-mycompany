<?php

namespace App\Ecommerce\Agent\Repositories;

use App\Ecommerce\Agent\Contracts\AgentDashboardRepositoryInterface;
use App\Ecommerce\Department\Enums\AgentStatus;
use App\Ecommerce\Order\Enums\OrderStatus;
use App\Models\DepartmentAgent;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class EloquentAgentDashboardRepository implements AgentDashboardRepositoryInterface
{
    public function getOperationalSummary(): array
    {
        return Cache::remember('agent_dashboard_op_summary', 60, function () {
            return [
                'products' => $this->countProducts(),
                'orders' => $this->countOrders(),
                'active_agents' => DepartmentAgent::query()
                    ->where('status', AgentStatus::ACTIVE)
                    ->count(),
            ];
        });
    }

    public function getMetrics(string $period = 'today'): array
    {
        $cacheKey = 'agent_dashboard_metrics_' . $period;

        return Cache::remember($cacheKey, 60, function () use ($period) {
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
                    OrderStatus::Cancelled->value,
                    OrderStatus::Refunded->value,
                ]);
            $orderSummary = $orders
                ->selectRaw('COUNT(*) as orders, COALESCE(SUM(total), 0) as revenue')
                ->first();

            return [
                'period' => $period,
                'from' => $from->toIso8601String(),
                'until' => $until->toIso8601String(),
                'orders' => (int) ($orderSummary->orders ?? 0),
                'revenue' => (int) ($orderSummary->revenue ?? 0),
                'currency' => (string) (Order::query()->whereNotNull('currency')->value('currency') ?? 'VND'),
                'products' => $this->countProducts(),
            ];
        });
    }

    private function countProducts(): int
    {
        return Product::query()->count();
    }

    private function countOrders(): int
    {
        return Order::query()->count();
    }
}
