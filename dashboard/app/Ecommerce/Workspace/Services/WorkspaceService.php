<?php

namespace App\Ecommerce\Workspace\Services;

use App\Ecommerce\Workspace\Repositories\WorkspaceRepositoryInterface;

class WorkspaceService
{
    protected WorkspaceRepositoryInterface $repository;

    protected CfoService $cfoService;

    protected LogisticsService $logisticsService;

    protected OpsService $opsService;

    protected CskhService $cskhService;

    protected HrService $hrService;

    public function __construct(
        WorkspaceRepositoryInterface $repository,
        CfoService $cfoService,
        LogisticsService $logisticsService,
        OpsService $opsService,
        CskhService $cskhService,
        HrService $hrService
    ) {
        $this->repository = $repository;
        $this->cfoService = $cfoService;
        $this->logisticsService = $logisticsService;
        $this->opsService = $opsService;
        $this->cskhService = $cskhService;
        $this->hrService = $hrService;
    }

    public function getCfoData(): array
    {
        return $this->buildWorkspaceData(
            $this->cfoService->getMetrics(),
            [__('workspace.cfo.tabs.cashflow'), __('workspace.cfo.tabs.pricing'), __('workspace.cfo.tabs.approvals')]
        );
    }

    public function getLogisticsData(): array
    {
        return $this->buildWorkspaceData(
            $this->logisticsService->getMetrics(),
            [__('workspace.logistics.tabs.inventory'), __('workspace.logistics.tabs.alerts'), __('workspace.logistics.tabs.pos')]
        );
    }

    public function getRndData(): array
    {
        return $this->buildWorkspaceData(
            $this->repository->getRndMetrics(),
            [__('workspace.rnd.tabs.bom'), __('workspace.rnd.tabs.menu'), __('workspace.rnd.tabs.experiments')]
        );
    }

    public function getOpsData(): array
    {
        return $this->buildWorkspaceData(
            $this->opsService->getMetrics(),
            [__('workspace.ops.tabs.live_orders'), __('workspace.ops.tabs.issues')]
        );
    }

    public function getCskhData(): array
    {
        return $this->buildWorkspaceData(
            $this->cskhService->getMetrics(),
            [__('workspace.cskh.tabs.reviews'), __('workspace.cskh.tabs.sentiment'), __('workspace.cskh.tabs.coupons')]
        );
    }

    public function getHrData(): array
    {
        return $this->buildWorkspaceData(
            $this->hrService->getMetrics(),
            [__('workspace.hr.tabs.health'), __('workspace.hr.tabs.risk')]
        );
    }

    public function getDefaultData(string $departmentCode): array
    {
        return $this->repository->getDefaultWorkspaceData($departmentCode);
    }

    private function buildWorkspaceData(array $metrics, array $tabs): array
    {
        return [
            'metrics' => $metrics,
            'tabs' => $tabs,
        ];
    }
}
