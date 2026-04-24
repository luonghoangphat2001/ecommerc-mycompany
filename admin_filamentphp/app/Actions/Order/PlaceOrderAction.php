<?php

namespace App\Actions\Order;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DTOs\Checkout\CheckoutRequestDTO;
use App\DTOs\Order\OrderDataDTO;
use App\Events\OrderCreated;
use App\Services\Order\OrderCalculatorService;
use App\Traits\HandleTransactions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PlaceOrderAction
{
    use HandleTransactions;

    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected ProductRepositoryInterface $productRepository,
        protected OrderCalculatorService $calculator
    ) {}

    /**
     * Handle the place order action.
     *
     * @param OrderDataDTO $dto
     * @return \App\Models\Order
     */
    public function execute(OrderDataDTO $dto)
    {
        return $this->useTransaction(function () use ($dto) {
            // 1. Prepare items for calculation
            $itemsForCalc = [];
            foreach ($dto->items as $item) {
                $product = $this->productRepository->find($item['product_id']);
                if ($product) {
                    $itemsForCalc[] = [
                        'product_id' => $product->id,
                        'qty' => $item['qty'],
                        'unit_price' => $product->price,
                        'total' => $product->price * $item['qty'],
                        'tax_class_id' => $product->tax_class_id,
                    ];
                }
            }

            // 2. Calculate totals using pure service
            $calcRequest = new CheckoutRequestDTO(
                items: $itemsForCalc,
                shippingMethod: $dto->shippingMethod,
                shippingAddress: $dto->shippingAddress,
                couponCode: $dto->couponCode,
                currency: $dto->currency
            );

            $calcResult = $this->calculator->calculate($calcRequest);

            // 3. Create Order
            $order = $this->orderRepository->create([
                'shop_customer_id' => $dto->customerId,
                'number' => 'ORD-' . strtoupper(Str::random(10)),
                'email' => $dto->email,
                'phone' => $dto->phone,
                'subtotal' => $calcResult->subtotal,
                'tax_amount' => $calcResult->taxTotal,
                'total' => $calcResult->total,
                'currency' => $calcResult->currency,
                'exchange_rate' => $calcResult->exchangeRate,
                'shipping_method' => $dto->shippingMethod,
                'notes' => $dto->notes,
                'status' => 'new',
            ]);

            // 4. Create Order Items (Products)
            foreach ($itemsForCalc as $item) {
                $order->items()->create([
                    'type' => 'product',
                    'shop_product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['total'],
                ]);
            }

            // 5. Create Order Items (Tax, Shipping, etc.)
            if ($calcResult->shippingTotal > 0) {
                $order->items()->create([
                    'type' => 'shipping',
                    'name' => 'Giao hàng: ' . $dto->shippingMethod,
                    'qty' => 1,
                    'unit_price' => $calcResult->shippingTotal,
                    'total' => $calcResult->shippingTotal,
                ]);
            }

            foreach ($calcResult->appliedTaxes as $tax) {
                $order->items()->create([
                    'type' => 'tax',
                    'name' => $tax['name'],
                    'qty' => 1,
                    'unit_price' => $tax['amount'],
                    'total' => $tax['amount'],
                ]);
            }

            // 6. Create Addresses
            $order->shippingAddress()->create($dto->shippingAddress);
            $order->billingAddress()->create($dto->billingAddress);

            // 7. Dispatch Event
            event(new OrderCreated($order));

            activity('order')
                ->performedOn($order)
                ->log('Order placed successfully via API');

            return $order;
        });
    }
}
