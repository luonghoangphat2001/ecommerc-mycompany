<?php

namespace App\Ecommerce\Coupon\Repositories;

use App\Ecommerce\Core\Repositories\BaseRepository;
use App\Ecommerce\Coupon\Contracts\CouponRepositoryInterface;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;

class EloquentCouponRepository extends BaseRepository implements CouponRepositoryInterface
{
    /**
     * EloquentCouponRepository constructor.
     *
     * @param Coupon $model
     */
    public function __construct(Coupon $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function findByCode(string $code): ?Coupon
    {
        return $this->model->where('code', $code)
                           ->where('is_active', true)
                           ->first();
    }

    /**
     * @inheritDoc
     */
    public function getUsageCountForUser(string $couponCode, ?int $userId): int
    {
        if (!$userId) {
            return 0;
        }

        return DB::table('shop_order_coupons')
            ->join('shop_orders', 'shop_order_coupons.order_id', '=', 'shop_orders.id')
            ->where('shop_order_coupons.coupon_code', $couponCode)
            ->where('shop_orders.user_id', $userId)
            ->count();
    }
}
