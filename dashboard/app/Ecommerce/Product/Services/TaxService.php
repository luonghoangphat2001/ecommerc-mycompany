<?php

namespace App\Ecommerce\Product\Services;

use App\Ecommerce\Product\Contracts\TaxClassRepositoryInterface;
use App\Ecommerce\Product\Contracts\TaxRateRepositoryInterface;
use App\Ecommerce\Product\Contracts\TaxServiceInterface;
use App\Ecommerce\Product\DTOs\TaxCalculationDTO;
use App\Enums\TaxRateStatus;
use App\Models\ComboProductItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\TaxClass;
use App\Settings\CheckoutSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TaxService implements TaxServiceInterface
{
    public function __construct(
        protected TaxRateRepositoryInterface $taxRateRepository,
        protected TaxClassRepositoryInterface $taxClassRepository
    ) {}

    /**
     * Calculate tax for a given amount and tax class.
     *
     * @param TaxClass|int|string|null $taxClass
     * @param int $amount
     * @param string|null $country
     * @param string|null $state
     * @param string|null $city
     * @return array{amount: int, status: string, rate_name: string, rate_percent: float, tax_rate_id: ?int}
     */
    public function calculate($taxClass, int $amount, ?string $country = null, ?string $state = null, ?string $city = null): array
    {
        $policy = app(\App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface::class);

        if (!$policy->isTaxCalculationEnabled()) {
            return $this->buildTaxResult(0, TaxRateStatus::TaxDisabled, 0.0);
        }

        $taxClassId = $this->resolveTaxClassId($taxClass);
        if (!$taxClassId || $amount <= 0) {
            return $this->buildTaxResult(0, TaxRateStatus::NoTax, 0.0);
        }

        $rate = $this->taxRateRepository->findMatchingRate($taxClassId, $country, $state, $city);
        if (!$rate) {
            return $this->buildTaxResult(0, TaxRateStatus::NoTax, 0.0);
        }

        $ratePercent = (float) $rate->rate;
        $includeTax = (bool) ($checkoutSettings->prices_include_tax ?? false);
        $taxAmount = $this->computeTaxFromAmount($amount, $ratePercent, $includeTax);

        return [
            'amount' => $taxAmount,
            'status' => TaxRateStatus::Applied->value,
            'rate_name' => $rate->name,
            'rate_percent' => $ratePercent,
            'tax_rate_id' => $rate->id,
        ];
    }

    /**
     * Authoritatively calculate tax for a product (Simple, Variable, or Combo).
     */
    public function calculateProductTax(
        Product|int $product,
        ?int $variantId,
        int $amount,
        ?string $country = null,
        ?string $state = null,
        ?string $city = null
    ): array {
        $productModel = is_numeric($product)
            ? Product::with(['comboComponents.product', 'comboComponents.variant', 'variants'])->find($product)
            : $product;

        if (!$productModel) {
            return $this->buildTaxResult(0, TaxRateStatus::ProductNotFound, 0.0, null, []);
        }

        $policy = app(\App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface::class);
        if (!$policy->isTaxCalculationEnabled()) {
            return $this->buildTaxResult(0, TaxRateStatus::TaxDisabled, 0.0, null, []);
        }
        if (!$productModel->apply_tax) {
            return $this->buildTaxResult(0, TaxRateStatus::NonTaxable, 0.0, null, []);
        }

        if ($productModel->isCombo()) {
            return $this->calculateComboTax($productModel, $amount, $country, $state, $city);
        }

        if ($productModel->isVariable() && $variantId) {
            $variant = $productModel->variants->firstWhere('id', $variantId) ?: ProductVariant::find($variantId);
            $taxClassId = $variant?->tax_class_id ?? $productModel->tax_class_id;
            $res = $this->calculate($taxClassId, $amount, $country, $state, $city);
            $res['breakdown'] = [];
            return $res;
        }

        $res = $this->calculate($productModel->tax_class_id, $amount, $country, $state, $city);
        $res['breakdown'] = [];
        return $res;
    }

    /**
     * Calculate tax for combo product by allocating across components when tax classes differ.
     */
    protected function calculateComboTax(
        Product $combo,
        int $amount,
        ?string $country = null,
        ?string $state = null,
        ?string $city = null
    ): array {
        $components = $combo->comboComponents;
        if ($components->isEmpty()) {
            $res = $this->calculate($combo->tax_class_id, $amount, $country, $state, $city);
            $res['breakdown'] = [];
            return $res;
        }

        $nominalTotal = $this->calculateNominalTotal($components, $amount);
        $totalTax = 0;
        $breakdown = [];
        $allocatedSoFar = 0;
        $count = $components->count();

        foreach ($components as $index => $item) {
            $isLast = ($index === $count - 1);
            $allocatedAmount = $this->allocateProportionalPrice(
                $item->subtotal,
                $nominalTotal,
                $amount,
                $allocatedSoFar,
                $isLast
            );

            $partData = $this->processComponentTax(
                $item,
                $allocatedAmount,
                $country,
                $state,
                $city,
                (int) $combo->tax_class_id
            );

            $totalTax += $partData['tax_amount'];
            $breakdown[] = $partData;
        }

        $hasMultipleBreakdowns = count($breakdown) > 1;
        $rateStatus = $hasMultipleBreakdowns ? TaxRateStatus::AllocatedComboTax : TaxRateStatus::ComboTax;
        $rateName = $hasMultipleBreakdowns ? $rateStatus->label() : ($breakdown[0]['rate_name'] ?? $rateStatus->label());

        return [
            'amount' => $totalTax,
            'status' => $rateStatus->value,
            'rate_name' => $rateName,
            'rate_percent' => $amount > 0 ? round(($totalTax / $amount) * 100, 2) : 0.0,
            'tax_rate_id' => null,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Calculate nominal sum of all components in combo.
     */
    private function calculateNominalTotal(Collection $components, int $fallbackAmount): int
    {
        $sum = (int) $components->sum(fn(ComboProductItem $item) => $item->subtotal);
        return $sum > 0 ? $sum : max(1, $fallbackAmount);
    }

    /**
     * Allocate combo selling price proportionally to component nominal value.
     */
    private function allocateProportionalPrice(
        int $itemNominal,
        int $nominalTotal,
        int $totalAmount,
        int &$allocatedSoFar,
        bool $isLast
    ): int {
        if ($isLast) {
            return max(0, $totalAmount - $allocatedSoFar);
        }

        $allocated = (int) round(($itemNominal / $nominalTotal) * $totalAmount);
        $allocatedSoFar += $allocated;
        return $allocated;
    }

    /**
     * Process tax calculation for an individual combo component.
     */
    private function processComponentTax(
        ComboProductItem $item,
        int $allocatedAmount,
        ?string $country,
        ?string $state,
        ?string $city,
        int $fallbackTaxClassId
    ): array {
        $itemProduct = $item->product;
        $itemVariant = $item->variant;
        $appliesTax = $itemProduct ? (bool) $itemProduct->apply_tax : true;
        $taxClassId = $itemVariant?->tax_class_id ?? $itemProduct?->tax_class_id ?? $fallbackTaxClassId;

        if ($appliesTax && $taxClassId) {
            $calc = $this->calculate($taxClassId, $allocatedAmount, $country, $state, $city);
            return [
                'component_id' => $item->id,
                'product_id' => $item->shop_product_id,
                'variant_id' => $item->product_variant_id,
                'name' => $itemProduct?->name ?? 'Component',
                'allocated_amount' => $allocatedAmount,
                'tax_amount' => $calc['amount'],
                'rate_name' => $calc['rate_name'],
                'rate_percent' => $calc['rate_percent'],
            ];
        }

        return [
            'component_id' => $item->id,
            'product_id' => $item->shop_product_id,
            'variant_id' => $item->product_variant_id,
            'name' => $itemProduct?->name ?? 'Component',
            'allocated_amount' => $allocatedAmount,
            'tax_amount' => 0,
            'rate_name' => TaxRateStatus::NonTaxable->label(),
            'rate_percent' => 0.0,
        ];
    }

    /**
     * Build standard tax result structure.
     */
    private function buildTaxResult(
        int $amount,
        TaxRateStatus $status,
        float $ratePercent,
        ?int $taxRateId = null,
        ?array $breakdown = null
    ): array {
        $data = [
            'amount' => $amount,
            'status' => $status->value,
            'rate_name' => $status->label(),
            'rate_percent' => $ratePercent,
            'tax_rate_id' => $taxRateId,
        ];

        if ($breakdown !== null) {
            $data['breakdown'] = $breakdown;
        }

        return $data;
    }

    /**
     * Compute tax from amount based on tax inclusive or exclusive settings.
     */
    private function computeTaxFromAmount(int $amount, float $ratePercent, bool $includeTax): int
    {
        if ($includeTax) {
            return (int) round($amount - ($amount / (1 + ($ratePercent / 100))));
        }

        return (int) round($amount * ($ratePercent / 100));
    }

    protected function resolveTaxClassId($taxClass): ?int
    {
        if ($taxClass instanceof TaxClass) {
            return $taxClass->id;
        }

        if (is_numeric($taxClass)) {
            return (int) $taxClass;
        }

        if (is_string($taxClass)) {
            $found = TaxClass::where('slug', $taxClass)->orWhere('name', $taxClass)->value('id');
            return $found ? (int) $found : null;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getTaxClassTableQuery(): Builder
    {
        return $this->taxClassRepository->query();
    }
}
