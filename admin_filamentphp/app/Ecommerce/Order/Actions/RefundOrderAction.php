<?php

namespace App\Ecommerce\Order\Actions;

use App\Models\Order;
use App\Models\OrderRefund;
use App\Ecommerce\Order\Enums\OrderStatus;
use App\Ecommerce\Loyalty\Actions\ClawbackPointsAction;
use App\Ecommerce\Order\Contracts\OrderRepositoryInterface;
use App\Ecommerce\Order\Contracts\OrderRefundRepositoryInterface;
use App\Ecommerce\Loyalty\Contracts\LoyaltyRepositoryInterface;
use App\Traits\HandleTransactions;
use Exception;

class RefundOrderAction
{
    use HandleTransactions;

    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected OrderRefundRepositoryInterface $refundRepository,
        protected LoyaltyRepositoryInterface $loyaltyRepository,
        protected ClawbackPointsAction $clawbackPointsAction
    ) {}

    /**
     * Execute the refund process for an order.
     *
     * @param Order|int $order
     * @param int $amount
     * @param string $reason
     * @return OrderRefund
     * @throws Exception
     */
    public function execute($order, int $amount, string $reason = ''): OrderRefund
    {
        if (is_int($order)) {
            $order = $this->orderRepository->find($order);
            if (!$order) {
                throw new Exception(__('admin.api.order_not_found'));
            }
        }

        if ($amount <= 0) {
            throw new Exception(__('admin.refund.invalid_amount'));
        }

        return $this->useTransaction(function () use ($order, $amount, $reason) {
            // 1. Create Order Refund record via Repository
            $refund = $this->refundRepository->create([
                'order_id' => $order->id,
                'amount' => $amount,
                'reason' => $reason,
            ]);

            // 2. Adjust Order Status if full refund
            $totalRefunded = $this->refundRepository->getTotalRefunded($order);
            if ($totalRefunded >= $order->total) {
                $this->orderRepository->update($order, ['status' => OrderStatus::Refunded]);
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
