<?php

namespace App\Ecommerce\Loyalty\Contracts;

interface LoyaltyRepositoryInterface
{
    /**
     * Retrieve or instantiate standard points aggregates.
     *
     * @param int $userId
     * @return mixed
     */
    public function findOrCreateByUserId(int $userId);

    /**
     * Increment parameters.
     *
     * @param int $userId
     * @param int $points
     * @param int $lifetime
     * @return bool
     */
    public function incrementPoints(int $userId, int $points, int $lifetime): bool;

    /**
     * Append logs.
     *
     * @param int $userId
     * @param int $points
     * @param string $actionType
     * @param int|null $orderId
     * @return bool
     */
    public function recordLog(int $userId, int $points, string $actionType, ?int $orderId = null): bool;

    /**
     * Get total points earned for an order.
     *
     * @param int $orderId
     * @return int
     */
    public function getPointsEarnedForOrder(int $orderId): int;
}


