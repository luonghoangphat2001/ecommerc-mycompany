<?php

namespace App\Ecommerce\Order\Services;

use App\Ecommerce\Address\DTOs\Address\AddressDTO;
use App\Ecommerce\Checkout\DTOs\CheckoutRequestDTO;
use App\Ecommerce\Checkout\Services\CheckoutCalculatorService;
use App\Ecommerce\Inventory\Actions\RevertStockAction;
use App\Ecommerce\Order\Contracts\OrderRepositoryInterface;
use App\Ecommerce\Order\Contracts\OrderServiceInterface;
use App\Ecommerce\Order\Contracts\RefundServiceInterface;
use App\Ecommerce\Order\Enums\OrderStatus;
use App\Ecommerce\Order\Events\OrderCreated;
use App\Ecommerce\Product\Contracts\TaxServiceInterface;
use App\Ecommerce\Shipping\Contracts\ShippingServiceInterface;
use App\Enums\PaymentStatus;
use App\Jobs\SendOrderEmailJob;
use App\Mail\OrderAdminMail;
use App\Mail\OrderCustomerMail;
use App\Models\Order;
use App\Services\Logging\ModuleLogger;
use App\Settings\MailSettings;
use App\Support\Infrastructure;
use App\Traits\HandleTransactions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;

class OrderService implements OrderServiceInterface
{
    use HandleTransactions;

    protected $orderRepository;

    protected $taxService;

    protected $shippingService;

    protected $calculator;

    protected $refundService;

    protected RevertStockAction $revertStockAction;

    protected MailSettings $mailSettings;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        TaxServiceInterface $taxService,
        ShippingServiceInterface $shippingService,
        CheckoutCalculatorService $calculator,
        RefundServiceInterface $refundService,
        RevertStockAction $revertStockAction,
        MailSettings $mailSettings
    ) {
        $this->orderRepository = $orderRepository;
        $this->taxService = $taxService;
        $this->shippingService = $shippingService;
        $this->calculator = $calculator;
        $this->refundService = $refundService;
        $this->revertStockAction = $revertStockAction;
        $this->mailSettings = $mailSettings;
    }

    /**
     * {@inheritDoc}
     */
    public function createOrder(array $data, array $items = []): Order
    {
        return $this->useTransaction(function () use ($data, $items) {
            /** @var Order $order */
            $order = $this->orderRepository->create($data);

            if (! empty($items)) {
                foreach ($items as $item) {
                    $order->items()->create($item);
                }
            }

            $order = $this->recalculateTotals($order);

            event(new OrderCreated($order));

            return $order;
        });
    }

    public function updateOrder(Order $order, array $data): Order
    {
        return $this->useTransaction(function () use ($order, $data) {
            // 1. Update basic order data (if any other than status)
            $orderData = Arr::except($data, [
                'items', 'internal_note', 'shipping', 'billing', 'status',
                'shipping_method_id', 'coupon_code', 'redeemed_points', 'manual_tax_amount',
                'refund_type', 'refund_amount', 'refund_reason',
            ]);
            if (! empty($orderData)) {
                $this->orderRepository->update($order->id, $orderData);
            }

            // 2. Handle Status Update
            if (isset($data['status']) && $data['status'] !== $order->status->value) {
                $newStatus = OrderStatus::from($data['status']);
                $this->updateStatus($order, $newStatus);

                // Handle Refund creation if status changed to refunded
                if ($newStatus === OrderStatus::Refunded) {
                    $refundType = $data['refund_type'] ?? 'full';
                    $refundAmount = $refundType === 'full' ? $order->total : (int) ($data['refund_amount'] ?? 0);
                    $refundReason = $data['refund_reason'] ?? '';

                    if ($refundAmount > 0) {
                        $this->processRefundOrThrow(
                            $order,
                            $refundAmount,
                            $refundReason,
                            $refundType,
                            'update_order_refund_failed',
                            'Refund process failed during order update.',
                            ['refund_amount' => $refundAmount]
                        );
                    }
                }
            }

            // 3. Update order items (prices and quantities)
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    // Only update existing product items
                    $order->items()->where('id', $itemData['id'])->update([
                        'qty' => $itemData['qty'],
                        'unit_price' => $itemData['unit_price'],
                        'total' => $itemData['qty'] * $itemData['unit_price'],
                    ]);
                }
            }

            // 4. Update internal note
            if (array_key_exists('internal_note', $data)) {
                $order->internal_note = $data['internal_note'];
            }

            // 4.5 Update extra fees & promotions
            if (array_key_exists('shipping_method_id', $data)) {
                if ($data['shipping_method_id']) {
                    $method = $this->shippingService->resolveMethod((int) $data['shipping_method_id']);
                    if (! $order->shipping) {
                        $order->shipping()->create(['shop_shipping_method_id' => $data['shipping_method_id'], 'method' => $method?->name]);
                    } else {
                        $order->shipping->update(['shop_shipping_method_id' => $data['shipping_method_id'], 'method' => $method?->name]);
                    }
                } else {
                    if ($order->shipping) {
                        $order->shipping->update(['shop_shipping_method_id' => null]);
                    }
                }
            }

            if (array_key_exists('coupon_code', $data)) {
                if ($data['coupon_code']) {
                    $order->coupons()->updateOrCreate(
                        ['order_id' => $order->id],
                        ['coupon_code' => $data['coupon_code'], 'discount_amount' => 0] // Recalculated later
                    );
                } else {
                    $order->coupons()->delete();
                }
            }

            if (array_key_exists('redeemed_points', $data)) {
                $order->redeemed_points = (int) ($data['redeemed_points'] ?? 0);
            }

            if (array_key_exists('manual_tax_amount', $data)) {
                $order->manual_tax_amount = $data['manual_tax_amount'];
            }
            $order->save();

            // 5. Update addresses
            if (isset($data['shipping']) && $order->shippingAddress) {
                $validation = $this->shippingService->validateAddress($data['shipping']);
                if (! $validation['is_valid']) {
                    throw new \Exception('Địa chỉ giao hàng không hợp lệ: ' . implode(', ', $validation['errors']));
                }
                $order->shippingAddress->update($data['shipping']);
            }
            if (isset($data['billing']) && $order->billingAddress) {
                $order->billingAddress->update($data['billing']);
            }

            return $this->recalculateTotals($order->fresh());
        });
    }

    /**
     * {@inheritDoc}
     */
    public function updateStatus(Order $order, OrderStatus $newStatus): bool
    {
        return $this->orderRepository->update($order->id, ['status' => $newStatus]);
    }

    /**
     * {@inheritDoc}
     */
    public function recalculateTotals(Order $order): Order
    {
        return $this->useTransaction(function () use ($order) {
            // 1. Product rows remain the only records in shop_order_items.
            $order->taxes()->delete();

            // 2. Build items for checkout calculation
            $itemsForCalc = [];
            foreach ($order->items as $item) {
                // Determine tax_class_id based on product apply_tax setting
                $taxClassId = null;
                if ($item->product) {
                    $taxClassId = $item->product->apply_tax ? $item->product->tax_class_id : null;
                }

                $itemsForCalc[] = [
                    'product_id' => $item->shop_product_id,
                    'qty' => $item->qty,
                    'unit_price' => $item->unit_price,
                    'total' => $item->unit_price * $item->qty,
                    'tax_class_id' => $taxClassId,
                ];
            }

            // 3. Use CheckoutCalculatorService (same as checkout flow)
            $address = $order->shippingAddress;
            $shippingAddressDTO = new AddressDTO(
                first_name: $address?->first_name ?? '',
                last_name: $address?->last_name ?? '',
                phone: $address?->phone ?? '',
                email: $address?->email ?? '',
                country_code: $address?->country_code ?? 'VN',
                state_id: $address?->state_id ?? null,
                city_id: $address?->city_id ?? null,
                ward_id: $address?->ward_id ?? null,
                address_detail: $address?->address_detail ?? '',
            );

            // Get coupon code from order coupon snapshot
            $couponCode = $order->coupons()->first()?->coupon_code ?? null;

            // Get redeemed points from order meta if it exists
            $redeemedPoints = (int) $order->redeemed_points;

            $calcRequest = new CheckoutRequestDTO(
                items: $itemsForCalc,
                shippingMethod: $order->shipping?->shop_shipping_method_id,
                shippingAddress: $shippingAddressDTO,
                couponCode: $couponCode,
                currency: $order->currency ?? 'VND'
            );
            $calcRequest->redeemPoints = $redeemedPoints;

            $calcResult = $this->calculator->calculate($calcRequest);

            // 4. Save tax breakdown in its dedicated table.
            foreach ($calcResult->appliedTaxes as $tax) {
                $order->taxes()->create([
                    'name' => $tax['name'],
                    'amount' => (int) $tax['amount'],
                    'is_shipping' => false,
                    'metadata' => ['rate' => $tax['rate'] ?? null],
                ]);
            }

            // 5. Keep shipping charge on its dedicated shipping record.
            if ($order->shipping) {
                $order->shipping->update(['amount' => $calcResult->shippingTotal]);
            }

            // 6. Update coupons
            if ($couponCode) {
                $order->coupons()->where('coupon_code', $couponCode)->update([
                    'discount_amount' => $calcResult->discountTotal,
                ]);
            }

            // 7. Apply the optional admin tax override.
            $taxAmount = $calcResult->taxTotal;
            $total = $calcResult->total;

            if ($order->manual_tax_amount !== null) {
                $taxAmount = max($taxAmount, (int) $order->manual_tax_amount);
                $total = $calcResult->subtotal - $calcResult->discountTotal - $calcResult->loyaltyDiscountTotal + $calcResult->shippingTotal + $taxAmount;
            }

            if ($taxAmount > $calcResult->taxTotal) {
                $order->taxes()->create([
                    'name' => 'Manual tax adjustment',
                    'amount' => $taxAmount - $calcResult->taxTotal,
                    'is_shipping' => false,
                    'metadata' => ['source' => 'admin_override'],
                ]);
            }

            // 8. Update order totals with all calculations
            $order->update([
                'subtotal' => $calcResult->subtotal,
                'discount_total' => $calcResult->discountTotal,
                'loyalty_discount_total' => $calcResult->loyaltyDiscountTotal,
                'shipping_total' => $calcResult->shippingTotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'currency' => $calcResult->currency,
                'exchange_rate' => $calcResult->exchangeRate,
            ]);

            return $order;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function deleteOrder(Order $order): bool
    {
        return $this->orderRepository->delete($order->id);
    }

    /**
     * {@inheritDoc}
     */
    public function cancel(Order $order, string $reason): bool
    {
        return $this->useTransaction(function () use ($order, $reason) {
            $order = $this->orderRepository->find($order->id) ?? $order;

            // Cancellation is a state transition. Replaying it must not restore stock twice.
            if ($order->status === OrderStatus::Cancelled
                || ($order->status instanceof OrderStatus && $order->status === OrderStatus::Cancelled)
                || $order->status === OrderStatus::Cancelled->value) {
                return true;
            }

            $order->update([
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
            ]);

            // Revert stock for all product items (simple, variable, combo)
            foreach ($order->items as $item) {
                $this->revertStockAction->revertOrderItem($item);
            }

            return $this->updateStatus($order, OrderStatus::Cancelled);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function refund(Order $order, string $reason): bool
    {
        return $this->useTransaction(function () use ($order, $reason) {
            $this->updateStatus($order, OrderStatus::Refunded);

            $this->processRefundOrThrow($order, $order->total, $reason, 'full', 'refund_failed', 'Refund failed.');

            return true;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function confirmPayment(Order $order): bool
    {
        return $this->useTransaction(function () use ($order) {
            $order->payments()->where('status', PaymentStatus::Pending->value)->update(['status' => PaymentStatus::Paid->value]);

            // If order was pending, move to processing if paid?
            // Often business logic dictates that paid orders move to Processing.
            if ($order->status === OrderStatus::Pending) {
                $this->updateStatus($order, OrderStatus::Processing);
            }

            return true;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function sendOrderConfirmationMail(Order $order): void
    {
        $mailClass = OrderCustomerMail::class;

        if ($this->mailSettings->use_queue_for_emails) {
            $this->queueOrderMail($order, $mailClass);
        } else {
            $customerEmail = Infrastructure::customerEmail($order);
            if ($customerEmail) {
                Mail::to($customerEmail)->send(new $mailClass($order));
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function sendAdminOrderNotification(Order $order): void
    {
        $mailClass = OrderAdminMail::class;

        if ($this->mailSettings->use_queue_for_emails) {
            $this->queueOrderMail($order, $mailClass);
        } else {
            Mail::to(Infrastructure::adminEmail())->send(new $mailClass($order));
        }
    }

    private function queueOrderMail(Order $order, string $mailClass): void
    {
        SendOrderEmailJob::dispatch((int) $order->getKey(), $mailClass)->afterCommit();
    }

    /**
     * {@inheritDoc}
     */
    public function hasPendingPayments(Order $order): bool
    {
        return $order->payments()->where('status', PaymentStatus::Pending->value)->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function paginateFiltered(int $perPage = 15, ?int $userId = null): LengthAwarePaginator
    {
        return $this->orderRepository->paginateFiltered($perPage, $userId);
    }

    /**
     * {@inheritDoc}
     */
    public function getFullOrder(int | string $id): ?Order
    {
        return $this->orderRepository->getFullOrder($id);
    }

    /**
     * {@inheritDoc}
     */
    public function find(int | string $id): ?Order
    {
        return $this->orderRepository->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getTaxTotal(Order $order): int
    {
        return $this->orderRepository->getTaxTotal($order);
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalShipping(Order $order): int
    {
        return $this->orderRepository->getTotalShipping($order);
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingTotalWithTax(Order $order): int
    {
        return $this->orderRepository->getShippingTotalWithTax($order);
    }

    /**
     * {@inheritDoc}
     */
    public function getLoyaltyDiscountTotal(Order $order): int
    {
        return $this->orderRepository->getLoyaltyDiscountTotal($order);
    }

    /**
     * {@inheritDoc}
     */
    public function getDistinctStatuses(): array
    {
        return $this->orderRepository->getDistinctStatuses();
    }

    /**
     * {@inheritDoc}
     */
    public function getTableQuery(): Builder
    {
        return $this->orderRepository->query()
            ->with(['shippingAddress', 'billingAddress', 'taxes', 'shipping', 'payments', 'items.taxes', 'shipping.taxes']);
    }

    private function logRefundFailure(
        string $event,
        string $message,
        Order $order,
        \Throwable $exception,
        array $context = []
    ): void {
        ModuleLogger::order()->error($event, $message, array_merge([
            'order_id' => $order->id,
            'exception' => get_class($exception),
        ], $context));
    }

    private function processRefundOrThrow(
        Order $order,
        float $amount,
        string $reason,
        string $type,
        string $event,
        string $message,
        array $context = []
    ): void {
        try {
            $this->refundService->processRefund($order, $amount, $reason, $type);
        } catch (\Exception $exception) {
            $this->logRefundFailure($event, $message, $order, $exception, $context);

            throw $exception;
        }
    }
}
