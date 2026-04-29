<?php

namespace App\Ecommerce\Loyalty\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\User;
use App\Ecommerce\Loyalty\Contracts\LoyaltyRepositoryInterface;
use App\Ecommerce\Loyalty\Contracts\LoyaltyServiceInterface;
use Carbon\Carbon;

use Exception;

class LoyaltyService implements LoyaltyServiceInterface
{
    protected LoyaltyRepositoryInterface $loyaltyRepository;

    public function __construct(LoyaltyRepositoryInterface $loyaltyRepository)
    {
        $this->loyaltyRepository = $loyaltyRepository;
    }

    /**
     * Award points based on successful order metrics.
     *
     * @param int $userId
     * @param int $orderId
     * @param int $points
     * @return bool
     * @throws Exception
     */
    public function awardPoints(int $userId, int $orderId, int $points): bool
    {
        if ($points <= 0) {
            return false;
        }

        return DB::transaction(function () use ($userId, $orderId, $points) {
            $this->loyaltyRepository->incrementPoints($userId, $points, $points);
            $this->loyaltyRepository->recordLog($userId, $points, trans('admin.loyalty.earn'), $orderId);

            return true;
        }, 3);
    }

    /**
     * Adjust manual rewards allocations via dashboards.
     *
     * @param int $userId
     * @param int $points
     * @param string|null $note
     * @return bool
     * @throws Exception
     */
    public function adjustPoints(int $userId, int $points, ?string $note = null): bool
    {
        return DB::transaction(function () use ($userId, $points) {
            $this->loyaltyRepository->incrementPoints($userId, $points, 0);
            $this->loyaltyRepository->recordLog($userId, $points, trans('admin.loyalty.adjustment'));

            return true;
        }, 3);
    }
}

