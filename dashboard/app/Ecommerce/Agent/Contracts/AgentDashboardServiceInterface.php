<?php

namespace App\Ecommerce\Agent\Contracts;

use App\Models\DepartmentAgent;

interface AgentDashboardServiceInterface
{
    /**
     * Build the authenticated Dashboard API connection status.
     *
     * @return array<string, mixed>
     */
    public function getConnectionStatus(DepartmentAgent $agent): array;
}
