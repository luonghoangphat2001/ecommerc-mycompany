<?php

namespace App\Ecommerce\Coupon\Contracts;

use App\Ecommerce\Core\Contracts\BaseRepositoryInterface;
use App\Models\Coupon;

interface CouponRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find coupon by its code.
     *
     * @param string $code
     * @return Coupon|null
     */
    public function findByCode(string $code): ?Coupon;

    /**
     * Get the usage count of a coupon by a specific user.
     *
     * @param string $couponCode
     * @param int|null $userId
     * @return int
     */
    public function getUsageCountForUser(string $couponCode, ?int $userId): int;
}
