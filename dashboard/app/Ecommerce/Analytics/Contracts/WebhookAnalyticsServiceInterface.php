<?php

namespace App\Ecommerce\Analytics\Contracts;

interface WebhookAnalyticsServiceInterface
{
    /**
     * Get webhook delivery chart data.
     *
     * @param int $days
     * @return array
     */
    public function getDeliveryChartData(int $days = 7): array;

    /**
     * Get webhook statistics overview stats.
     *
     * @return array
     */
    public function getStatsOverview(): array;

    /**
     * Get total number of webhooks.
     *
     * @return int
     */
    public function getTotalWebhooksCount(): int;

    /**
     * Clean up old webhook logs.
     *
     * @param int $days
     * @return int
     */
    public function cleanupLogs(int $days): int;
}
