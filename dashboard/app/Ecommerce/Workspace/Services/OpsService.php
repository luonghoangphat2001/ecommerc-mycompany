<?php

namespace App\Ecommerce\Workspace\Services;

use App\Ecommerce\Workspace\Repositories\OpsRepositoryInterface;

class OpsService
{
    protected OpsRepositoryInterface $opsRepository;

    public function __construct(OpsRepositoryInterface $opsRepository)
    {
        $this->opsRepository = $opsRepository;
    }

    public function getWorkspaceData(string $period = 'all'): array
    {
        return [
            'metrics' => $this->opsRepository->getMetrics($period),
            'liveOrders' => $this->opsRepository->getLiveOrders($period),
            'incidents' => $this->opsRepository->getIncidents($period),
        ];
    }

    public function createIncident(array $data)
    {
        return $this->opsRepository->createIncident($data);
    }

    public function updateIncident($id, array $data)
    {
        return $this->opsRepository->updateIncident($id, $data);
    }

    public function deleteIncident($id)
    {
        return $this->opsRepository->deleteIncident($id);
    }
}
