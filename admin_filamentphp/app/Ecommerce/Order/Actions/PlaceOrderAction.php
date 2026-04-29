<?php

namespace App\Ecommerce\Order\Actions;

use App\Ecommerce\Order\Contracts\OrderRepositoryInterface;
use App\Ecommerce\Product\Contracts\ProductRepositoryInterface;
use App\Ecommerce\Order\DTOs\Checkout\CheckoutRequestDTO;
use App\Ecommerce\Order\DTOs\Order\OrderDataDTO;
use App\Ecommerce\Order\Events\OrderCreated;
use App\Ecommerce\Order\Services\OrderCalculatorService;
use App\Traits\HandleTransactions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Ecommerce\Address\DTOs\Address\AddressDTO;
use App\Models\Order;

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
     * @return Order
     */
    public function execute(OrderDataDTO $dto)
    {
        return $this->useTransaction(function () use ($dto) {
            // 1. Prepare items for calculation
            $itemsForCalc = [];
            foreach ($dto->items as $item) {
                $product = $this->productRepository->find($item['product_id']);
                if ($product) {
                    // 🛡️ Price Integrity Check
                    if (isset($item['price']) && (float)$item['price'] !== (float)$product->price) {
                        Log::warning("Price mismatch for product #{$product->id}: Client sent {$item['price']}, DB has {$product->price}");
                        throw new \Exception(__('admin.api.price_mismatch'));
                    }

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
            $shippingAddressDTO = new AddressDTO(
                first_name: $dto->shippingAddress['first_name'] ?? '',
                last_name: $dto->shippingAddress['last_name'] ?? '',
                phone: $dto->shippingAddress['phone'] ?? '',
                email: $dto->shippingAddress['email'] ?? '',
                country_code: $dto->shippingAddress['country'] ?? 'VN',
                state_id: $dto->shippingAddress['state'] ?? null,
                city_id: $dto->shippingAddress['city'] ?? null,
                ward_id: $dto->shippingAddress['region'] ?? null,
                address_detail: $dto->shippingAddress['street'] ?? '',
            );
            
            $calcRequest = new CheckoutRequestDTO(
                items: $itemsForCalc,
                shippingMethod: $dto->shippingMethod,
                shippingAddress: $shippingAddressDTO,
                couponCode: $dto->couponCode,
                currency: $dto->currency
            );

            $calcResult = $this->calculator->calculate($calcRequest);

            // 3. Create Order
            $order = $this->orderRepository->create([
                'user_id' => $dto->customerId,
                'number' => 'ORD-' . strtoupper(Str::random(10)),
                'subtotal' => $calcResult->subtotal,
                'tax_amount' => $calcResult->taxTotal,
                'total' => $calcResult->total,
                'currency' => $calcResult->currency,
                'exchange_rate' => $calcResult->exchangeRate,
                'status' => 'pending',
            ]);

            // 3.1 Apply Coupon & Create Snapshot
            if ($dto->couponCode && $calcResult->discountTotal > 0) {
                $couponService = app(\App\Ecommerce\Coupon\Contracts\CouponServiceInterface::class);
                try {
                    // This safely increments usage count via Atomic Lock
                    $couponService->applyCoupon(
                        $dto->couponCode,
                        $calcRequest->items,
                        (float)$calcResult->subtotal,
                        $dto->customerId
                    );

                    // Save Snapshot
                    \App\Models\OrderCoupon::create([
                        'order_id' => $order->id,
                        'coupon_code' => $dto->couponCode,
                        'discount_amount' => $calcResult->discountTotal,
                    ]);
                } catch (\App\Exceptions\CouponValidationException $e) {
                    // Rollback if applying failed
                    throw new \Exception($e->getMessage());
                }
            }

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
            $order->shippingAddress()->create([
                'type' => 'shipping',
                'first_name' => $dto->shippingAddress['first_name'] ?? '',
                'last_name' => $dto->shippingAddress['last_name'] ?? '',
                'phone' => $dto->shippingAddress['phone'] ?? '',
                'email' => $dto->shippingAddress['email'] ?? '',
                'country_code' => $dto->shippingAddress['country'] ?? 'VN',
                'state_id' => $dto->shippingAddress['state'] ?? null,
                'city_id' => $dto->shippingAddress['city'] ?? null,
                'ward_id' => $dto->shippingAddress['region'] ?? null,
                'address_detail' => $dto->shippingAddress['street'] ?? '',
            ]);
            
            $order->billingAddress()->create([
                'type' => 'billing',
                'first_name' => $dto->billingAddress['first_name'] ?? '',
                'last_name' => $dto->billingAddress['last_name'] ?? '',
                'phone' => $dto->billingAddress['phone'] ?? '',
                'email' => $dto->billingAddress['email'] ?? '',
                'country_code' => $dto->billingAddress['country'] ?? 'VN',
                'state_id' => $dto->billingAddress['state'] ?? null,
                'city_id' => $dto->billingAddress['city'] ?? null,
                'ward_id' => $dto->billingAddress['region'] ?? null,
                'address_detail' => $dto->billingAddress['street'] ?? '',
            ]);

            // 7. Load relations for event listeners
            $order->load('shippingAddress', 'billingAddress');
            
            // 8. Dispatch Event
            event(new OrderCreated($order));

            activity('order')
                ->performedOn($order)
                ->log('Order placed successfully via API');

            return $order;
        });
    }
}
