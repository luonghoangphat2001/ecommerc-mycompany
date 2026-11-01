<?php

namespace App\Ecommerce\Settings\Services;

use App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface;
use App\Ecommerce\Shipping\Carriers\CarrierManager;
use App\Settings\AdvancedSettings;
use App\Settings\ApiSettings;
use App\Settings\CheckoutSettings;
use App\Settings\CouponSettings;
use App\Settings\EcommerceSettings;
use App\Settings\GeneralSettings;
use App\Settings\InventorySettings;
use App\Settings\LoyaltySettings;
use App\Settings\MarketingSettings;
use App\Settings\ProductSettings;
use Illuminate\Support\Facades\Cache;

class FeaturePolicyService implements FeaturePolicyServiceInterface
{
    private const CACHE_KEY = 'system_feature_policy_summary';
    private const CACHE_TTL = 3600;

    public function __construct(
        protected EcommerceSettings $ecommerceSettings,
        protected CheckoutSettings $checkoutSettings,
        protected InventorySettings $inventorySettings,
        protected MarketingSettings $marketingSettings,
        protected CouponSettings $couponSettings,
        protected LoyaltySettings $loyaltySettings,
        protected ProductSettings $productSettings
    ) {}

    /**
     * @inheritDoc
     */
    public function isEcommerceEnabled(): bool
    {
        return (bool) ($this->ecommerceSettings->enabled ?? true);
    }

    /**
     * @inheritDoc
     */
    public function isCatalogMode(): bool
    {
        if (!$this->isEcommerceEnabled()) {
            return true;
        }

        return (bool) ($this->ecommerceSettings->catalog_mode ?? false);
    }

    /**
     * @inheritDoc
     */
    public function isInventoryTrackingEnabled(): bool
    {
        return (bool) ($this->inventorySettings->manage_stock ?? true);
    }

    /**
     * @inheritDoc
     */
    public function isMultiWarehouseEnabled(): bool
    {
        if (!$this->isInventoryTrackingEnabled()) {
            return false;
        }

        return (bool) ($this->inventorySettings->multi_warehouse_enabled ?? false);
    }

    /**
     * @inheritDoc
     */
    public function isTaxCalculationEnabled(): bool
    {
        $globalTax = (bool) ($this->ecommerceSettings->tax_enabled ?? true);
        $checkoutTax = (bool) ($this->checkoutSettings->enable_tax ?? true);

        return $globalTax && $checkoutTax;
    }

    /**
     * @inheritDoc
     */
    public function isShippingEnabled(): bool
    {
        $globalShipping = (bool) ($this->ecommerceSettings->shipping_enabled ?? true);
        $checkoutShipping = (bool) ($this->checkoutSettings->enable_shipping ?? true);

        return $globalShipping && $checkoutShipping;
    }

    /**
     * @inheritDoc
     */
    public function isManualShippingEnabled(): bool
    {
        return $this->isShippingEnabled();
    }

    /**
     * @inheritDoc
     */
    public function isCarrierShippingEnabled(): bool
    {
        if (!$this->isShippingEnabled()) {
            return false;
        }

        try {
            $carrierManager = app(CarrierManager::class);
            return count($carrierManager->getEnabledCarriers()) > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function isCouponsEnabled(): bool
    {
        $marketing = (bool) ($this->marketingSettings->enable_coupons ?? true);
        $coupon = (bool) ($this->couponSettings->enable_coupons ?? true);

        return $marketing && $coupon;
    }

    /**
     * @inheritDoc
     */
    public function isLoyaltyEnabled(): bool
    {
        $marketing = (bool) ($this->marketingSettings->loyalty_enabled ?? true);
        $loyalty = (bool) ($this->loyaltySettings->enabled ?? true);

        return $marketing && $loyalty;
    }

    /**
     * @inheritDoc
     */
    public function isComboEnabled(): bool
    {
        return (bool) ($this->marketingSettings->combo_enabled ?? true);
    }

    /**
     * @inheritDoc
     */
    public function isUpsellEnabled(): bool
    {
        return (bool) ($this->marketingSettings->upsell_enabled ?? true);
    }

    /**
     * @inheritDoc
     */
    public function isCrossSellEnabled(): bool
    {
        return (bool) ($this->marketingSettings->cross_sell_enabled ?? true);
    }

    /**
     * @inheritDoc
     */
    public function isPaymentMethodEnabled(string $method): bool
    {
        $configured = $this->checkoutSettings->enabled_payment_gateways ?? ['cod'];
        $clean = strtolower(trim($method));

        return in_array($clean, array_map('strtolower', $configured), true);
    }

    /**
     * @inheritDoc
     */
    public function isReviewsEnabled(): bool
    {
        return (bool) ($this->productSettings->enable_reviews ?? true);
    }

    /**
     * @inheritDoc
     */
    public function isGuestCheckoutAllowed(): bool
    {
        return (bool) ($this->checkoutSettings->enable_guest_checkout ?? true);
    }

    /**
     * @inheritDoc
     */
    public function getFeatureSummary(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return [
                'ecommerce_enabled' => $this->isEcommerceEnabled(),
                'catalog_mode' => $this->isCatalogMode(),
                'inventory_tracking' => $this->isInventoryTrackingEnabled(),
                'multi_warehouse' => $this->isMultiWarehouseEnabled(),
                'tax_calculation' => $this->isTaxCalculationEnabled(),
                'shipping_calculation' => $this->isShippingEnabled(),
                'manual_shipping' => $this->isManualShippingEnabled(),
                'carrier_shipping' => $this->isCarrierShippingEnabled(),
                'coupons' => $this->isCouponsEnabled(),
                'loyalty' => $this->isLoyaltyEnabled(),
                'combos' => $this->isComboEnabled(),
                'upsell' => $this->isUpsellEnabled(),
                'cross_sell' => $this->isCrossSellEnabled(),
                'reviews' => $this->isReviewsEnabled(),
                'guest_checkout' => $this->isGuestCheckoutAllowed(),
                'enabled_payment_gateways' => $this->checkoutSettings->enabled_payment_gateways ?? ['cod'],
            ];
        });
    }

    /**
     * @inheritDoc
     */
    public function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}

