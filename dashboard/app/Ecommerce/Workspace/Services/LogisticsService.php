<?php

namespace App\Ecommerce\Workspace\Services;

use App\Ecommerce\Workspace\Repositories\LogisticsRepositoryInterface;

class LogisticsService
{
    protected LogisticsRepositoryInterface $logisticsRepository;

    public function __construct(LogisticsRepositoryInterface $logisticsRepository)
    {
        $this->logisticsRepository = $logisticsRepository;
    }

    public function getWorkspaceData(string $period = 'all'): array
    {
        return [
            'metrics' => $this->logisticsRepository->getMetrics($period),
            'lowStockAlerts' => $this->logisticsRepository->getLowStockAlerts($period),
            'inventoryStocks' => $this->logisticsRepository->getInventoryStocks($period),
            'purchaseOrders' => $this->logisticsRepository->getPurchaseOrders($period),
        ];
    }

    public function createPO(array $data)
    {
        return $this->logisticsRepository->createPO($data);
    }

    public function updatePO($id, array $data)
    {
        return $this->logisticsRepository->updatePO($id, $data);
    }

    public function deletePO($id)
    {
        return $this->logisticsRepository->deletePO($id);
    }
}
