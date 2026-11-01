<?php

namespace App\Ecommerce\Settings\Contracts;

interface FeaturePolicyServiceInterface
{
    /**
     * Check if the e-commerce store operations are enabled.
     */
    public function isEcommerceEnabled(): bool;

    /**
     * Check if the store is in catalog-only mode (browsing allowed, purchasing disabled).
     */
    public function isCatalogMode(): bool;

    /**
     * Check if inventory management and stock tracking are active.
     */
    public function isInventoryTrackingEnabled(): bool;

    /**
     * Check if multi-warehouse stock routing is active.
     */
    public function isMultiWarehouseEnabled(): bool;

    /**
     * Check if tax calculation is enabled.
     */
    public function isTaxCalculationEnabled(): bool;

    /**
     * Check if shipping calculation and methods are enabled.
     */
    public function isShippingEnabled(): bool;

    /**
     * Check if manual shipping (flat rate, weight based, free shipping, local pickup) is enabled.
     */
    public function isManualShippingEnabled(): bool;

    /**
     * Check if carrier API shipping (real-time third-party quotes) is enabled.
     */
    public function isCarrierShippingEnabled(): bool;

    /**
     * Check if coupons and promo codes are enabled.
     */
    public function isCouponsEnabled(): bool;

    /**
     * Check if loyalty points program is enabled.
     */
    public function isLoyaltyEnabled(): bool;

    /**
     * Check if combo products are enabled.
     */
    public function isComboEnabled(): bool;

    /**
     * Check if upsell recommendations are enabled.
     */
    public function isUpsellEnabled(): bool;

    /**
     * Check if cross-sell recommendations are enabled.
     */
    public function isCrossSellEnabled(): bool;

    /**
     * Check if a specific payment gateway is enabled for new checkouts.
     */
    public function isPaymentMethodEnabled(string $method): bool;

    /**
     * Check if customer reviews are enabled.
     */
    public function isReviewsEnabled(): bool;

    /**
     * Check if guest checkout is allowed.
     */
    public function isGuestCheckoutAllowed(): bool;

    /**
     * Get a comprehensive summary of all feature toggle states.
     *
     * @return array<string, bool|mixed>
     */
    public function getFeatureSummary(): array;

    /**
     * Flush cached feature policy states.
     */
    public function flushCache(): void;
}

