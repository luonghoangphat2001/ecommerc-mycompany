<?php

namespace App\Ecommerce\Settings\Services;

use App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface;
use App\Ecommerce\Settings\Contracts\StorefrontSettingsServiceInterface;
use App\Settings\EcommerceSettings;
use App\Settings\GeneralSettings;
use App\Settings\InventorySettings;
use App\Settings\ProductSettings;
use App\Settings\CheckoutSettings;
use App\Settings\FooterSettings;
use App\Settings\CustomSettings;
use App\Models\Menu;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StorefrontSettingsService implements StorefrontSettingsServiceInterface
{
    public function __construct(
        protected ?FeaturePolicyServiceInterface $featurePolicy = null
    ) {
        $this->featurePolicy = $this->featurePolicy ?: app(FeaturePolicyServiceInterface::class);
    }

    public function getStorefrontSettings(): array
    {
        $queryLocale = request()->query('locale') ?? request()->query('lang');
        $xLocale = request()->header('X-Locale');
        $acceptHeader = request()->header('Accept-Language');
        $acceptLocale = is_string($acceptHeader) && trim($acceptHeader) !== ''
            ? substr(strtolower(trim(explode(',', $acceptHeader)[0])), 0, 2)
            : null;

        $locale = null;
        foreach ([$queryLocale, $xLocale, $acceptLocale, app()->getLocale()] as $candidate) {
            if (is_string($candidate) && in_array(strtolower(trim($candidate)), ['vi', 'en'], true)) {
                $locale = strtolower(trim($candidate));
                break;
            }
        }
        $locale = $locale ?: 'vi';

        app()->setLocale($locale);

        return Cache::remember('storefront_settings_v2_' . $locale, 3600, function () use ($locale) {
            /** @var GeneralSettings $general */
            $general = app(GeneralSettings::class);
            /** @var ProductSettings $product */
            $product = app(ProductSettings::class);
            /** @var CheckoutSettings $checkout */
            $checkout = app(CheckoutSettings::class);
            /** @var FooterSettings $footer */
            $footer = app(FooterSettings::class);
            /** @var CustomSettings $custom */
            $custom = app(CustomSettings::class);
            /** @var \App\Settings\MarketingSettings $marketing */
            $marketing = app(\App\Settings\MarketingSettings::class);
            /** @var EcommerceSettings $ecommerce */
            $ecommerce = app(EcommerceSettings::class);
            /** @var InventorySettings $inventory */
            $inventory = app(InventorySettings::class);
            /** @var \App\Settings\LoyaltySettings $loyaltySettings */
            $loyaltySettings = app(\App\Settings\LoyaltySettings::class);

            $policy = $this->featurePolicy ?: app(FeaturePolicyServiceInterface::class);

            $locales = config('app.supported_locales', ['vi', 'en']);
            $languages = [];
            $translations = [];

            foreach ($locales as $localeConfig) {
                $code = is_array($localeConfig) ? $localeConfig['code'] : $localeConfig;
                $name = is_array($localeConfig) ? $localeConfig['name'] : strtoupper($code);

                $languages[] = [
                    'code' => $code,
                    'name' => $name
                ];
                $translations[$code] = trans('storefront', [], $code);
            }

            $currencies = [
                ['code' => 'VND', 'symbol' => '₫', 'name' => 'VND (₫)'],
                ['code' => 'USD', 'symbol' => '$', 'name' => 'USD ($)'],
            ];

            $shippingMethods = $policy->isShippingEnabled()
                ? ShippingMethod::where('is_enabled', true)->get()->map(fn($m) => [
                    'id' => $m->id,
                    'name' => $this->localizeShippingMethodName($m),
                    'type' => $m->type instanceof \BackedEnum ? $m->type->value : $m->type,
                    'settings' => $m->settings,
                ])->values()
                : [];

            $footerMenuHandle = $footer->menu_handle ?: 'footer-menu';
            $footerMenu = Menu::where('handle', $footerMenuHandle)->with('items')->first();
            $footerLinks = ($footerMenu && $footerMenu->items->isNotEmpty())
                ? $footerMenu->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'url' => $item->url,
                        'target' => $item->target ?? '_self',
                        'order' => $item->order ?? 0,
                    ];
                })->values()->all()
                : ($footer->links ?? []);

            return [
                'general' => [
                    'site_name' => $general->store_name,
                    'store_name' => $general->store_name,
                    'store_email' => $general->store_email,
                    'store_phone' => $general->store_phone,
                    'store_country' => $general->store_country,
                    'default_currency' => $general->default_currency,
                    'currency_symbol' => $general->currency_symbol,
                    'currency_position' => $general->currency_position,
                    'thousand_separator' => $general->thousand_separator,
                    'decimal_separator' => $general->decimal_separator,
                    'decimal_places' => $general->decimal_places,
                    'logo' => $general->logo ? Storage::url($general->logo) : null,
                    'favicon' => $general->favicon ? Storage::url($general->favicon) : null,
                    'primary_color' => app(\App\Settings\DBSettings::class)->primary_color,
                    'currencies' => $currencies,
                ],
                'ecommerce' => [
                    'enabled' => $policy->isEcommerceEnabled(),
                    'catalog_mode' => $policy->isCatalogMode(),
                    'tax_enabled' => $policy->isTaxCalculationEnabled(),
                    'shipping_enabled' => $policy->isShippingEnabled(),
                    'inventory_tracking' => $policy->isInventoryTrackingEnabled(),
                    'enable_wishlist' => (bool) ($ecommerce->enable_wishlist ?? true),
                    'enable_compare' => (bool) ($ecommerce->enable_compare ?? false),
                ],
                'header' => [
                    'site_name' => $general->store_name,
                    'logo' => $general->logo ? Storage::url($general->logo) : null,
                    'favicon' => $general->favicon ? Storage::url($general->favicon) : null,
                ],
                'products' => [
                    'add_to_cart_behavior' => $product->add_to_cart_behavior,
                    'enable_reviews' => $policy->isReviewsEnabled(),
                ],
                'inventory' => [
                    'manage_stock' => $policy->isInventoryTrackingEnabled(),
                    'multi_warehouse_enabled' => $policy->isMultiWarehouseEnabled(),
                ],
                'marketing' => [
                    'enable_coupons' => $policy->isCouponsEnabled(),
                    'enable_loyalty' => $policy->isLoyaltyEnabled(),
                    'loyalty_enabled' => $policy->isLoyaltyEnabled(),
                    'upsell_enabled' => $policy->isUpsellEnabled(),
                    'cross_sell_enabled' => $policy->isCrossSellEnabled(),
                    'combo_enabled' => $policy->isComboEnabled(),
                    'points_per_currency' => $loyaltySettings->points_per_currency ?? 1,
                    'point_conversion_rate' => $loyaltySettings->point_conversion_rate ?? 1000,
                    'spending_amount_per_point' => $loyaltySettings->spending_amount_per_point ?? 10000,
                ],
                'loyalty' => [
                    'enabled' => $policy->isLoyaltyEnabled(),
                    'spending_amount_per_point' => $loyaltySettings->spending_amount_per_point ?? 10000,
                    'point_conversion_rate' => $loyaltySettings->point_conversion_rate ?? 1000,
                    'minimum_points_to_redeem' => $loyaltySettings->minimum_points_to_redeem ?? 10,
                    'max_redeem_percentage' => $loyaltySettings->max_redeem_percentage ?? 50,
                    'exclude_sale_items' => (bool) ($loyaltySettings->exclude_sale_items ?? false),
                ],
                'checkout' => [
                    'enable_tax' => $policy->isTaxCalculationEnabled(),
                    'enable_shipping' => $policy->isShippingEnabled(),
                    'enable_guest_checkout' => $policy->isGuestCheckoutAllowed(),
                    'prices_include_tax' => (bool) ($checkout->prices_include_tax ?? false),
                    'enabled_payment_gateways' => $checkout->enabled_payment_gateways ?? ['cod'],
                    'payment_gateways' => $this->getPaymentGatewaysConfig($checkout),
                    'shipping_methods' => $shippingMethods,
                ],
                'footer' => [
                    'copyright' => $footer->copyright,
                    'menu_handle' => $footerMenuHandle,
                    'links' => $footerLinks,
                ],
                'custom' => [
                    'custom_css' => $custom->custom_css,
                    'custom_js' => $custom->custom_js,
                ],
                'currencies' => $currencies,
                'languages' => $languages,
                'translations' => $translations,
                'countries' => config('countries', []),
            ];
        });
    }

    private function localizeShippingMethodName(ShippingMethod $method): string
    {
        $rawName = $method->name;
        if (is_string($rawName) && str_starts_with(trim($rawName), '{')) {
            $decoded = json_decode($rawName, true);
            if (is_array($decoded)) {
                return (string) ($decoded[app()->getLocale()] ?? $decoded['vi'] ?? $decoded['en'] ?? reset($decoded));
            }
        }

        $type = $method->type instanceof \BackedEnum ? $method->type->value : (string) $method->type;
        if ($type === 'flat_rate' && in_array($rawName, ['Flat Rate', 'Giao hàng tiêu chuẩn', 'Standard Shipping'], true)) {
            return __('storefront.checkout.flat_rate_standard');
        }

        return (string) $rawName;
    }

    private function getPaymentGatewaysConfig($checkout): array
    {
        $gateways = [];
        $enabled = $checkout->enabled_payment_gateways ?? ['cod'];

        foreach ($enabled as $gateway) {
            $gateways[] = match ($gateway) {
                'cod' => ['id' => 'cod', 'name' => __('storefront.payments.cod'), 'icon' => 'truck'],
                'bank_transfer' => ['id' => 'bank_transfer', 'name' => __('storefront.payments.bank_transfer'), 'icon' => 'building'],
                'stripe' => ['id' => 'stripe', 'name' => __('storefront.payments.stripe'), 'icon' => 'credit-card', 'public_key' => $checkout->stripe_public_key],
                'paypal' => ['id' => 'paypal', 'name' => __('storefront.payments.paypal'), 'icon' => 'paypal', 'client_id' => $checkout->paypal_client_id, 'mode' => $checkout->paypal_mode],
                'vnpay' => ['id' => 'vnpay', 'name' => 'VNPay', 'icon' => 'building', 'tmn_code' => $checkout->vnpay_tmn_code],
                'momo' => ['id' => 'momo', 'name' => 'Ví MoMo', 'icon' => 'wallet', 'partner_code' => $checkout->momo_partner_code],
                default => ['id' => $gateway, 'name' => ucfirst($gateway), 'icon' => 'credit-card'],
            };
        }

        return $gateways;
    }
}
