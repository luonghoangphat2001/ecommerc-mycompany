<?php

namespace App\Ecommerce\Workspace\Repositories;

interface LogisticsRepositoryInterface
{
    public function getMetrics(string $period = 'all'): array;
    public function getLowStockAlerts(string $period = 'all');
    public function getInventoryStocks(string $period = 'all');
    public function getPurchaseOrders(string $period = 'all');
    public function createPO(array $data);
    public function updatePO($id, array $data);
    public function deletePO($id);
}
