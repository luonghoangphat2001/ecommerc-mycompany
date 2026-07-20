<?php

namespace App\Ecommerce\Workspace\Services;

use App\Ecommerce\Workspace\Repositories\CskhRepositoryInterface;

class CskhService
{
    protected CskhRepositoryInterface $cskhRepository;

    public function __construct(CskhRepositoryInterface $cskhRepository)
    {
        $this->cskhRepository = $cskhRepository;
    }

    public function getWorkspaceData(string $period = 'all'): array
    {
        return [
            'metrics' => $this->cskhRepository->getMetrics($period),
            'reviews' => $this->cskhRepository->getReviews($period),
            'coupons' => $this->cskhRepository->getCoupons(),
        ];
    }

    public function createReview(array $data)
    {
        return $this->cskhRepository->createReview($data);
    }

    public function updateReview($id, array $data)
    {
        return $this->cskhRepository->updateReview($id, $data);
    }

    public function deleteReview($id)
    {
        return $this->cskhRepository->deleteReview($id);
    }
}
