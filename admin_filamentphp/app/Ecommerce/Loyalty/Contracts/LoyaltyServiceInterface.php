<?php

namespace App\Ecommerce\Loyalty\Contracts;

interface LoyaltyServiceInterface
{
    /**
     * Award points based on successful order metrics.
     *
     * @param int $userId
     * @param int $orderId
     * @param int $points
     * @return bool
     * @throws \Exception
     */
    public function awardPoints(int $userId, int $orderId, int $points): bool;

    /**
     * Adjust manual rewards allocations via dashboards.
     *
     * @param int $userId
     * @param int $points
     * @param string|null $note
     * @return bool
     * @throws \Exception
     */
    public function adjustPoints(int $userId, int $points, ?string $note = null): bool;
}
