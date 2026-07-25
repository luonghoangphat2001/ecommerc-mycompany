<?php

namespace App\Ecommerce\Agent\Contracts;

interface AgentDashboardRepositoryInterface
{
    /**
     * Return a small, read-only operational snapshot for authenticated agents.
     *
     * @return array{products: int, orders: int, active_agents: int}
     */
    public function getOperationalSummary(): array;

    /**
     * Return read-only order and revenue metrics for the current business day.
     *
     * @return array{period: string, from: string, until: string, orders: int, revenue: int, currency: string, products: int}
     */
    public function getMetrics(string $period = 'today'): array;
}
