<?php

namespace App\Ecommerce\Order\Repositories;

use App\Ecommerce\Order\Contracts\OrderRepositoryInterface;
use App\Models\Order;
use App\Ecommerce\Core\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class EloquentOrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    /**
     * EloquentOrderRepository constructor.
     *
     * @param Order $model
     */
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function findByNumber(string $number, array $relations = []): ?Order
    {
        return $this->model->with($relations)->where('number', $number)->first();
    }

    /**
     * @inheritDoc
     */
    public function getRecentOrders(int $limit = 10, array $relations = []): Collection
    {
        return $this->model->with($relations)->latest()->limit($limit)->get();
    }

    /**
     * @inheritDoc
     */
    public function getFullOrder(int|string $id): ?Order
    {
        return $this->model->with(['items.product.media', 'shippingAddress', 'billingAddress', 'payments', 'customer', 'metas', 'coupons', 'refunds', 'shipping', 'activities'])
            ->find($id);
    }

    /**
     * @inheritDoc
     */
    public function getTaxTotal(Order $order): int
    {
        return (int) $order->tax_amount;
    }

    /**
     * @inheritDoc
     */
    public function getShippingTotalWithTax(Order $order): int
    {
        $shipping = $order->shipping;

        if (!$shipping) {
            return 0;
        }

        return (int) ($shipping->amount + ($shipping->tax?->amount ?? 0));
    }

    /**
     * @inheritDoc
     */
    public function getTotalShipping(Order $order): int
    {
        return (int) ($order->shipping?->amount ?? 0);
    }

    /**
     * @inheritDoc
     */
    public function getLoyaltyDiscountTotal(Order $order): int
    {
        $meta = $order->metas()->where('key', 'loyalty_discount')->first();
        return $meta ? (int) $meta->value : 0;
    }

    /**
     * @inheritDoc
     */
    public function getDistinctStatuses(): array
    {
        return $this->model->select('status')->distinct()->pluck('status')->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getMetaValue(Order $order, string $key): ?string
    {
        return $order->metas()->where('key', $key)->first()?->value;
    }

    /**
     * @inheritDoc
     */
    public function paginateFiltered(int $perPage = 15, ?int $userId = null): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = $this->model->query()->orderBy('created_at', 'desc');
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return \Spatie\QueryBuilder\QueryBuilder::for($query)
            ->allowedFilters([
                \Spatie\QueryBuilder\AllowedFilter::scope('created_at_between'),
                'status',
                'number',
            ])
            ->allowedSorts(['created_at', 'total'])
            ->allowedIncludes(['items', 'shippingAddress', 'billingAddress', 'payments', 'shipping'])
            ->defaultSort('-created_at')
            ->paginate($perPage);
    }
}
