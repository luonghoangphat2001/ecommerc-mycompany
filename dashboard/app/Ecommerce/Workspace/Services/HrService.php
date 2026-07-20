<?php

namespace App\Ecommerce\Workspace\Services;

use App\Ecommerce\Workspace\Repositories\HrRepositoryInterface;

class HrService
{
    protected HrRepositoryInterface $hrRepository;

    public function __construct(HrRepositoryInterface $hrRepository)
    {
        $this->hrRepository = $hrRepository;
    }

    public function getWorkspaceData(string $period = 'all'): array
    {
        return [
            'metrics' => $this->hrRepository->getMetrics($period),
            'contracts' => $this->hrRepository->getContracts($period),
        ];
    }

    public function createContract(array $data)
    {
        return $this->hrRepository->createContract($data);
    }

    public function updateContract($id, array $data)
    {
        return $this->hrRepository->updateContract($id, $data);
    }

    public function deleteContract($id)
    {
        return $this->hrRepository->deleteContract($id);
    }
}
