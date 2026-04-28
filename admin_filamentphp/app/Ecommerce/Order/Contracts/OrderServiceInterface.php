<?php

namespace App\Ecommerce\Order\Contracts;

use App\Models\Order;
use App\Ecommerce\Order\Enums\OrderStatus;
use App\Ecommerce\Order\DTOs\Checkout\CreateOrderDTO;

interface OrderServiceInterface
{
    /**
     * Create a new order.
     *
     * @param array|CreateOrderDTO $data
     * @param array $items
     * @return Order
     */
    public function createOrder(array|CreateOrderDTO $data, array $items = []): Order;

    /**
     * Update order and recalculate totals.
     *
     * @param Order $order
     * @param array $data
     * @return Order
     */
    public function updateOrder(Order $order, array $data): Order;

    /**
     * Transition order status.
     *
     * @param Order $order
     * @param OrderStatus $newStatus
     * @return bool
     */
    public function updateStatus(Order $order, OrderStatus $newStatus): bool;

    /**
     * Recalculate order totals (taxes, shipping, fees).
     *
     * @param Order $order
     * @return Order
     */
    public function recalculateTotals(Order $order): Order;

    /**
     * Delete an order.
     *
     * @param Order $order
     * @return bool
     */
    public function deleteOrder(Order $order): bool;

    /**
     * Cancel an order with a reason.
     *
     * @param Order $order
     * @param string $reason
     * @return bool
     */
    public function cancel(Order $order, string $reason): bool;

    /**
     * Refund an order with a reason and create a reversal payment.
     *
     * @param Order $order
     * @param string $reason
     * @return bool
     */
    public function refund(Order $order, string $reason): bool;

    /**
     * Confirm payment for an order.
     *
     * @param Order $order
     * @return bool
     */
    public function confirmPayment(Order $order): bool;

    /**
     * Send order confirmation email to the customer.
     *
     * @param Order $order
     * @return void
     */
    public function sendOrderConfirmationMail(Order $order): void;

    /**
     * Send order notification email to the administrator.
     *
     * @param Order $order
     * @return void
     */
    public function sendAdminOrderNotification(Order $order): void;

    /**
     * Check if order has any pending payments.
     *
     * @param Order $order
     * @return bool
     */
    public function hasPendingPayments(Order $order): bool;

    public function paginateFiltered(int $perPage = 15, ?int $userId = null): \Illuminate\Pagination\LengthAwarePaginator;

    public function getFullOrder(int|string $id): ?Order;

    public function find(int|string $id): ?Order;

    public function getTaxTotal(Order $order): int;

    public function getTotalShipping(Order $order): int;

    public function getShippingTotalWithTax(Order $order): int;

    public function getMetaValue(Order $order, string $key): ?string;

    public function getDistinctStatuses(): array;

    public function getTableQuery(): \Illuminate\Database\Eloquent\Builder;
}
