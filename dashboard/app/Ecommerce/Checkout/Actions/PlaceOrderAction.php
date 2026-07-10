<?php

namespace App\Ecommerce\Checkout\Actions;

use App\Ecommerce\Order\Contracts\OrderRepositoryInterface;
use App\Ecommerce\Product\Contracts\ProductRepositoryInterface;
use App\Ecommerce\Checkout\DTOs\CheckoutRequestDTO;
use App\Ecommerce\Checkout\DTOs\CheckoutResultDTO;
use App\Ecommerce\Order\DTOs\Order\OrderDataDTO;
use App\Ecommerce\Order\Events\OrderCreated;
use App\Ecommerce\Checkout\Services\CheckoutCalculatorService;
use App\Traits\HandleTransactions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Ecommerce\Address\DTOs\Address\AddressDTO;
use App\Models\Order;
use App\Settings\LoyaltySettings;
use App\Ecommerce\Loyalty\Services\LoyaltyService;
use App\Ecommerce\Inventory\Actions\DeductStockAction;
use App\Ecommerce\Inventory\Actions\CheckStockAction;
use App\Settings\InventorySettings;
use Illuminate\Validation\ValidationException;

class PlaceOrderAction
{
    use HandleTransactions;

    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected ProductRepositoryInterface $productRepository,
        protected CheckoutCalculatorService $calculator,
        protected LoyaltyService $loyaltyService,
        protected LoyaltySettings $loyaltySettings,
        protected DeductStockAction $deductStockAction,
        protected CheckStockAction $checkStockAction,
        protected InventorySettings $inventorySettings
    ) {}

    /**
     * Handle the place order action.
     *
     * @param OrderDataDTO $dto
     * @return Order
     */
    public function execute(OrderDataDTO $dto): Order
    {
        return $this->useTransaction(function () use ($dto) {
            // 1. Prepare items with validation
            $itemsForCalc = $this->prepareItems($dto);

            // 2. Calculate totals
            $calcResult = $this->calculateOrderTotals($dto, $itemsForCalc);

            // 3. Create Order
            $order = $this->createOrder($dto, $calcResult);

            // 4. Apply promotions
            $this->applyCoupon($dto, $order, $calcResult, $itemsForCalc);
            $this->applyLoyaltyRedemption($dto, $order, $calcResult);
            $this->recordLoyaltyAccrual($dto, $order, $calcResult);

            // 5. Create order items and manage inventory
            $this->createProductOrderItems($order, $itemsForCalc);
            $this->createFeeOrderItems($order, $dto, $calcResult);

            // 6. Create addresses
            $this->createOrderAddresses($order, $dto);

            // 7. Finalize order
            return $this->finalizeOrder($order);
        });
    }

    /**
     * Prepare items for calculation with validation.
     */
    private function prepareItems(OrderDataDTO $dto): array
    {
        $itemsForCalc = [];

        foreach ($dto->items as $item) {
            $product = $this->productRepository->find($item['product_id']);
            if (!$product) {
                continue;
            }

            $this->validateProductPrice($item, $product);
            $this->validateProductStock($product, $item['qty']);

            $itemsForCalc[] = [
                'product_id' => $product->id,
                'qty' => $item['qty'],
                'unit_price' => $product->price,
                'total' => $product->price * $item['qty'],
                'tax_class_id' => $product->tax_class_id,
            ];
        }

        return $itemsForCalc;
    }

    /**
     * Validate product price integrity.
     */
    private function validateProductPrice(array $item, $product): void
    {
        if (isset($item['price']) && (float)$item['price'] !== (float)$product->price) {
            \App\Services\Logging\ModuleLogger::order()->warning('price_mismatch', "Price mismatch for product #{$product->id}: Client sent {$item['price']}, DB has {$product->price}", ['product_id' => $product->id, 'client_price' => $item['price'], 'db_price' => $product->price]);
            throw ValidationException::withMessages([
                'order' => [__('messages.price_mismatch')],
            ]);
        }
    }

    /**
     * Validate product stock availability.
     */
    private function validateProductStock($product, int $qty): void
    {
        if ($this->inventorySettings->multi_warehouse_enabled) {
            if (!$this->checkStockAction->execute($product->sku, $qty)) {
                throw ValidationException::withMessages([
                    'order' => [__('messages.insufficient_stock')],
                ]);
            }
        } else {
            if ($product->available_stock < $qty) {
                throw ValidationException::withMessages([
                    'order' => [__('messages.insufficient_stock')],
                ]);
            }
        }
    }

    /**
     * Calculate order totals.
     */
    private function calculateOrderTotals(OrderDataDTO $dto, array $itemsForCalc): CheckoutResultDTO
    {
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

        return $this->calculator->calculate($calcRequest);
    }

    /**
     * Create the main order record.
     */
    private function createOrder(OrderDataDTO $dto, CheckoutResultDTO $calcResult): Order
    {
        return $this->orderRepository->create([
            'user_id' => $dto->customerId,
            'number' => 'ORD-' . strtoupper(Str::random(10)),
            'subtotal' => $calcResult->subtotal,
            'tax_amount' => $calcResult->taxTotal,
            'total' => $calcResult->total,
            'currency' => $calcResult->currency,
            'exchange_rate' => $calcResult->exchangeRate,
            'status' => 'pending',
        ]);
    }

    /**
     * Apply coupon and create snapshot.
     */
    private function applyCoupon(OrderDataDTO $dto, Order $order, CheckoutResultDTO $calcResult, array $itemsForCalc): void
    {
        if (!$dto->couponCode || $calcResult->discountTotal <= 0) {
            return;
        }

        $couponService = app(\App\Ecommerce\Coupon\Contracts\CouponServiceInterface::class);

        try {
            $couponService->applyCoupon(
                $dto->couponCode,
                $itemsForCalc,
                (float)$calcResult->subtotal,
                $dto->customerId
            );

            \App\Models\OrderCoupon::create([
                'order_id' => $order->id,
                'coupon_code' => $dto->couponCode,
                'discount_amount' => $calcResult->discountTotal,
            ]);
        } catch (\App\Exceptions\CouponValidationException $e) {
            throw ValidationException::withMessages([
                'order' => [$e->getMessage()],
            ]);
        }
    }

    /**
     * Apply loyalty point redemption.
     */
    private function applyLoyaltyRedemption(OrderDataDTO $dto, Order $order, CheckoutResultDTO $calcResult): void
    {
        if (!$this->loyaltySettings->enabled || $calcResult->loyaltyDiscountTotal <= 0 || !$dto->customerId) {
            return;
        }

        $redeemedPoints = (int) ($calcResult->loyaltyDiscountTotal / $this->loyaltySettings->point_conversion_rate);

        $this->loyaltyService->adjustPoints(
            $dto->customerId,
            -$redeemedPoints,
            "Redeemed for Order #{$order->id}"
        );
        
        $order->metas()->createMany([
            ['key' => 'redeemed_points', 'value' => (string)$redeemedPoints],
            ['key' => 'loyalty_discount', 'value' => (string)$calcResult->loyaltyDiscountTotal],
        ]);
    }

    /**
     * Record loyalty point accrual.
     */
    private function recordLoyaltyAccrual(OrderDataDTO $dto, Order $order, CheckoutResultDTO $calcResult): void
    {
        if (!$this->loyaltySettings->enabled || !$dto->customerId || $calcResult->total <= 0) {
            return;
        }

        $earnedPoints = (int) (($calcResult->subtotal - $calcResult->discountTotal - $calcResult->loyaltyDiscountTotal) * $this->loyaltySettings->points_per_currency);

        if ($earnedPoints > 0) {
            $this->loyaltyService->awardPoints($dto->customerId, $order->id, $earnedPoints);
        }
    }

    /**
     * Create product order items and deduct inventory.
     */
    private function createProductOrderItems(Order $order, array $itemsForCalc): void
    {
        foreach ($itemsForCalc as $item) {
            $itemMetadata = $this->deductInventoryAndBuildMetadata($item);

            $order->items()->create([
                'type' => 'product',
                'shop_product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'total' => $item['total'],
                'metadata' => $itemMetadata,
            ]);
        }
    }

    /**
     * Deduct inventory and build item metadata.
     */
    private function deductInventoryAndBuildMetadata(array $item): array
    {
        $itemMetadata = [];
        $product = $this->productRepository->find($item['product_id']);

        if (!$product) {
            return $itemMetadata;
        }

        if ($this->inventorySettings->multi_warehouse_enabled) {
            $inventory = $this->findAndDeductFromInventory($product, $item['qty']);
            if ($inventory) {
                $itemMetadata['inventory_id'] = $inventory->id;
                $itemMetadata['inventory_name'] = $inventory->name;
            } else {
                $product->decrement('qty', $item['qty']);
                $product->decrement('total_stock', $item['qty']);
                $itemMetadata['inventory_id'] = null;
                $itemMetadata['inventory_name'] = 'Default Stock';
            }
        } else {
            $product->decrement('qty', $item['qty']);
            $product->decrement('total_stock', $item['qty']);
            $itemMetadata['inventory_id'] = null;
            $itemMetadata['inventory_name'] = 'Default Stock';
        }

        return $itemMetadata;
    }

    /**
     * Find inventory with stock and deduct from it.
     */
    private function findAndDeductFromInventory($product, int $qty): ?\App\Models\Inventory
    {
        if (!$product->inventories) {
            return null;
        }

        $inventory = $product->inventories()
            ->where('is_active', true)
            ->wherePivot('stock_quantity', '>=', $qty)
            ->orderByDesc('shop_product_inventory_stocks.stock_quantity')
            ->first();

        if ($inventory) {
            $this->deductStockAction->execute($product->id, $inventory->id, $qty);
            return $inventory;
        }

        return null;
    }

    /**
     * Create fee order items (shipping, tax, etc.).
     */
    private function createFeeOrderItems(Order $order, OrderDataDTO $dto, CheckoutResultDTO $calcResult): void
    {
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
    }

    /**
     * Create order addresses.
     */
    private function createOrderAddresses(Order $order, OrderDataDTO $dto): void
    {
        $shippingData = [
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
        ];

        $billingData = [
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
        ];

        $order->shippingAddress()->create($shippingData);
        $order->billingAddress()->create($billingData);

        // Auto save to user's address book if authenticated
        if ($order->user_id) {
            $addressBookService = app(\App\Ecommerce\Address\Contracts\AddressBookServiceInterface::class);
            $userAddresses = $addressBookService->listAddresses($order->user_id);
            
            $checkExists = function($data, $type) use ($userAddresses) {
                return $userAddresses->contains(function($addr) use ($data, $type) {
                    return $addr->type === $type 
                        && $addr->phone === $data['phone']
                        && $addr->address_detail === $data['address_detail'];
                });
            };

            if (!$checkExists($shippingData, 'shipping')) {
                $addressBookService->addAddress($order->user_id, $shippingData);
            }
            if (!empty($dto->billingAddress) && !$checkExists($billingData, 'billing')) {
                $addressBookService->addAddress($order->user_id, $billingData);
            }
        }
    }

    /**
     * Finalize order: load relations, dispatch event, log activity.
     */
    private function finalizeOrder(Order $order): Order
    {
        $order->load('shippingAddress', 'billingAddress');

        event(new OrderCreated($order));

        activity('order')
            ->performedOn($order)
            ->log('Order placed successfully via API');

        return $order;
    }
}
