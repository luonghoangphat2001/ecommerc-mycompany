<?php

namespace App\Services;

use App\Contracts\Repositories\WebhookAnalyticsRepositoryInterface;
use App\Contracts\Services\WebhookAnalyticsServiceInterface;

class WebhookAnalyticsService implements WebhookAnalyticsServiceInterface
{
    /**
     * @var WebhookAnalyticsRepositoryInterface
     */
    protected $repository;

    /**
     * @var \App\Contracts\Repositories\WebhookLogRepositoryInterface
     */
    protected $logRepository;

    /**
     * WebhookAnalyticsService constructor.
     *
     * @param WebhookAnalyticsRepositoryInterface $repository
     * @param \App\Contracts\Repositories\WebhookLogRepositoryInterface $logRepository
     */
    public function __construct(
        WebhookAnalyticsRepositoryInterface $repository,
        \App\Contracts\Repositories\WebhookLogRepositoryInterface $logRepository
    ) {
        $this->repository = $repository;
        $this->logRepository = $logRepository;
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryChartData(int $days = 7): array
    {
        return $this->repository->getDeliveryTrend($days);
    }

    public function getStatsOverview(): array
    {
        return $this->repository->getStatsSummary();
    }

    /**
     * @inheritDoc
     */
    public function getTotalWebhooksCount(): int
    {
        return $this->repository->getTotalWebhooksCount();
    }

    /**
     * @inheritDoc
     */
    public function cleanupLogs(int $days): int
    {
        return $this->logRepository->deleteOlderThan(now()->subDays($days));
    }
}
