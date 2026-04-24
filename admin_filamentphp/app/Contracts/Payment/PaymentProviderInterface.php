<?php

namespace App\Contracts\Payment;

use App\Models\Order;

interface PaymentProviderInterface
{
    /**
     * Authorize a payment for the given order.
     *
     * @param Order $order
     * @param array $payload
     * @return array ['success' => bool, 'transaction_id' => string, 'message' => string]
     */
    public function authorize(Order $order, array $payload = []): array;

    /**
     * Capture a previously authorized payment.
     *
     * @param string $transactionId
     * @param int|null $amount
     * @return bool
     */
    public function capture(string $transactionId, ?int $amount = null): bool;

    /**
     * Refund a payment.
     *
     * @param string $transactionId
     * @param int|null $amount
     * @return bool
     */
    public function refund(string $transactionId, ?int $amount = null): bool;

    /**
     * Get the payment URL (for redirection drivers like VNPay/Stripe Checkout).
     *
     * @param Order $order
     * @return string|null
     */
    public function getPaymentUrl(Order $order): ?string;
}
