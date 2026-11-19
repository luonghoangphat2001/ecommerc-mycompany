<?php

namespace App\Ecommerce\Order\Actions;

use App\Ecommerce\Inventory\Actions\RevertStockAction;
use App\Ecommerce\Loyalty\Actions\ClawbackPointsAction;
use App\Ecommerce\Loyalty\Contracts\LoyaltyRepositoryInterface;
use App\Ecommerce\Order\Concerns\LocksOrderForUpdate;
use App\Ecommerce\Order\Contracts\OrderRefundRepositoryInterface;
use App\Ecommerce\Order\Contracts\OrderRepositoryInterface;
use App\Ecommerce\Order\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderRefund;
use App\Traits\HandleTransactions;
use Exception;

class RefundOrderAction
{
    use HandleTransactions;
    use LocksOrderForUpdate;

    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected OrderRefundRepositoryInterface $refundRepository,
        protected LoyaltyRepositoryInterface $loyaltyRepository,
        protected ClawbackPointsAction $clawbackPointsAction,
        protected RevertStockAction $revertStockAction,
    ) {}

    /**
     * Execute the refund process for an order.
     *
     * @param  Order|int  $order
     *
     * @throws Exception
     */
    public function execute($order, int $amount, string $reason = '', array $restock = [], ?string $idempotencyKey = null): OrderRefund
    {
        if (is_int($order)) {
            $order = $this->orderRepository->find($order);
            if (! $order) {
                throw new Exception(__('admin.api.order_not_found'));
            }
        }

        if ($amount <= 0) {
            throw new Exception(__('admin.refund.invalid_amount'));
        }

        return $this->useTransaction(function () use ($order, $amount, $reason, $restock, $idempotencyKey) {
            $order = $this->lockOrderForUpdate($order);

            if ($idempotencyKey) {
                $existing = $order->refunds->first(fn (OrderRefund $refund) => ($refund->metadata['idempotency_key'] ?? null) === $idempotencyKey);
                if ($existing) {
                    return $existing;
                }
            }

            $disposition = $restock['disposition'] ?? 'none';
            if (! in_array($disposition, ['none', 'restockable', 'damaged'], true)) {
                throw new Exception('Invalid inventory disposition.');
            }

            // 1. Create Order Refund record via Repository
            $refund = $this->refundRepository->create([
                'order_id' => $order->id,
                'amount' => $amount,
                'reason' => $reason,
                'metadata' => [
                    'inventory_disposition' => $disposition,
                    'idempotency_key' => $idempotencyKey,
                ],
            ]);

            if ($disposition === 'restockable') {
                foreach ((array) ($restock['items'] ?? []) as $itemData) {
                    $item = $order->items()->whereKey((int) ($itemData['order_item_id'] ?? 0))->firstOrFail();
                    $requested = (int) ($itemData['quantity'] ?? 0);
                    if ($requested <= 0) {
                        throw new Exception('Restock quantity must be greater than zero.');
                    }
                    $restored = $this->revertStockAction->restoreOrderItemQuantity($item, $requested);
                    if ($restored !== $requested) {
                        throw new Exception('Restock quantity exceeds the original deducted quantity or has no traceable source.');
                    }
                }
            }

            // 2. Adjust Order Status if full refund
            $totalRefunded = $this->refundRepository->getTotalRefunded($order);
            if ($totalRefunded >= $order->total) {
                $this->orderRepository->update($order->id, ['status' => OrderStatus::Refunded]);
            }

            // 3. Perform Loyalty Point Clawback
            if ($order->user_id) {
                // Find points earned for this order via Repository
                $pointsEarned = $this->loyaltyRepository->getPointsEarnedForOrder($order->id);

                if ($pointsEarned > 0) {
                    // For partial refund, claw back proportionate amount
                    $pointsToClawback = (int) min($pointsEarned, ($amount / $order->total) * $pointsEarned);

                    if ($pointsToClawback > 0) {
                        $this->clawbackPointsAction->execute($order->user_id, $order->id, $pointsToClawback);
                    }
                }
            }

            activity('order')
                ->performedOn($order)
                ->log("Processed refund of {$amount} for Order #{$order->id}");

            return $refund;
        });
    }
}
