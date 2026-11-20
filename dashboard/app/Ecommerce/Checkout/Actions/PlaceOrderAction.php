<?php

namespace App\Ecommerce\Checkout\Actions;

use App\Ecommerce\Address\Contracts\AddressBookServiceInterface;
use App\Ecommerce\Address\DTOs\Address\AddressDTO;
use App\Ecommerce\Audit\Contracts\FinancialAuditServiceInterface;
use App\Ecommerce\Checkout\DTOs\CheckoutRequestDTO;
use App\Ecommerce\Checkout\DTOs\CheckoutResultDTO;
use App\Ecommerce\Checkout\Services\CheckoutCalculatorService;
use App\Ecommerce\Coupon\Contracts\CouponServiceInterface;
use App\Ecommerce\Inventory\Actions\CheckStockAction;
use App\Ecommerce\Inventory\Actions\DeductStockAction;
use App\Ecommerce\Inventory\Contracts\InventoryRepositoryInterface;
use App\Ecommerce\Loyalty\Services\LoyaltyService;
use App\Ecommerce\Order\Contracts\OrderRepositoryInterface;
use App\Ecommerce\Order\DTOs\Order\OrderDataDTO;
use App\Ecommerce\Order\Enums\OrderStatus;
use App\Ecommerce\Order\Events\OrderCreated;
use App\Ecommerce\Product\Contracts\ProductRepositoryInterface;
use App\Ecommerce\Product\Contracts\TaxServiceInterface;
use App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface;
use App\Ecommerce\Shipping\Contracts\ShippingServiceInterface;
use App\Enums\AddressType;
use App\Exceptions\CouponValidationException;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderCoupon;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Logging\ModuleLogger;
use App\Settings\InventorySettings;
use App\Settings\LoyaltySettings;
use App\Support\Media;
use App\Traits\HandleTransactions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
        protected InventoryRepositoryInterface $inventoryRepository,
        protected CheckStockAction $checkStockAction,
        protected InventorySettings $inventorySettings,
        protected TaxServiceInterface $taxService,
        protected CouponServiceInterface $couponService,
        protected AddressBookServiceInterface $addressBookService,
        protected ShippingServiceInterface $shippingService,
        protected FinancialAuditServiceInterface $financialAuditService,
        protected FeaturePolicyServiceInterface $featurePolicy,
    ) {}

    /**
     * Handle the place order action.
     */
    public function execute(OrderDataDTO $dto): Order
    {
        if (! $this->featurePolicy->isEcommerceEnabled()) {
            $this->throwOrderValidation('Cửa hàng hiện đang tạm dừng nhận đơn hàng mới.');
        }

        if ($this->featurePolicy->isCatalogMode()) {
            $this->throwOrderValidation('Hệ thống đang ở chế độ xem danh mục (Catalog Mode), không thể đặt hàng.');
        }

        if ($dto->paymentMethod && ! $this->featurePolicy->isPaymentMethodEnabled($dto->paymentMethod)) {
            $this->throwOrderValidation("Phương thức thanh toán [{$dto->paymentMethod}] hiện không được hỗ trợ.");
        }

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
            $this->recordLoyaltyAccrual($dto, $order, $calcResult, $itemsForCalc);

            // 5. Create order items and manage inventory
            $this->createProductOrderItems($order, $itemsForCalc);
            $this->saveOrderTaxes($order, $calcResult);

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
        foreach ($dto->items as $item) {
            $product = $this->productRepository->findForCheckout((int) $item['product_id']);
            if (! $product) {
                continue;
            }

            $variantId = $item['variant_id'] ?? null;
            $qty = max(1, (int) $item['qty']);
            $variant = null;

            if ($product->isVariable()) {
                if (! $variantId) {
                    $this->throwOrderValidation("Vui lòng chọn phiên bản cho sản phẩm {$product->name}.");
                }

                $variant = $product->variants->firstWhere('id', $variantId)
                    ?: $this->productRepository->findVariantForProduct($variantId, $product->id);
                if (! $variant || $variant->product_id != $product->id || ! $variant->is_active) {
                    $this->throwOrderValidation('Phiên bản sản phẩm đã chọn không hợp lệ hoặc đã ngừng bán.');
                }

                $this->validateSubmittedPrice($item, $variant, 'variant', 'variant_id');
                $this->validateVariantStock($variant, $qty);

                $pricing = $this->resolvePricing($product, $variant);
                $taxClassId = $this->resolveTaxClassId($product, $variant);
                $taxBreakdown = null;
            } elseif ($product->isCombo()) {
                if (! $this->featurePolicy->isComboEnabled()) {
                    $this->throwOrderValidation("Sản phẩm combo {$product->name} hiện đang tạm ngừng hỗ trợ.");
                }

                $this->validateSubmittedPrice($item, $product, 'combo', 'product_id');
                $this->validateComboStock($product, $qty);

                $pricing = $this->resolvePricing($product);
                $taxClassId = $this->resolveTaxClassId($product);

                $taxCalc = $this->taxService->calculateProductTax($product, null, $pricing['effective_price'] * $qty, $dto->shippingAddress['country'] ?? 'VN', $dto->shippingAddress['state'] ?? null);
                $taxBreakdown = null;
                if (! empty($taxCalc['breakdown']) && count($taxCalc['breakdown']) > 1) {
                    $taxBreakdown = collect($taxCalc['breakdown'])->map(function ($b) {
                        $cProduct = $this->productRepository->find((int) $b['product_id']);
                        $cVariant = $b['variant_id']
                            ? $this->productRepository->findVariantForProduct((int) $b['variant_id'], (int) $b['product_id'], false)
                            : null;

                        return [
                            'name' => $b['name'],
                            'tax_class_id' => $cVariant?->tax_class_id ?? $cProduct?->tax_class_id,
                            'allocated_amount' => $b['allocated_amount'],
                        ];
                    })->toArray();
                }
            } else {
                $this->validateSubmittedPrice($item, $product, 'product', 'product_id');
                $this->validateProductStock($product, $qty);

                $pricing = $this->resolvePricing($product);
                $taxClassId = $this->resolveTaxClassId($product);
                $taxBreakdown = null;
            }

            $effectivePrice = $pricing['effective_price'];
            $originalPrice = $pricing['original_price'];
            $salePrice = $pricing['sale_price'];

            $discountAmount = 0;
            if ($originalPrice > $effectivePrice) {
                $discountAmount = ($originalPrice - $effectivePrice) * $qty;
            }

            $itemsForCalc[] = [
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'name' => $product->name,
                'sku' => $variant?->sku ?? $product->sku,
                'variant_attributes' => $variant?->attributes,
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

    private function resolveTaxClassId(Product $product, ?ProductVariant $variant = null): ?int
    {
        return $variant?->tax_class_id ?? ($product->apply_tax ? $product->tax_class_id : null);
    }

    private function resolvePricing(Product $product, ?ProductVariant $variant = null): array
    {
        $source = $variant ?? $product;

        return [
            'effective_price' => (int) $source->effective_price,
            'original_price' => (int) $source->price,
            'sale_price' => $source->is_on_sale ? (int) $source->sale_price : null,
        ];
    }

    private function validateSubmittedPrice(array $item, Product | ProductVariant $model, string $entity, string $idKey): void
    {
        if (! isset($item['price']) || (float) $item['price'] === (float) $model->effective_price) {
            return;
        }

        ModuleLogger::order()->warning(
            'price_mismatch',
            "Price mismatch for {$entity} #{$model->id}: Client sent {$item['price']}, DB has {$model->effective_price}",
            [$idKey => $model->id, 'client_price' => $item['price'], 'db_price' => $model->effective_price]
        );

        $this->throwOrderValidation(__('messages.price_mismatch'));
    }

    private function validateVariantStock(ProductVariant $variant, int $qty): void
    {
        if (! $this->inventoryTrackingEnabled()) {
            return;
        }

        if ($variant->available_stock < $qty) {
            $this->throwInsufficientStock();
        }
    }

    private function validateComboStock(Product $product, int $qty): void
    {
        if (! $this->inventoryTrackingEnabled()) {
            return;
        }

        $comboStock = $product->calculateComboStock();
        if ($comboStock < $qty) {
            $this->throwInsufficientStock();
        }
    }

    /**
     * Validate product stock availability.
     */
    private function validateProductStock(Product $product, int $qty): void
    {
        if (! $this->inventoryTrackingEnabled()) {
            return;
        }

        if ($this->inventorySettings->multi_warehouse_enabled) {
            if (! $this->checkStockAction->execute($product->sku, $qty)) {
                $this->throwInsufficientStock();
            }
        } else {
            if ($product->available_stock < $qty) {
                $this->throwInsufficientStock();
            }
        }
    }

    private function inventoryTrackingEnabled(): bool
    {
        return $this->featurePolicy->isInventoryTrackingEnabled();
    }

    private function throwInsufficientStock(): never
    {
        $this->throwOrderValidation(__('messages.insufficient_stock'));
    }

    private function throwOrderValidation(string $message): never
    {
        throw ValidationException::withMessages(['order' => [$message]]);
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
            ModuleLogger::order()->warning('total_mismatch', "Total mismatch for customer: Client sent {$dto->grandTotal}, Backend calculated {$calcResult->total}", ['client_total' => $dto->grandTotal, 'backend_total' => $calcResult->total]);

            $this->throwOrderValidation('Tổng tiền không khớp, vui lòng tải lại trang và thử lại.');
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
            'discount_total' => $calcResult->discountTotal,
            'loyalty_discount_total' => $calcResult->loyaltyDiscountTotal,
            'shipping_total' => $calcResult->shippingTotal,
            'redeemed_points' => 0,
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
        if (! $dto->couponCode || $calcResult->discountTotal <= 0) {
            return;
        }

        try {
            $this->couponService->applyCoupon(
                $dto->couponCode,
                $itemsForCalc,
                (float) $calcResult->subtotal,
                $dto->customerId
            );

            OrderCoupon::create([
                'order_id' => $order->id,
                'coupon_code' => $dto->couponCode,
                'discount_amount' => $calcResult->discountTotal,
            ]);
        } catch (CouponValidationException $e) {
            $this->throwOrderValidation($e->getMessage());
        }
    }

    /**
     * Apply loyalty point redemption.
     */
    private function applyLoyaltyRedemption(OrderDataDTO $dto, Order $order, CheckoutResultDTO $calcResult): void
    {
        if (! $this->loyaltySettings->enabled || $calcResult->loyaltyDiscountTotal <= 0 || ! $dto->customerId) {
            return;
        }

        $conversionRate = $this->loyaltySettings->conversionRate();
        $redeemedPoints = (int) ceil($calcResult->loyaltyDiscountTotal / $conversionRate);

        $this->loyaltyService->adjustPoints(
            $dto->customerId,
            -$redeemedPoints,
            "Redeemed for Order #{$order->id}"
        );

        $order->update(['redeemed_points' => $redeemedPoints]);
    }

    /**
     * Record loyalty point accrual.
     */
    private function recordLoyaltyAccrual(OrderDataDTO $dto, Order $order, CheckoutResultDTO $calcResult, array $itemsForCalc = []): void
    {
        if (! $this->loyaltySettings->enabled || ! $dto->customerId || $calcResult->total <= 0) {
            return;
        }

        $earnedPoints = 0;
        $eligibleAmount = 0;

        $productIds = collect($itemsForCalc)->pluck('product_id')->filter()->unique()->all();
        $products = ! empty($productIds)
            ? $this->productRepository->findByIds($productIds, ['categories'])->keyBy('id')
            : collect();

        $excludedProductIds = array_map('intval', $this->loyaltySettings->excluded_product_ids ?? []);
        $excludedCategoryIds = array_map('intval', $this->loyaltySettings->excluded_category_ids ?? []);
        $excludeSaleItems = (bool) ($this->loyaltySettings->exclude_sale_items ?? false);

        foreach ($itemsForCalc as $item) {
            $productId = $item['product_id'] ?? null;
            $product = $products->get($productId);

            if (! $product) {
                continue;
            }

            // Check if product is explicitly excluded from points
            if ($product->is_loyalty_earnable === false) {
                continue;
            }

            // Check global excluded products
            if (in_array((int) $productId, $excludedProductIds, true)) {
                continue;
            }

            // Check global excluded categories
            if (! empty($excludedCategoryIds)) {
                $categoryIds = $product->categories->pluck('id')->all();
                if (! empty(array_intersect($categoryIds, $excludedCategoryIds))) {
                    continue;
                }
            }

            // Check if sale items are excluded
            if ($excludeSaleItems && $product->is_on_sale) {
                continue;
            }

            $qty = (int) ($item['qty'] ?? 1);

            // If product has custom fixed bonus reward points
            if ($product->loyalty_points_reward && $product->loyalty_points_reward > 0) {
                $earnedPoints += (int) ($product->loyalty_points_reward * $qty);
            } else {
                $itemTotal = (float) ($item['total'] ?? (($item['unit_price'] ?? 0) * $qty));
                $eligibleAmount += $itemTotal;
            }
        }

        // Calculate points from eligible spending amount
        $spendingRate = $this->loyaltySettings->spendingRate();
        if ($spendingRate > 0) {
            $earnedPoints += (int) floor($eligibleAmount / $spendingRate);
        } else {
            // Fallback to legacy points_per_currency
            $multiplier = (float) ($this->loyaltySettings->points_per_currency ?? 1);
            $earnedPoints += (int) round($eligibleAmount * $multiplier);
        }

        if ($earnedPoints > 0) {
            $this->loyaltyService->awardPoints($dto->customerId, $order->id, $earnedPoints);
            $order->update(['earned_points' => $earnedPoints]);
        }
    }

    /**
     * Create product order items and deduct inventory.
     */
    private function createProductOrderItems(Order $order, array $itemsForCalc): void
    {
        foreach ($itemsForCalc as $item) {
            /** @var OrderItem $orderItem */
            $orderItem = $order->items()->create([
                'shop_product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'] ?? null,
                'name' => $item['name'],
                'sku' => $item['sku'],
                'variant_attributes' => $item['variant_attributes'],
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'original_price' => $item['original_price'] ?? $item['unit_price'],
                'sale_price' => $item['sale_price'] ?? null,
                'discount_amount' => $item['discount_amount'] ?? 0,
                'total' => $item['total'],
                'metadata' => [],
            ]);

            $orderItem->update([
                'metadata' => $this->deductInventoryAndBuildMetadata($item, $orderItem),
            ]);
        }
    }

    /**
     * Deduct inventory and build item metadata.
     */
    private function deductInventoryAndBuildMetadata(array $item, OrderItem $orderItem): array
    {
        $itemMetadata = [];
        $product = $this->productRepository->findForCheckout((int) $item['product_id']);

        if (! $product) {
            return $itemMetadata;
        }

        $imageUrl = Media::productImage($product);
        if ($imageUrl) {
            $itemMetadata['image_url'] = $imageUrl;
        }

        $qty = (int) $item['qty'];
        $trackStock = $this->inventoryTrackingEnabled();

        // 1. Variable Product inventory deduction
        if ($product->isVariable() && ! empty($item['variant_id'])) {
            $variant = $product->variants->firstWhere('id', $item['variant_id'])
                ?: $this->productRepository->findVariantForProduct((int) $item['variant_id'], $product->id);
            if ($variant) {
                if ($trackStock) {
                    $allocated = $this->inventorySettings->multi_warehouse_enabled
                        ? $this->deductFromInventorySource($product, $qty, $orderItem, $variant->id)
                        : null;
                    if (! $allocated) {
                        $this->decrementVariantStock($variant, $product, $qty);
                    }
                }

                $itemMetadata['product_type'] = 'variable';
                $itemMetadata['variant_id'] = $variant->id;
                $itemMetadata['variant_sku'] = $variant->sku;
                $itemMetadata['attributes'] = $variant->attributes;
                if ($allocated) {
                    $itemMetadata['stock_source'] = $allocated;
                }

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
                $allocation = null;

                if ($trackStock) {
                    if ($cVariant) {
                        $allocation = $this->inventorySettings->multi_warehouse_enabled
                            ? $this->deductFromInventorySource($cProduct, $totalDeductQty, $orderItem, $cVariant->id)
                            : null;
                        if (! $allocation) {
                            $this->decrementVariantStock($cVariant, $cProduct, $totalDeductQty);
                        }
                    } elseif ($cProduct) {
                        $allocation = $this->inventorySettings->multi_warehouse_enabled
                            ? $this->deductFromInventorySource($cProduct, $totalDeductQty, $orderItem)
                            : null;
                        if (! $allocation) {
                            $this->decrementProductStock($cProduct, $totalDeductQty);
                        }
                    }
                }

                $componentSnapshot = [
                    'component_id' => $component->id,
                    'product_id' => $component->shop_product_id,
                    'variant_id' => $component->product_variant_id,
                    'name' => $cProduct?->name ?? 'Component',
                    'qty_per_combo' => $componentQty,
                    'total_qty' => $totalDeductQty,
                    'unit_price' => $component->unit_price,
                    'tax_class_id' => $cVariant?->tax_class_id ?? $cProduct?->tax_class_id,
                ];
                if ($allocation) {
                    $componentSnapshot['stock_source'] = $allocation;
                }
                $componentSnapshots[] = $componentSnapshot;
            }

            $itemMetadata['product_type'] = 'combo';
            $itemMetadata['combo_components'] = $componentSnapshots;

            return $itemMetadata;
        }

        // 3. Simple Product inventory deduction
        $itemMetadata['product_type'] = 'simple';
        if ($trackStock) {
            if ($this->inventorySettings->multi_warehouse_enabled) {
                $allocation = $this->findAndDeductFromInventory($product, $qty, $orderItem);
                if ($allocation) {
                    $itemMetadata['inventory_id'] = $allocation['inventory_id'];
                    $itemMetadata['inventory_name'] = $allocation['inventory_name'];
                    $itemMetadata['stock_source'] = $allocation;
                } else {
                    $this->decrementProductStock($product, $qty);
                    $itemMetadata['inventory_id'] = null;
                    $itemMetadata['inventory_name'] = 'Default Stock';
                }
            } else {
                $this->decrementProductStock($product, $qty);
                $itemMetadata['inventory_id'] = null;
                $itemMetadata['inventory_name'] = 'Default Stock';
            }
        } else {
            $itemMetadata['inventory_id'] = null;
            $itemMetadata['inventory_name'] = 'Stock Tracking Disabled';
        }

        return $itemMetadata;
    }

    private function decrementVariantStock(ProductVariant $variant, ?Product $product, int $quantity): void
    {
        $variant->decrement('qty', $quantity);
        $product?->decrement('total_stock', $quantity);
    }

    private function decrementProductStock(Product $product, int $quantity): void
    {
        $product->decrement('qty', $quantity);
        $product->decrement('total_stock', $quantity);
    }

    /**
     * Find inventory with stock and deduct from it.
     */
    private function deductFromInventorySource($product, int $qty, OrderItem $orderItem, ?int $variantId = null): ?array
    {
        return $this->findAndDeductFromInventory($product, $qty, $orderItem, $variantId);
    }

    private function findAndDeductFromInventory($product, int $qty, OrderItem $orderItem, ?int $variantId = null): ?array
    {
        $relation = $variantId
            ? $this->productRepository->findVariantById($variantId)?->inventories()
            : $product->inventories();
        if (! $relation) {
            return null;
        }

        $inventory = $relation
            ->where('is_active', true)
            ->wherePivot('stock_quantity', '>=', $qty)
            ->orderByDesc('shop_product_inventory_stocks.stock_quantity')
            ->first();

        if ($inventory) {
            $before = $this->inventoryRepository->getStock($product->id, $inventory->id);
            $this->deductStockAction->execute($product->id, $inventory->id, $qty, $variantId);
            $after = $this->inventoryRepository->getStock($product->id, $inventory->id);
            $movementId = $this->inventoryRepository->recordMovement([
                'shop_product_id' => $product->id,
                'warehouse_id' => $inventory->id,
                'variant_id' => $variantId,
                'movement_type' => 'order_stock_deduction',
                'source_type' => \App\Models\OrderItem::class,
                'source_id' => $orderItem->id,
                'prev_stock' => $before,
                'quantity_changed' => -$qty,
                'new_stock' => $after,
            ]);

            return [
                'inventory_id' => $inventory->id,
                'inventory_name' => $inventory->name,
                'warehouse_id' => $inventory->id,
                'variant_id' => $variantId,
                'quantity' => $qty,
                'movement_reference' => "order_item:{$orderItem->id}:inventory:{$inventory->id}",
                'movement_recorded' => $movementId,
            ];
        }

        return null;
    }

    /**
     * Save calculated tax rows in the order tax table.
     */
    private function saveOrderTaxes(Order $order, CheckoutResultDTO $calcResult): void
    {
        foreach ($calcResult->appliedTaxes as $tax) {
            $order->taxes()->create([
                'name' => $tax['name'],
                'amount' => (int) $tax['amount'],
                'is_shipping' => false,
                'metadata' => ['rate' => $tax['rate'] ?? null],
            ]);
        }
    }

    /**
     * Create order addresses.
     */
    private function createOrderAddresses(Order $order, OrderDataDTO $dto): void
    {
        $shippingData = $this->buildAddressPayload($dto->shippingAddress, AddressType::Shipping->value);
        $billingData = $this->buildAddressPayload($dto->billingAddress, AddressType::Billing->value);

        $order->shippingAddress()->create($shippingData);
        $order->billingAddress()->create($billingData);

        // Auto save to user's address book if authenticated
        if ($order->user_id) {
            $userAddresses = $this->addressBookService->listAddresses($order->user_id);

            $checkExists = function ($data, $type) use ($userAddresses) {
                return $userAddresses->contains(function ($addr) use ($data, $type) {
                    return $addr->type === $type
                        && $addr->phone === $data['phone']
                        && $addr->address_detail === $data['address_detail'];
                });
            };

            if (! $checkExists($shippingData, AddressType::Shipping->value)) {
                $this->addressBookService->addAddress($order->user_id, $shippingData);
            }
            if (! empty($dto->billingAddress) && ! $checkExists($billingData, AddressType::Billing->value)) {
                $this->addressBookService->addAddress($order->user_id, $billingData);
            }
        }
    }

    private function buildAddressPayload(array $address, string $type): array
    {
        return [
            'type' => $type,
            'first_name' => $address['first_name'] ?? '',
            'last_name' => $address['last_name'] ?? '',
            'phone' => $address['phone'] ?? '',
            'email' => $address['email'] ?? '',
            'country_code' => $address['country'] ?? 'VN',
            'state_id' => $address['state'] ?? null,
            'city_id' => $address['city'] ?? null,
            'ward_id' => $address['region'] ?? null,
            'address_detail' => trim(($address['street'] ?? '') . (! empty($address['address_line_2']) ? ', ' . $address['address_line_2'] : '')),
        ];
    }

    /**
     * Create order shipping record and snapshot.
     */
    private function createOrderShipping(Order $order, OrderDataDTO $dto, CheckoutResultDTO $calcResult): void
    {
        if (! $this->featurePolicy->isShippingEnabled()) {
            return;
        }

        $shippingMethodModel = $this->shippingService->resolveMethod($dto->shippingMethod);

        $zone = $this->shippingService->resolveMatchingZone(
            $dto->shippingAddress['country'] ?? 'VN',
            $dto->shippingAddress['state'] ?? null
        );

        $trackingNumber = 'TRK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        $order->shipping()->create([
            'shop_shipping_method_id' => $shippingMethodModel?->id,
            'method' => $shippingMethodModel?->name ?? (string) $dto->shippingMethod,
            'amount' => $calcResult->shippingTotal,
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
            $order->update(['customer_notes' => $dto->notes]);
        }

        event(new OrderCreated($order));

        activity('order')
            ->performedOn($order)
            ->log('Order placed successfully via API');

        try {
            $this->financialAuditService->logOrderFinalized($order, [
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
