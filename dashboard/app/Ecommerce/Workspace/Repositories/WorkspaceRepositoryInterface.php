<?php

namespace App\Ecommerce\Workspace\Repositories;

interface WorkspaceRepositoryInterface
{
    public function getCfoMetrics(): array;
    public function getLogisticsMetrics(): array;
    public function getRndMetrics(): array;
    public function getOpsMetrics(): array;
    public function getCskhMetrics(): array;
    public function getHrMetrics(): array;
    public function getDefaultWorkspaceData(string $departmentCode): array;
}
