<?php

namespace App\Ecommerce\Cart\Services;

use App\Ecommerce\Address\DTOs\Address\AddressDTO;
use App\Ecommerce\Cart\Contracts\CartCalculationServiceInterface;
use App\Ecommerce\Checkout\DTOs\CheckoutRequestDTO;
use App\Ecommerce\Checkout\DTOs\CheckoutResultDTO;
use App\Ecommerce\Checkout\Services\CheckoutCalculatorService;
use App\Ecommerce\Product\Contracts\TaxServiceInterface;
use App\Ecommerce\Shipping\Contracts\ShippingServiceInterface;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Settings\CheckoutSettings;

class CartCalculationService implements CartCalculationServiceInterface
{
    public function __construct(
        protected TaxServiceInterface $taxService,
        protected ShippingServiceInterface $shippingService,
        protected CheckoutSettings $checkoutSettings,
        protected CheckoutCalculatorService $calculator
    ) {}

    /**
     * Calculate cart totals using authoritative checkout pipeline.
     */
    public function calculate(
        array $items,
        ?string $country = 'VN',
        ?string $state = null,
        ?string $shippingMethodId = null,
        ?string $couponCode = null
    ): array {
        $itemsForCalc = $this->prepareItemsForCalculation($items, $country, $state);
        $shippingAddress = $this->buildShippingAddress($country, $state);

        $request = new CheckoutRequestDTO(
            items: $itemsForCalc,
            shippingMethod: $shippingMethodId,
            shippingAddress: $shippingAddress,
            couponCode: $couponCode,
            currency: 'VND'
        );

        $calcResult = $this->calculator->calculate($request);

        return $this->formatCalculationResponse($calcResult, $items);
    }

    /**
     * Prepare cart items for pipeline calculation.
     */
    private function prepareItemsForCalculation(array $rawItems, ?string $country, ?string $state): array
    {
        $itemsForCalc = [];
        foreach ($rawItems as $rawItem) {
            $prepared = $this->buildSingleItemForCalc($rawItem, $country, $state);
            if ($prepared !== null) {
                $itemsForCalc[] = $prepared;
            }
        }

        return $itemsForCalc;
    }

    /**
     * Build calculation payload for a single item.
     */
    private function buildSingleItemForCalc(array $item, ?string $country, ?string $state): ?array
    {
        $productId = $item['product_id'] ?? $item['id'] ?? null;
        $variantId = $item['variant_id'] ?? null;

        $product = Product::with(['comboComponents.product', 'comboComponents.variant', 'variants'])->find($productId);
        if (!$product) {
            return null;
        }

        $qty = max(1, (int) ($item['quantity'] ?? $item['qty'] ?? 1));
        [$unitPrice, $taxClassId] = $this->resolvePriceAndTaxClass($product, $variantId);
        $itemTotal = $unitPrice * $qty;

        $taxBreakdown = $product->isCombo()
            ? $this->resolveComboTaxBreakdown($product, $itemTotal, $country, $state)
            : null;

        return [
            'product_id' => $product->id,
            'variant_id' => $variantId,
            'qty' => $qty,
            'unit_price' => $unitPrice,
            'total' => $itemTotal,
            'tax_class_id' => $taxClassId,
            'tax_breakdown' => $taxBreakdown,
        ];
    }

    /**
     * Resolve effective unit price and tax class for simple or variable product.
     *
     * @return array{0: int, 1: ?int}
     */
    private function resolvePriceAndTaxClass(Product $product, ?int $variantId): array
    {
        $unitPrice = (int) $product->effective_price;
        $taxClassId = $product->apply_tax ? $product->tax_class_id : null;

        if ($product->isVariable() && $variantId) {
            $variant = $product->variants->firstWhere('id', $variantId) ?: ProductVariant::find($variantId);
            if ($variant) {
                $unitPrice = (int) $variant->effective_price;
                $taxClassId = $variant->tax_class_id ?? $taxClassId;
            }
        }

        return [$unitPrice, $taxClassId];
    }

    /**
     * Resolve mixed tax breakdown for combo components.
     */
    private function resolveComboTaxBreakdown(Product $combo, int $totalAmount, ?string $country, ?string $state): ?array
    {
        $taxCalc = $this->taxService->calculateProductTax($combo, null, $totalAmount, $country, $state);
        if (empty($taxCalc['breakdown']) || count($taxCalc['breakdown']) <= 1) {
            return null;
        }

        return collect($taxCalc['breakdown'])->map(function (array $b) {
            $cProduct = Product::find($b['product_id']);
            $cVariant = !empty($b['variant_id']) ? ProductVariant::find($b['variant_id']) : null;

            return [
                'name' => $b['name'],
                'tax_class_id' => $cVariant?->tax_class_id ?? $cProduct?->tax_class_id,
                'allocated_amount' => $b['allocated_amount'],
            ];
        })->toArray();
    }

    /**
     * Build standard address DTO for checkout calculation pipeline.
     */
    private function buildShippingAddress(?string $country, ?string $state): AddressDTO
    {
        return new AddressDTO(
            first_name: 'Guest',
            last_name: 'User',
            phone: '',
            email: '',
            country_code: $country ?? 'VN',
            state_id: $state,
            city_id: null,
            ward_id: null,
            address_detail: 'Cart Calculation Destination'
        );
    }

    /**
     * Format calculation result into expected cart response shape.
     */
    private function formatCalculationResponse(CheckoutResultDTO $calcResult, array $originalItems): array
    {
        $discountTotal = $calcResult->discountTotal + $calcResult->loyaltyDiscountTotal;

        return [
            'subtotal' => $calcResult->subtotal,
            'shipping_cost' => $calcResult->shippingTotal,
            'tax_amount' => $calcResult->taxTotal,
            'discount_amount' => $discountTotal,
            'shipping' => [
                'amount' => $calcResult->shippingTotal,
            ],
            'tax' => [
                'amount' => $calcResult->taxTotal,
                'applied_taxes' => $calcResult->appliedTaxes,
            ],
            'discount' => [
                'amount' => $discountTotal,
            ],
            'total' => $calcResult->total,
            'items_count' => collect($originalItems)->sum('quantity'),
        ];
    }

    /**
     * Get available shipping methods for cart.
     */
    public function getAvailableShippingMethods(int $subtotal, ?string $country = 'VN', ?string $state = null): array
    {
        return $this->shippingService->getAvailableMethods($country, $state, null, null, $subtotal)->toArray();
    }
}
