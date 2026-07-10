<?php

namespace App\Ecommerce\Order\Services;

use App\Ecommerce\Order\Contracts\OrderRepositoryInterface;
use App\Ecommerce\Order\Contracts\OrderServiceInterface;
use App\Models\Order;
use App\Models\OrderShipping;
use App\Ecommerce\Order\Enums\OrderStatus;
use App\Traits\HandleTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

use App\Ecommerce\Product\Contracts\TaxServiceInterface;
use App\Ecommerce\Shipping\Contracts\ShippingServiceInterface;
use App\Ecommerce\Checkout\Services\CheckoutCalculatorService;
use App\Ecommerce\Checkout\DTOs\CheckoutRequestDTO;
use App\Ecommerce\Address\DTOs\Address\AddressDTO;

use App\Ecommerce\Order\Events\OrderCreated;
use App\Settings\MailSettings;
use App\Mail\OrderCustomerMail;
use App\Mail\OrderAdminMail;
use App\Jobs\SendOrderEmailJob;
use Illuminate\Support\Facades\Mail;

class OrderService implements OrderServiceInterface
{
    use HandleTransactions;

    protected $orderRepository;
    protected $taxService;
    protected $shippingService;
    protected $calculator;
    protected $refundService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        TaxServiceInterface $taxService,
        ShippingServiceInterface $shippingService,
        CheckoutCalculatorService $calculator,
        \App\Ecommerce\Order\Contracts\RefundServiceInterface $refundService
    ) {
        $this->orderRepository = $orderRepository;
        $this->taxService = $taxService;
        $this->shippingService = $shippingService;
        $this->calculator = $calculator;
        $this->refundService = $refundService;
    }

    /**
     * @inheritDoc
     */
    public function createOrder(array $data, array $items = []): Order
    {
        return $this->useTransaction(function () use ($data, $items) {
            /** @var Order $order */
            $order = $this->orderRepository->create($data);

            if (!empty($items)) {
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
            $orderData = \Illuminate\Support\Arr::except($data, [
                'items', 'internal_note', 'shipping', 'billing', 'status',
                'shipping_method_id', 'coupon_code', 'redeemed_points', 'manual_tax_amount',
                'refund_type', 'refund_amount', 'refund_reason'
            ]);
            if (!empty($orderData)) {
                $this->orderRepository->update($order->id, $orderData);
            }

            // 2. Handle Status Update
            if (isset($data['status']) && $data['status'] !== $order->status->value) {
                $newStatus = \App\Ecommerce\Order\Enums\OrderStatus::from($data['status']);
                $this->updateStatus($order, $newStatus);

                // Handle Refund creation if status changed to refunded
                if ($newStatus->value === 'refunded') {
                    $refundType = $data['refund_type'] ?? 'full';
                    $refundAmount = $refundType === 'full' ? $order->total : (int) ($data['refund_amount'] ?? 0);
                    $refundReason = $data['refund_reason'] ?? '';

                    if ($refundAmount > 0) {
                        try {
                            $this->refundService->processRefund($order, $refundAmount, $refundReason, $refundType);
                        } catch (\Exception $e) {
                            \App\Services\Logging\ModuleLogger::order()->error('update_order_refund_failed', "Refund process failed during order update: " . $e->getMessage(), ['order_id' => $order->id, 'refund_amount' => $refundAmount]);
                            throw $e; // Re-throw to rollback transaction
                        }
                    }
                }
            }

            // 3. Update order items (prices and quantities)
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    // Only update existing product items
                    $order->productItems()->where('id', $itemData['id'])->update([
                        'qty' => $itemData['qty'],
                        'unit_price' => $itemData['unit_price'],
                        'total' => $itemData['qty'] * $itemData['unit_price'],
                    ]);
                }
            }

            // 4. Update internal note
            if (array_key_exists('internal_note', $data)) {
                $order->metas()->updateOrCreate(
                    ['key' => 'internal_note'],
                    ['value' => $data['internal_note']]
                );
            }

            // 4.5 Update extra fees & promotions
            if (array_key_exists('shipping_method_id', $data)) {
                if ($data['shipping_method_id']) {
                    $method = \App\Models\ShippingMethod::find($data['shipping_method_id']);
                    if (!$order->shipping) {
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
                $order->metas()->updateOrCreate(
                    ['key' => 'redeemed_points'],
                    ['value' => (string) ($data['redeemed_points'] ?? 0)]
                );
            }

            if (array_key_exists('manual_tax_amount', $data)) {
                if ($data['manual_tax_amount'] !== null) {
                    $order->metas()->updateOrCreate(
                        ['key' => 'manual_tax_amount'],
                        ['value' => (string) $data['manual_tax_amount']]
                    );
                } else {
                    $order->metas()->where('key', 'manual_tax_amount')->delete();
                }
            }

            // 5. Update addresses
            if (isset($data['shipping']) && $order->shippingAddress) {
                $validation = $this->shippingService->validateAddress($data['shipping']);
                if (!$validation['is_valid']) {
                    throw new \Exception("Địa chỉ giao hàng không hợp lệ: " . implode(", ", $validation['errors']));
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
     * @inheritDoc
     */
    public function updateStatus(Order $order, OrderStatus $newStatus): bool
    {
        return $this->orderRepository->update($order->id, ['status' => $newStatus]);
    }

    /**
     * @inheritDoc
     */
    public function recalculateTotals(Order $order): Order
    {
        return $this->useTransaction(function () use ($order) {
            // 1. Flush volatile pricing line items
            $order->items()->whereIn('type', ['tax', 'shipping', 'fee'])->delete();

            // 2. Build items for checkout calculation
            $itemsForCalc = [];
            foreach ($order->productItems as $item) {
                $itemsForCalc[] = [
                    'product_id' => $item->shop_product_id,
                    'qty' => $item->qty,
                    'unit_price' => $item->unit_price,
                    'total' => $item->unit_price * $item->qty,
                    'tax_class_id' => $item->product?->tax_class_id,
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
            $redeemedPoints = (int) $order->metas()->where('key', 'redeemed_points')->first()?->value ?? 0;

            $calcRequest = new CheckoutRequestDTO(
                items: $itemsForCalc,
                shippingMethod: $order->shipping?->shop_shipping_method_id,
                shippingAddress: $shippingAddressDTO,
                couponCode: $couponCode,
                currency: $order->currency ?? 'VND'
            );
            $calcRequest->redeemPoints = $redeemedPoints;

            $calcResult = $this->calculator->calculate($calcRequest);

            // 4. Save tax details from calculation
            foreach ($calcResult->appliedTaxes as $tax) {
                $order->items()->create([
                    'type' => 'tax',
                    'name' => $tax['name'],
                    'qty' => 1,
                    'unit_price' => $tax['amount'],
                    'total' => $tax['amount'],
                ]);
            }

            // 5. Save shipping item and update OrderShipping
            if ($calcResult->shippingTotal > 0) {
                $order->items()->create([
                    'type' => 'shipping',
                    'name' => 'Giao hàng: ' . ($order->shipping?->method ?? 'Standard'),
                    'qty' => 1,
                    'unit_price' => $calcResult->shippingTotal,
                    'total' => $calcResult->shippingTotal,
                ]);
                
                if ($order->shipping) {
                    $order->shipping->update(['amount' => $calcResult->shippingTotal]);
                }
            } else {
                if ($order->shipping) {
                    $order->shipping->update(['amount' => 0]);
                }
            }

            // 6. Update coupons
            if ($couponCode) {
                $order->coupons()->where('coupon_code', $couponCode)->update([
                    'discount_amount' => $calcResult->discountTotal
                ]);
            }
            
            // 6.5 Update loyalty discount meta
            if ($calcResult->loyaltyDiscountTotal > 0) {
                $order->metas()->updateOrCreate(
                    ['key' => 'loyalty_discount'],
                    ['value' => (string) $calcResult->loyaltyDiscountTotal]
                );
            } else {
                $order->metas()->where('key', 'loyalty_discount')->delete();
            }

            // 7. Handle manual tax override
            $manualTaxMeta = $order->metas()->where('key', 'manual_tax_amount')->first();
            $taxAmount = $calcResult->taxTotal;
            $total = $calcResult->total;
            
            if ($manualTaxMeta && $manualTaxMeta->value !== null && $manualTaxMeta->value !== '') {
                $manualTax = (int) $manualTaxMeta->value;
                // If manual tax is higher than calculated tax, or just override completely?
                // The user said: "nếu admin ko edit thì lấy tự động theo hệ thông admin có edit thì lấy cao hơn" -> wait, does that mean max(auto, manual) or override?
                // "có edit thì lấy cao hơn" usually means "if edited, prioritize the edit". Let's just override it if it is set.
                // Wait, "lấy cao hơn" could literally mean max(). I will use max to be safe.
                if ($manualTax > $taxAmount) {
                    $taxAmount = $manualTax;
                    $total = $calcResult->subtotal - $calcResult->discountTotal - $calcResult->loyaltyDiscountTotal + $calcResult->shippingTotal + $taxAmount;
                }
            }

            // 8. Update order totals with all calculations
            $order->update([
                'subtotal' => $calcResult->subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'currency' => $calcResult->currency,
                'exchange_rate' => $calcResult->exchangeRate,
            ]);

            return $order;
        });
    }

    /**
     * @inheritDoc
     */
    public function deleteOrder(Order $order): bool
    {
        return $this->orderRepository->delete($order->id);
    }

    /**
     * @inheritDoc
     */
    public function cancel(Order $order, string $reason): bool
    {
        return $this->useTransaction(function () use ($order, $reason) {
            $order->metas()->updateOrCreate(
                ['key' => 'cancellation_reason'],
                ['value' => $reason]
            );
            $order->metas()->updateOrCreate(
                ['key' => 'cancelled_at'],
                ['value' => now()->toDateTimeString()]
            );

            return $order->update(['status' => OrderStatus::Cancelled]);
        });
    }

    /**
     * @inheritDoc
     */
    public function refund(Order $order, string $reason): bool
    {
        return $this->useTransaction(function () use ($order, $reason) {
            $this->orderRepository->update($order->id, [
                'status' => OrderStatus::Refunded,
            ]);

            try {
                $this->refundService->processRefund($order, $order->total, $reason, 'full');
            } catch (\Exception $e) {
                \App\Services\Logging\ModuleLogger::order()->error('refund_failed', "Refund failed: " . $e->getMessage(), ['order_id' => $order->id]);
                throw $e;
            }

            return true;
        });
    }

    /**
     * @inheritDoc
     */
    public function confirmPayment(Order $order): bool
    {
        return $this->useTransaction(function () use ($order) {
            $order->payments()->where('status', 'pending')->update(['status' => 'paid']);
            
            // If order was pending, move to processing if paid? 
            // Often business logic dictates that paid orders move to Processing.
            if ($order->status === OrderStatus::Pending) {
                $this->updateStatus($order, OrderStatus::Processing);
            }

            return true;
        });
    }

    /**
     * @inheritDoc
     */
    public function sendOrderConfirmationMail(Order $order): void
    {
        $settings = app(MailSettings::class);
        $mailClass = OrderCustomerMail::class;

        if ($settings->use_queue_for_emails) {
            SendOrderEmailJob::dispatch($order, $mailClass);
        } else {
            $customerEmail = $order->billingAddress?->email ?? $order->shippingAddress?->email ?? $order->user?->email;
            if ($customerEmail) {
                Mail::to($customerEmail)->send(new $mailClass($order));
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function sendAdminOrderNotification(Order $order): void
    {
        $settings = app(MailSettings::class);
        $mailClass = OrderAdminMail::class;

        if ($settings->use_queue_for_emails) {
            SendOrderEmailJob::dispatch($order, $mailClass);
        } else {
            Mail::to(config('mail.from.address'))->send(new $mailClass($order));
        }
    }
    /**
     * @inheritDoc
     */
    public function hasPendingPayments(Order $order): bool
    {
        return $order->payments()->where('status', 'pending')->exists();
    }

    /**
     * @inheritDoc
     */
    public function paginateFiltered(int $perPage = 15, ?int $userId = null): LengthAwarePaginator
    {
        return $this->orderRepository->paginateFiltered($perPage, $userId);
    }

    /**
     * @inheritDoc
     */
    public function getFullOrder(int|string $id): ?Order
    {
        return $this->orderRepository->getFullOrder($id);
    }

    /**
     * @inheritDoc
     */
    public function find(int|string $id): ?Order
    {
        return $this->orderRepository->find($id);
    }

    /**
     * @inheritDoc
     */
    public function getTaxTotal(Order $order): int
    {
        return $this->orderRepository->getTaxTotal($order);
    }

    /**
     * @inheritDoc
     */
    public function getTotalShipping(Order $order): int
    {
        return $this->orderRepository->getTotalShipping($order);
    }

    /**
     * @inheritDoc
     */
    public function getShippingTotalWithTax(Order $order): int
    {
        return $this->orderRepository->getShippingTotalWithTax($order);
    }

    /**
     * @inheritDoc
     */
    public function getLoyaltyDiscountTotal(Order $order): int
    {
        return $this->orderRepository->getLoyaltyDiscountTotal($order);
    }

    /**
     * @inheritDoc
     */
    public function getMetaValue(Order $order, string $key): ?string
    {
        return $this->orderRepository->getMetaValue($order, $key);
    }

    /**
     * @inheritDoc
     */
    public function getDistinctStatuses(): array
    {
        return $this->orderRepository->getDistinctStatuses();
    }

    /**
     * @inheritDoc
     */
    public function getTableQuery(): Builder
    {
        return $this->orderRepository->query()
            ->with(['shippingAddress', 'billingAddress', 'metas', 'taxes', 'shipping', 'payments', 'productItems.tax', 'shipping.tax']);
    }
}
