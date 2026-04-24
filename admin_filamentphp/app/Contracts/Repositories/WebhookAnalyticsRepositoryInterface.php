<?php

namespace App\Contracts\Repositories;

interface WebhookAnalyticsRepositoryInterface
{
    /**
     * Get webhook delivery trend for the last X days.
     *
     * @param int $days
     * @return array
     */
    public function getDeliveryTrend(int $days = 7): array;

    /**
     * Get statistics summary for dashboard.
     *
     * @return array
     */
    public function getStatsSummary(): array;

    /**
     * Get total number of webhooks.
     *
     * @return int
     */
    public function getTotalWebhooksCount(): int;
}
