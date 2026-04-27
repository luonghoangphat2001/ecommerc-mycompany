<?php

namespace App\Services;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Services\OrderServiceInterface;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Traits\HandleTransactions;
use Illuminate\Support\Facades\DB;

class OrderService implements OrderServiceInterface
{
    use HandleTransactions;

    protected $orderRepository;
    protected $taxService;
    protected $shippingService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        TaxService $taxService,
        ShippingService $shippingService
    ) {
        $this->orderRepository = $orderRepository;
        $this->taxService = $taxService;
        $this->shippingService = $shippingService;
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

            return $this->recalculateTotals($order);
        });
    }

    /**
     * @inheritDoc
     */
    public function updateOrder(Order $order, array $data): Order
    {
        return $this->useTransaction(function () use ($order, $data) {
            $this->orderRepository->update($order->id, $data);
            
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
            // 1. Flush volatile pricing line items (not Taxes/Shipping anymore as they have dedicated tables)
            $order->items()->whereIn('type', ['tax', 'shipping', 'fee'])->delete();

            $subtotal = $order->productItems()->sum('total');
            $address = $order->shippingAddress;
            $country = $address?->country_code ?? 'VN';

            $totalTaxAmount = 0;

            // 2. Process Itemized Taxes
            foreach ($order->productItems as $item) {
                if ($product = $item->product) {
                    $taxClass = $product->taxClass;
                    if ($taxClass) {
                        $taxResult = $this->taxService->calculate($taxClass, $item->total, $country);
                        $taxAmount = $taxResult['amount'];
                        $taxRateId = $taxResult['tax_rate_id'];
                        $rateName = $taxResult['rate_name'];

                        if ($taxAmount >= 0) {
                            $order->taxes()->updateOrCreate(
                                ['shop_order_item_id' => $item->id],
                                [
                                    'shop_tax_rate_id' => $taxRateId,
                                    'name' => $rateName,
                                    'amount' => $taxAmount,
                                    'is_shipping' => false,
                                ]
                            );
                            $totalTaxAmount += $taxAmount;
                        }
                    }
                }
            }

            // Cleanup taxes for items that no longer exist
            $order->taxes()
                ->whereNotNull('shop_order_item_id')
                ->whereNotIn('shop_order_item_id', $order->productItems->pluck('id'))
                ->delete();

            // 3. Resolve Shipping Eligibility & Allocation
            $availableMethods = $this->shippingService->getAvailableMethods(
                country: $country,
                state: $address?->state_id,
                postcode: $address?->postal_code,
                ward: $address?->ward_id,
                subtotal: (int) $subtotal
            );

            $shippingInfo = $order->shipping ?? new \App\Models\OrderShipping(['order_id' => $order->id]);
            $selectedMethod = $availableMethods->firstWhere('method_id', $shippingInfo->shop_shipping_method_id);
            if (!$selectedMethod) {
                $selectedMethod = $availableMethods->firstWhere('name', $shippingInfo->method);
            }
            if (!$selectedMethod) {
                $selectedMethod = $availableMethods->first();
            }

            if ($selectedMethod) {
                $shipping = $order->shipping()->updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'shop_shipping_method_id' => $selectedMethod['method_id'],
                        'method' => $selectedMethod['name'],
                        'amount' => $selectedMethod['cost'],
                    ]
                );

                // Create/Update Shipping Tax in relational table
                $shippingTaxRecord = $order->taxes()->updateOrCreate(
                    ['is_shipping' => true, 'shop_order_item_id' => null],
                    [
                        'name' => 'Shipping Tax',
                        'amount' => $shipping->tax_amount ?? 0,
                    ]
                );
                $totalTaxAmount += $shippingTaxRecord->amount;
            }

            // 4. Update core order payload signature
            $order->update([
                'subtotal' => $subtotal,
                'tax_amount' => $totalTaxAmount,
                'total' => $subtotal + $totalTaxAmount + ($selectedMethod['cost'] ?? 0),
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

            // Create refund record
            $order->refunds()->create([
                'amount' => $order->total,
                'reason' => $reason
            ]);

            // Create reversal payment record
            $order->payments()->create([
                'method' => $order->payments()->latest()->first()?->method ?? 'other',
                'amount' => -$order->total,
                'status' => 'refunded',
                'currency' => $order->currency ?? 'VND',
                'reference' => 'Refund for order #' . $order->number
            ]);

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
        $settings = app(\App\Settings\MailSettings::class);
        $mailClass = \App\Mail\OrderCustomerMail::class;

        if ($settings->use_queue_for_emails) {
            \App\Jobs\SendOrderEmailJob::dispatch($order, $mailClass);
        } else {
            $customerEmail = $order->billingAddress?->email ?? $order->shippingAddress?->email ?? $order->user?->email;
            if ($customerEmail) {
                \Illuminate\Support\Facades\Mail::to($customerEmail)
                    ->send(new $mailClass($order));
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function sendAdminOrderNotification(Order $order): void
    {
        $settings = app(\App\Settings\MailSettings::class);
        $mailClass = \App\Mail\OrderAdminMail::class;

        if ($settings->use_queue_for_emails) {
            \App\Jobs\SendOrderEmailJob::dispatch($order, $mailClass);
        } else {
            \Illuminate\Support\Facades\Mail::to(config('mail.from.address'))
                ->send(new $mailClass($order));
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
    public function paginateFiltered(int $perPage = 15, ?int $userId = null): \Illuminate\Pagination\LengthAwarePaginator
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
    public function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->orderRepository->query()
            ->with(['shippingAddress', 'billingAddress', 'metas', 'taxes', 'shipping', 'payments', 'productItems.tax', 'shipping.tax']);
    }
}
