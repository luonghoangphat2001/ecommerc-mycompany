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
}
