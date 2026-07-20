<?php

namespace App\Ecommerce\Workspace\Services;

use App\Ecommerce\Workspace\Repositories\CfoRepositoryInterface;

class CfoService
{
    protected CfoRepositoryInterface $cfoRepository;

    public function __construct(CfoRepositoryInterface $cfoRepository)
    {
        $this->cfoRepository = $cfoRepository;
    }

    public function getWorkspaceData(string $period = 'all'): array
    {
        return [
            'metrics' => $this->cfoRepository->getMetrics($period),
            'proposals' => $this->cfoRepository->getProposals($period),
            'payrolls' => $this->cfoRepository->getPayrolls($period),
            'prices' => $this->cfoRepository->getMaterialPrices($period),
        ];
    }

    public function createProposal(array $data)
    {
        // Business logic could go here (e.g., checking budget)
        return $this->cfoRepository->createProposal($data);
    }

    public function updateProposal($id, array $data)
    {
        return $this->cfoRepository->updateProposal($id, $data);
    }

    public function deleteProposal($id)
    {
        return $this->cfoRepository->deleteProposal($id);
    }

    public function createPayroll(array $data)
    {
        return $this->cfoRepository->createPayroll($data);
    }

    public function updatePayroll($id, array $data)
    {
        return $this->cfoRepository->updatePayroll($id, $data);
    }

    public function deletePayroll($id)
    {
        return $this->cfoRepository->deletePayroll($id);
    }
}
