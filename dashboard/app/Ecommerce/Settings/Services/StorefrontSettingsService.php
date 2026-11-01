<?php

namespace App\Ecommerce\Settings\Services;

use App\Ecommerce\Settings\Contracts\StorefrontSettingsServiceInterface;
use App\Settings\GeneralSettings;
use App\Settings\ProductSettings;
use App\Settings\CheckoutSettings;
use App\Settings\FooterSettings;
use App\Settings\CustomSettings;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StorefrontSettingsService implements StorefrontSettingsServiceInterface
{
    public function __construct()
    {
        // No dependencies needed - uses Spatie Settings directly
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
                'header' => [
                    'site_name' => $general->store_name,
                    'logo' => $general->logo ? Storage::url($general->logo) : null,
                    'favicon' => $general->favicon ? Storage::url($general->favicon) : null,
                ],
                'products' => [
                    'add_to_cart_behavior' => $product->add_to_cart_behavior,
                    'enable_reviews' => $product->enable_reviews,
                ],
                'marketing' => [
                    'enable_coupons' => (bool) ($marketing->enable_coupons ?? true),
                    'enable_loyalty' => (bool) ($marketing->loyalty_enabled ?? true),
                    'loyalty_enabled' => (bool) ($marketing->loyalty_enabled ?? true),
                    'upsell_enabled' => (bool) ($marketing->upsell_enabled ?? true),
                    'cross_sell_enabled' => (bool) ($marketing->cross_sell_enabled ?? true),
                    'combo_enabled' => (bool) ($marketing->combo_enabled ?? true),
                    'points_per_currency' => $marketing->points_per_currency ?? 1,
                    'point_conversion_rate' => $marketing->point_conversion_rate ?? 1000,
                ],
                'checkout' => [
                    'enable_guest_checkout' => $checkout->enable_guest_checkout,
                    'enabled_payment_gateways' => $checkout->enabled_payment_gateways,
                    'payment_gateways' => $this->getPaymentGatewaysConfig($checkout),
                    'shipping_methods' => ShippingMethod::where('is_enabled', true)->get()->map(fn($m) => [
                        'id' => $m->id,
                        'name' => $this->localizeShippingMethodName($m),
                        'type' => $m->type instanceof \BackedEnum ? $m->type->value : $m->type,
                        'settings' => $m->settings,
                    ]),
                ],
                'footer' => [
                    'copyright' => $footer->copyright,
                    'links' => $footer->links,
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
