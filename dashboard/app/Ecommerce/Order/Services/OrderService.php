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

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        TaxServiceInterface $taxService,
        ShippingServiceInterface $shippingService,
        CheckoutCalculatorService $calculator
    ) {
        $this->orderRepository = $orderRepository;
        $this->taxService = $taxService;
        $this->shippingService = $shippingService;
        $this->calculator = $calculator;
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

            $calcRequest = new CheckoutRequestDTO(
                items: $itemsForCalc,
                shippingMethod: $order->shipping?->method,
                shippingAddress: $shippingAddressDTO,
                couponCode: $couponCode,
                currency: $order->currency ?? 'VND'
            );

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

            // 5. Save shipping item
            if ($calcResult->shippingTotal > 0) {
                $order->items()->create([
                    'type' => 'shipping',
                    'name' => 'Giao hàng: ' . ($order->shipping?->method ?? 'Standard'),
                    'qty' => 1,
                    'unit_price' => $calcResult->shippingTotal,
                    'total' => $calcResult->shippingTotal,
                ]);
            }

            // 6. Update order totals with all calculations (same as checkout)
            $order->update([
                'subtotal' => $calcResult->subtotal,
                'tax_amount' => $calcResult->taxTotal,
                'total' => $calcResult->total,
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
