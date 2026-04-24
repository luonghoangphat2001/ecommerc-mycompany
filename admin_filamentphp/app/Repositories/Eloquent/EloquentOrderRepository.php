<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\Order;
use App\Repositories\Eloquent\BaseRepository;
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
        return $this->model->with(['items.product', 'shippingAddress', 'billingAddress', 'payments', 'customer'])
            ->find($id);
    }

    /**
     * @inheritDoc
     */
    public function getTaxTotal(Order $order): int
    {
        return (int) $order->taxes()->sum('amount');
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
    public function paginateFiltered(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return \Spatie\QueryBuilder\QueryBuilder::for($this->model)
            ->allowedFilters([
                \Spatie\QueryBuilder\AllowedFilter::scope('created_at_between'),
                'status',
                'number',
            ])
            ->allowedSorts(['created_at', 'total'])
            ->allowedIncludes(['items', 'shippingAddress', 'billingAddress'])
            ->defaultSort('-created_at')
            ->paginate($perPage);
    }
}
