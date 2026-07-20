<?php

namespace App\Ecommerce\Workspace\Repositories;

interface OpsRepositoryInterface
{
    public function getMetrics(string $period = 'all'): array;
    public function getLiveOrders(string $period = 'all');
    public function getIncidents(string $period = 'all');
    public function createIncident(array $data);
    public function updateIncident($id, array $data);
    public function deleteIncident($id);
}
