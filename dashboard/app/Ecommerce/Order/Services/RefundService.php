<?php

namespace App\Ecommerce\Order\Services;

use App\Ecommerce\Order\Contracts\OrderRefundRepositoryInterface;
use App\Ecommerce\Order\Contracts\RefundServiceInterface;
use App\Ecommerce\Payment\Services\PaymentManager;
use App\Models\Order;
use App\Models\OrderRefund;
use App\Traits\HandleTransactions;
use Exception;
use Illuminate\Support\Facades\Log;

class RefundService implements RefundServiceInterface
{
    use HandleTransactions;

    protected $refundRepository;
    protected $paymentManager;

    public function __construct(
        OrderRefundRepositoryInterface $refundRepository,
        PaymentManager $paymentManager
    ) {
        $this->refundRepository = $refundRepository;
        $this->paymentManager = $paymentManager;
    }

    /**
     * @inheritDoc
     */
    public function validateRefundAvailability(Order $order, float $amount): bool
    {
        if ($amount <= 0) {
            throw new Exception('Số tiền hoàn phải lớn hơn 0.');
        }

        $totalRefunded = $this->refundRepository->getTotalRefunded($order);
        $availableToRefund = $order->total - $totalRefunded;

        if ($amount > $availableToRefund) {
            throw new Exception("Số tiền hoàn ({$amount}) vượt quá số tiền có thể hoàn của đơn hàng ({$availableToRefund}).");
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function processRefund(Order $order, float $amount, string $reason, string $type = 'full'): ?OrderRefund
    {
        return $this->useTransaction(function () use ($order, $amount, $reason, $type) {
            $this->validateRefundAvailability($order, $amount);

            $payment = $order->payments()->latest()->first();
            $paymentMethod = $payment?->method ?? 'other';
            $transactionId = $payment?->transaction_id ?? 'N/A';

            // 1. Try to process refund via PaymentManager if the gateway supports it.
            // If the method is 'other' or 'cod' or unsupported, it might just return true or we catch exception.
            try {
                // If a driver exists for the method, attempt to refund.
                $driver = $this->paymentManager->driver($paymentMethod);
                if ($driver) {
                    $refundSuccess = $driver->refund($transactionId, (int) $amount);
                    if (!$refundSuccess) {
                        throw new Exception("Lỗi khi gọi API hoàn tiền từ cổng thanh toán {$paymentMethod}.");
                    }
                }
            } catch (Exception $e) {
                // For manual/cod methods, there might be no driver, which is fine, we just record it locally.
                // We only log if it's an actual API failure.
                if (str_contains($e->getMessage(), 'not supported') || str_contains($e->getMessage(), 'not found')) {
                    \App\Services\Logging\ModuleLogger::refund()->info('refund_local_record', "No payment driver for {$paymentMethod}. Proceeding with local DB refund record.", ['order_id' => $order->id, 'payment_method' => $paymentMethod]);
                } else {
                    \App\Services\Logging\ModuleLogger::refund()->error('refund_gateway_error', "PaymentGateway Refund Error: " . $e->getMessage(), ['order_id' => $order->id, 'transaction_id' => $transactionId]);
                    throw $e;
                }
            }

            // 2. Create the refund record
            /** @var OrderRefund $refund */
            $refund = $this->refundRepository->create([
                'order_id' => $order->id,
                'amount' => $amount,
                'reason' => $reason,
                'metadata' => [
                    'type' => $type,
                    'status' => 'completed',
                    'gateway_transaction_id' => $transactionId,
                ]
            ]);

            // 3. Create a reversal payment record to balance the books
            $order->payments()->create([
                'method' => $paymentMethod,
                'amount' => -$amount,
                'status' => 'refunded',
                'currency' => $order->currency ?? 'VND',
                'reference' => 'Refund for order #' . $order->number . ' - ' . $reason
            ]);

            return $refund;
        });
    }
}
