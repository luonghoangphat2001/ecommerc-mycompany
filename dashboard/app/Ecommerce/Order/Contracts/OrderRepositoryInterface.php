<?php

namespace App\Ecommerce\Order\Contracts;

use App\Ecommerce\Core\Repositories\BaseRepository;

use App\Ecommerce\Core\Contracts\BaseRepositoryInterface;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find order by number.
     *
     * @param string $number
     * @param array $relations
     * @return Order|null
     */
    public function findByNumber(string $number, array $relations = []): ?Order;

    /**
     * Get recent orders.
     *
     * @param int $limit
     * @param array $relations
     * @return Collection
     */
    public function getRecentOrders(int $limit = 10, array $relations = []): Collection;

    /**
     * Get order with all items and addresses.
     *
     * @param int|string $id
     * @return Order|null
     */
    public function getFullOrder(int|string $id): ?Order;

    /**
     * Get total tax amount for an order.
     *
     * @param Order $order
     * @return int
     */
    public function getTaxTotal(Order $order): int;

    /**
     * Get total shipping amount for an order including tax.
     *
     * @param Order $order
     * @return int
     */
    public function getShippingTotalWithTax(Order $order): int;

    /**
     * Get total shipping amount for an order.
     *
     * @param Order $order
     * @return int
     */
    public function getTotalShipping(Order $order): int;

    /**
     * Get total loyalty discount for an order.
     *
     * @param Order $order
     * @return int
     */
    public function getLoyaltyDiscountTotal(Order $order): int;

    /**
     * Get distinct order statuses.
     *
     * @return array
     */
    public function getDistinctStatuses(): array;

    /**
     * Get a meta value for an order.
     *
     * @param Order $order
     * @param string $key
     * @return string|null
     */
    public function getMetaValue(Order $order, string $key): ?string;

    /**
     * Get filtered and paginated orders.
     *
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginateFiltered(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator;
}
