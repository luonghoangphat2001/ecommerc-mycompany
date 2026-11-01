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
use App\Ecommerce\Product\Contracts\TaxServiceInterface;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Settings\LoyaltySettings;
use App\Ecommerce\Loyalty\Services\LoyaltyService;
use App\Ecommerce\Inventory\Actions\DeductStockAction;
use App\Ecommerce\Inventory\Actions\CheckStockAction;
use App\Settings\InventorySettings;
use Illuminate\Validation\ValidationException;
use App\Ecommerce\Order\Enums\OrderStatus;
use App\Enums\OrderItemType;
use App\Enums\AddressType;

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

            // Validate totals match
            $this->validateOrderTotals($dto, $calcResult);

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

            // 6.5. Create shipping snapshot
            $this->createOrderShipping($order, $dto, $calcResult);

            // 7. Finalize order
            return $this->finalizeOrder($order, $dto);
        });
    }

    /**
     * Prepare items for calculation with validation.
     */
    private function prepareItems(OrderDataDTO $dto): array
    {
        $itemsForCalc = [];
        $taxService = app(TaxServiceInterface::class);

        foreach ($dto->items as $item) {
            $product = Product::with(['comboComponents.product', 'comboComponents.variant', 'variants'])->find($item['product_id']);
            if (!$product) {
                continue;
            }

            $variantId = $item['variant_id'] ?? null;
            $qty = max(1, (int) $item['qty']);
            $variant = null;

            if ($product->isVariable()) {
                if (!$variantId) {
                    throw ValidationException::withMessages([
                        'order' => ["Vui lòng chọn phiên bản cho sản phẩm {$product->name}."],
                    ]);
                }

                $variant = $product->variants->firstWhere('id', $variantId) ?: ProductVariant::find($variantId);
                if (!$variant || $variant->product_id != $product->id || !$variant->is_active) {
                    throw ValidationException::withMessages([
                        'order' => ['Phiên bản sản phẩm đã chọn không hợp lệ hoặc đã ngừng bán.'],
                    ]);
                }

                $this->validateVariantPrice($item, $variant);
                $this->validateVariantStock($variant, $qty);

                $effectivePrice = (int) $variant->effective_price;
                $originalPrice = (int) $variant->price;
                $salePrice = $variant->is_on_sale ? (int) $variant->sale_price : null;
                $taxClassId = $variant->tax_class_id ?? ($product->apply_tax ? $product->tax_class_id : null);
                $taxBreakdown = null;
            } elseif ($product->isCombo()) {
                $this->validateComboPrice($item, $product);
                $this->validateComboStock($product, $qty);

                $effectivePrice = (int) $product->effective_price;
                $originalPrice = (int) $product->price;
                $salePrice = $product->is_on_sale ? (int) $product->sale_price : null;
                $taxClassId = $product->apply_tax ? $product->tax_class_id : null;

                $taxCalc = $taxService->calculateProductTax($product, null, $effectivePrice * $qty, $dto->shippingAddress['country'] ?? 'VN', $dto->shippingAddress['state'] ?? null);
                $taxBreakdown = null;
                if (!empty($taxCalc['breakdown']) && count($taxCalc['breakdown']) > 1) {
                    $taxBreakdown = collect($taxCalc['breakdown'])->map(function ($b) {
                        $cProduct = Product::find($b['product_id']);
                        $cVariant = $b['variant_id'] ? ProductVariant::find($b['variant_id']) : null;
                        return [
                            'name' => $b['name'],
                            'tax_class_id' => $cVariant?->tax_class_id ?? $cProduct?->tax_class_id,
                            'allocated_amount' => $b['allocated_amount'],
                        ];
                    })->toArray();
                }
            } else {
                $this->validateProductPrice($item, $product);
                $this->validateProductStock($product, $qty);

                $effectivePrice = (int) $product->effective_price;
                $originalPrice = (int) $product->price;
                $salePrice = $product->is_on_sale ? (int) $product->sale_price : null;
                $taxClassId = $product->apply_tax ? $product->tax_class_id : null;
                $taxBreakdown = null;
            }

            $discountAmount = 0;
            if ($originalPrice > $effectivePrice) {
                $discountAmount = ($originalPrice - $effectivePrice) * $qty;
            }

            $itemsForCalc[] = [
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'qty' => $qty,
                'unit_price' => $effectivePrice,
                'total' => $effectivePrice * $qty,
                'tax_class_id' => $taxClassId,
                'tax_breakdown' => $taxBreakdown,
                'original_price' => $originalPrice,
                'sale_price' => $salePrice,
                'discount_amount' => $discountAmount,
            ];
        }

        return $itemsForCalc;
    }

    private function validateVariantPrice(array $item, ProductVariant $variant): void
    {
        if (isset($item['price']) && (float)$item['price'] !== (float)$variant->effective_price) {
            \App\Services\Logging\ModuleLogger::order()->warning('price_mismatch', "Price mismatch for variant #{$variant->id}: Client sent {$item['price']}, DB has {$variant->effective_price}", ['variant_id' => $variant->id, 'client_price' => $item['price'], 'db_price' => $variant->effective_price]);
            throw ValidationException::withMessages([
                'order' => [__('messages.price_mismatch')],
            ]);
        }
    }

    private function validateVariantStock(ProductVariant $variant, int $qty): void
    {
        if ($variant->available_stock < $qty) {
            throw ValidationException::withMessages([
                'order' => [__('messages.insufficient_stock')],
            ]);
        }
    }

    private function validateComboPrice(array $item, Product $product): void
    {
        if (isset($item['price']) && (float)$item['price'] !== (float)$product->effective_price) {
            \App\Services\Logging\ModuleLogger::order()->warning('price_mismatch', "Price mismatch for combo #{$product->id}: Client sent {$item['price']}, DB has {$product->effective_price}", ['product_id' => $product->id, 'client_price' => $item['price'], 'db_price' => $product->effective_price]);
            throw ValidationException::withMessages([
                'order' => [__('messages.price_mismatch')],
            ]);
        }
    }

    private function validateComboStock(Product $product, int $qty): void
    {
        $comboStock = $product->calculateComboStock();
        if ($comboStock < $qty) {
            throw ValidationException::withMessages([
                'order' => [__('messages.insufficient_stock')],
            ]);
        }
    }

    /**
     * Validate product price integrity.
     */
    private function validateProductPrice(array $item, $product): void
    {
        if (isset($item['price']) && (float)$item['price'] !== (float)$product->effective_price) {
            \App\Services\Logging\ModuleLogger::order()->warning('price_mismatch', "Price mismatch for product #{$product->id}: Client sent {$item['price']}, DB has {$product->effective_price}", ['product_id' => $product->id, 'client_price' => $item['price'], 'db_price' => $product->effective_price]);
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
            address_detail: trim(($dto->shippingAddress['street'] ?? '') . (isset($dto->shippingAddress['address_line_2']) && $dto->shippingAddress['address_line_2'] ? ', ' . $dto->shippingAddress['address_line_2'] : '')),
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
     * Validate order totals from client against backend calculation
     */
    private function validateOrderTotals(OrderDataDTO $dto, CheckoutResultDTO $calcResult): void
    {
        // Allow up to 2 units (e.g. 2 VND) difference for rounding issues
        if (abs($calcResult->total - $dto->grandTotal) > 2) {
            \App\Services\Logging\ModuleLogger::order()->warning('total_mismatch', "Total mismatch for customer: Client sent {$dto->grandTotal}, Backend calculated {$calcResult->total}", ['client_total' => $dto->grandTotal, 'backend_total' => $calcResult->total]);
            throw ValidationException::withMessages([
                'order' => ['Tổng tiền không khớp, vui lòng tải lại trang và thử lại.'],
            ]);
        }
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
            'status' => OrderStatus::Pending->value,
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
                'type' => OrderItemType::Product->value,
                'shop_product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'] ?? null,
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'original_price' => $item['original_price'] ?? $item['unit_price'],
                'sale_price' => $item['sale_price'] ?? null,
                'discount_amount' => $item['discount_amount'] ?? 0,
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
        $product = Product::with(['comboComponents.product', 'comboComponents.variant', 'variants'])->find($item['product_id']);

        if (!$product) {
            return $itemMetadata;
        }

        $imageUrl = $this->extractProductImage($product);
        if ($imageUrl) {
            $itemMetadata['image_url'] = $imageUrl;
        }

        $qty = (int) $item['qty'];

        // 1. Variable Product inventory deduction
        if ($product->isVariable() && !empty($item['variant_id'])) {
            $variant = $product->variants->firstWhere('id', $item['variant_id']) ?: ProductVariant::find($item['variant_id']);
            if ($variant) {
                $variant->decrement('qty', $qty);
                $product->decrement('total_stock', $qty);

                $itemMetadata['product_type'] = 'variable';
                $itemMetadata['variant_id'] = $variant->id;
                $itemMetadata['variant_sku'] = $variant->sku;
                $itemMetadata['attributes'] = $variant->attributes;
                return $itemMetadata;
            }
        }

        // 2. Combo Product inventory deduction from components
        if ($product->isCombo()) {
            $componentSnapshots = [];

            foreach ($product->comboComponents as $component) {
                $componentQty = max(1, (int) $component->quantity);
                $totalDeductQty = $componentQty * $qty;
                $cProduct = $component->product;
                $cVariant = $component->variant;

                if ($cVariant) {
                    $cVariant->decrement('qty', $totalDeductQty);
                    $cProduct?->decrement('total_stock', $totalDeductQty);
                } elseif ($cProduct) {
                    $cProduct->decrement('qty', $totalDeductQty);
                    $cProduct->decrement('total_stock', $totalDeductQty);
                }

                $componentSnapshots[] = [
                    'component_id' => $component->id,
                    'product_id' => $component->shop_product_id,
                    'variant_id' => $component->product_variant_id,
                    'name' => $cProduct?->name ?? 'Component',
                    'qty_per_combo' => $componentQty,
                    'total_qty' => $totalDeductQty,
                    'unit_price' => $component->unit_price,
                    'tax_class_id' => $cVariant?->tax_class_id ?? $cProduct?->tax_class_id,
                ];
            }

            $itemMetadata['product_type'] = 'combo';
            $itemMetadata['combo_components'] = $componentSnapshots;
            return $itemMetadata;
        }

        // 3. Simple Product inventory deduction
        $itemMetadata['product_type'] = 'simple';
        if ($this->inventorySettings->multi_warehouse_enabled) {
            $inventory = $this->findAndDeductFromInventory($product, $qty);
            if ($inventory) {
                $itemMetadata['inventory_id'] = $inventory->id;
                $itemMetadata['inventory_name'] = $inventory->name;
            } else {
                $product->decrement('qty', $qty);
                $product->decrement('total_stock', $qty);
                $itemMetadata['inventory_id'] = null;
                $itemMetadata['inventory_name'] = 'Default Stock';
            }
        } else {
            $product->decrement('qty', $qty);
            $product->decrement('total_stock', $qty);
            $itemMetadata['inventory_id'] = null;
            $itemMetadata['inventory_name'] = 'Default Stock';
        }

        return $itemMetadata;
    }

    /**
     * Extract product image for snapshot.
     */
    private function extractProductImage($product): ?string
    {
        $imageUrl = null;
        
        if (!$product->relationLoaded('featuredImage')) {
            $product->load('featuredImage');
        }
        if ($product->featuredImage) {
            $imageUrl = $product->featuredImage->url;
        }
        
        if (!$imageUrl) {
            if (!$product->relationLoaded('media')) {
                $product->load('media');
            }
            $imageUrl = $product->getFirstMediaUrl('images');
        }
        
        if (!$imageUrl && $product->product_images) {
            $mediaId = is_array($product->product_images) ? ($product->product_images[0] ?? null) : (int)$product->product_images;
            if ($mediaId) {
                $media = \Awcodes\Curator\Models\Media::find($mediaId);
                if ($media) {
                    $imageUrl = $media->url;
                } else {
                    $spatieMedia = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId);
                    if ($spatieMedia) {
                        $imageUrl = $spatieMedia->getUrl();
                    }
                }
            }
        }
        
        return $imageUrl ?: null;
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
                'type' => OrderItemType::Shipping->value,
                'name' => 'Giao hàng: ' . $dto->shippingMethod,
                'qty' => 1,
                'unit_price' => $calcResult->shippingTotal,
                'total' => $calcResult->shippingTotal,
            ]);
        }

        foreach ($calcResult->appliedTaxes as $tax) {
            $order->items()->create([
                'type' => OrderItemType::Tax->value,
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
            'type' => AddressType::Shipping->value,
            'first_name' => $dto->shippingAddress['first_name'] ?? '',
            'last_name' => $dto->shippingAddress['last_name'] ?? '',
            'phone' => $dto->shippingAddress['phone'] ?? '',
            'email' => $dto->shippingAddress['email'] ?? '',
            'country_code' => $dto->shippingAddress['country'] ?? 'VN',
            'state_id' => $dto->shippingAddress['state'] ?? null,
            'city_id' => $dto->shippingAddress['city'] ?? null,
            'ward_id' => $dto->shippingAddress['region'] ?? null,
            'address_detail' => trim(($dto->shippingAddress['street'] ?? '') . (isset($dto->shippingAddress['address_line_2']) && $dto->shippingAddress['address_line_2'] ? ', ' . $dto->shippingAddress['address_line_2'] : '')),
        ];

        $billingData = [
            'type' => AddressType::Billing->value,
            'first_name' => $dto->billingAddress['first_name'] ?? '',
            'last_name' => $dto->billingAddress['last_name'] ?? '',
            'phone' => $dto->billingAddress['phone'] ?? '',
            'email' => $dto->billingAddress['email'] ?? '',
            'country_code' => $dto->billingAddress['country'] ?? 'VN',
            'state_id' => $dto->billingAddress['state'] ?? null,
            'city_id' => $dto->billingAddress['city'] ?? null,
            'ward_id' => $dto->billingAddress['region'] ?? null,
            'address_detail' => trim(($dto->billingAddress['street'] ?? '') . (isset($dto->billingAddress['address_line_2']) && $dto->billingAddress['address_line_2'] ? ', ' . $dto->billingAddress['address_line_2'] : '')),
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

            if (!$checkExists($shippingData, AddressType::Shipping->value)) {
                $addressBookService->addAddress($order->user_id, $shippingData);
            }
            if (!empty($dto->billingAddress) && !$checkExists($billingData, AddressType::Billing->value)) {
                $addressBookService->addAddress($order->user_id, $billingData);
            }
        }
    }

    /**
     * Create order shipping record and snapshot.
     */
    private function createOrderShipping(Order $order, OrderDataDTO $dto, CheckoutResultDTO $calcResult): void
    {
        $shippingMethodModel = null;
        if (is_numeric($dto->shippingMethod)) {
            $shippingMethodModel = \App\Models\ShippingMethod::find((int) $dto->shippingMethod);
        } elseif (!empty($dto->shippingMethod)) {
            $shippingMethodModel = \App\Models\ShippingMethod::where('type', $dto->shippingMethod)
                ->orWhere('name', $dto->shippingMethod)
                ->first();
        }

        $zone = app(\App\Ecommerce\Shipping\Contracts\ShippingServiceInterface::class)->resolveMatchingZone(
            $dto->shippingAddress['country'] ?? 'VN',
            $dto->shippingAddress['state'] ?? null
        );

        $trackingNumber = 'TRK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        $order->shipping()->create([
            'shop_shipping_method_id' => $shippingMethodModel?->id,
            'method' => $shippingMethodModel?->name ?? (string) $dto->shippingMethod,
            'amount' => $calcResult->shippingTotal,
            'tax_amount' => 0,
            'tracking_number' => $trackingNumber,
            'metadata' => [
                'carrier' => $shippingMethodModel?->settings['carrier'] ?? 'standard',
                'zone_id' => $zone?->id,
                'zone_name' => $zone?->name,
                'zone_code' => $zone?->code,
                'estimated_days' => $shippingMethodModel?->settings['estimated_days'] ?? 3,
                'status' => 'pending',
                'status_text' => 'Chờ xử lý và giao cho đơn vị vận chuyển',
                'destination' => [
                    'country' => $dto->shippingAddress['country'] ?? 'VN',
                    'state' => $dto->shippingAddress['state'] ?? null,
                    'city' => $dto->shippingAddress['city'] ?? null,
                    'street' => $dto->shippingAddress['street'] ?? null,
                ],
            ],
        ]);
    }

    /**
     * Finalize order: load relations, dispatch event, log activity.
     */
    private function finalizeOrder(Order $order, OrderDataDTO $dto): Order
    {
        $order->load('shippingAddress', 'billingAddress');

        if ($dto->notes) {
            $order->metas()->create([
                'key' => 'customer_notes',
                'value' => $dto->notes
            ]);
        }

        event(new OrderCreated($order));

        activity('order')
            ->performedOn($order)
            ->log('Order placed successfully via API');

        try {
            app(\App\Ecommerce\Audit\Contracts\FinancialAuditServiceInterface::class)->logOrderFinalized($order, [
                'subtotal' => $order->subtotal,
                'shipping_total' => $order->shipping_total,
                'tax_total' => $order->tax_total,
                'discount_total' => $order->discount_total,
                'total' => $order->total,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        return $order;
    }
}
