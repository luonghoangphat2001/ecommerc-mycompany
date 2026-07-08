<?php

namespace App\Ecommerce\Analytics\Services;

use App\Ecommerce\Analytics\Contracts\WebhookAnalyticsRepositoryInterface;
use App\Ecommerce\Analytics\Contracts\WebhookAnalyticsServiceInterface;
use App\Ecommerce\Analytics\Contracts\WebhookLogRepositoryInterface;

class WebhookAnalyticsService implements WebhookAnalyticsServiceInterface
{
    /**
     * @var WebhookAnalyticsRepositoryInterface
     */
    protected $repository;

    /**
     * @var WebhookLogRepositoryInterface
     */
    protected $logRepository;

    /**
     * WebhookAnalyticsService constructor.
     *
     * @param WebhookAnalyticsRepositoryInterface $repository
     * @param WebhookLogRepositoryInterface $logRepository
     */
    public function __construct(
        WebhookAnalyticsRepositoryInterface $repository,
        WebhookLogRepositoryInterface $logRepository
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
