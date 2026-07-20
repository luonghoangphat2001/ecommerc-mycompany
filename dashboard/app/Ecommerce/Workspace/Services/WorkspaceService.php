<?php

namespace App\Ecommerce\Workspace\Services;

use App\Ecommerce\Workspace\Repositories\WorkspaceRepositoryInterface;

class WorkspaceService
{
    protected WorkspaceRepositoryInterface $repository;

    public function __construct(WorkspaceRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getCfoData(): array
    {
        return [
            'metrics' => $this->repository->getCfoMetrics(),
            'tabs' => [__('workspace.cfo.tabs.cashflow'), __('workspace.cfo.tabs.pricing'), __('workspace.cfo.tabs.approvals')],
        ];
    }

    public function getLogisticsData(): array
    {
        return [
            'metrics' => $this->repository->getLogisticsMetrics(),
            'tabs' => [__('workspace.logistics.tabs.inventory'), __('workspace.logistics.tabs.alerts'), __('workspace.logistics.tabs.pos')],
        ];
    }

    public function getRndData(): array
    {
        return [
            'metrics' => $this->repository->getRndMetrics(),
            'tabs' => [__('workspace.rnd.tabs.bom'), __('workspace.rnd.tabs.menu'), __('workspace.rnd.tabs.experiments')],
        ];
    }

    public function getOpsData(): array
    {
        return [
            'metrics' => $this->repository->getOpsMetrics(),
            'tabs' => [__('workspace.ops.tabs.live_orders'), __('workspace.ops.tabs.issues')],
        ];
    }

    public function getCskhData(): array
    {
        return [
            'metrics' => $this->repository->getCskhMetrics(),
            'tabs' => [__('workspace.cskh.tabs.reviews'), __('workspace.cskh.tabs.sentiment'), __('workspace.cskh.tabs.coupons')],
        ];
    }

    public function getHrData(): array
    {
        return [
            'metrics' => $this->repository->getHrMetrics(),
            'tabs' => [__('workspace.hr.tabs.health'), __('workspace.hr.tabs.risk')],
        ];
    }

    public function getDefaultData(string $departmentCode): array
    {
        return $this->repository->getDefaultWorkspaceData($departmentCode);
    }
}
