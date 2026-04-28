<?php

namespace App\Ecommerce\Settings\Services;

use App\Ecommerce\Settings\Contracts\StorefrontSettingsServiceInterface;
use App\Ecommerce\Settings\Contracts\SettingServiceInterface;
use App\Settings\GeneralSettings;
use App\Settings\ProductSettings;
use App\Settings\CheckoutSettings;
use App\Settings\FooterSettings;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StorefrontSettingsService implements StorefrontSettingsServiceInterface
{
    protected SettingServiceInterface $settingService;

    public function __construct(SettingServiceInterface $settingService)
    {
        $this->settingService = $settingService;
    }

    public function getStorefrontSettings(): array
    {
        $locale = request()->query('lang') ?? request()->header('Accept-Language') ?? app()->getLocale();
        if (!in_array($locale, ['vi', 'en'])) {
            $locale = 'vi';
        }
        
        app()->setLocale($locale);

        activity('system')
            ->log('Storefront settings fetched via API. Locale: ' . $locale);
            
        return Cache::remember('storefront_settings_v1_' . $locale, 3600, function () use ($locale) {
            /** @var GeneralSettings $general */
            $general = app(GeneralSettings::class);
            /** @var ProductSettings $product */
            $product = app(ProductSettings::class);
            /** @var CheckoutSettings $checkout */
            $checkout = app(CheckoutSettings::class);
            /** @var FooterSettings $footer */
            $footer = app(FooterSettings::class);

            $locales = config('translation-manager.available_locales', ['vi', 'en']);
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

            return [
                'general' => [
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
                    'logo' => $general->logo ? \Illuminate\Support\Facades\Storage::url($general->logo) : null,
                    'favicon' => $general->favicon ? \Illuminate\Support\Facades\Storage::url($general->favicon) : null,
                ],
                'products' => [
                    'add_to_cart_behavior' => $product->add_to_cart_behavior,
                    'enable_reviews' => $product->enable_reviews,
                ],
                'checkout' => [
                    'enable_guest_checkout' => $checkout->enable_guest_checkout,
                    'enabled_payment_gateways' => $checkout->enabled_payment_gateways,
                    'payment_gateways' => $this->getPaymentGatewaysConfig($checkout),
                    'shipping_methods' => ShippingMethod::where('is_enabled', true)->get()->map(fn($m) => [
                        'id' => $m->id,
                        'name' => $m->name,
                        'type' => $m->type,
                        'settings' => $m->settings,
                    ]),
                ],
                'footer' => [
                    'copyright' => $footer->copyright,
                    'links' => $footer->links,
                ],
                'languages' => $languages,
                'translations' => $translations,
                'countries' => config('countries', []),
            ];
        });
    }

    private function getPaymentGatewaysConfig($checkout): array
    {
        $gateways = [];
        $enabled = $checkout->enabled_payment_gateways ?? ['cod'];

        foreach ($enabled as $gateway) {
            $gateways[] = match ($gateway) {
                'cod' => ['id' => 'cod', 'name' => trans('storefront.payments.cod'), 'icon' => 'truck'],
                'stripe' => ['id' => 'stripe', 'name' => trans('storefront.payments.stripe'), 'icon' => 'credit-card', 'public_key' => $checkout->stripe_public_key],
                'paypal' => ['id' => 'paypal', 'name' => trans('storefront.payments.paypal'), 'icon' => 'paypal', 'client_id' => $checkout->paypal_client_id, 'mode' => $checkout->paypal_mode],
                'vnpay' => ['id' => 'vnpay', 'name' => 'VNPay', 'icon' => 'building', 'tmn_code' => $checkout->vnpay_tmn_code],
                'momo' => ['id' => 'momo', 'name' => 'Ví MoMo', 'icon' => 'wallet', 'partner_code' => $checkout->momo_partner_code],
                default => ['id' => $gateway, 'name' => ucfirst($gateway), 'icon' => 'credit-card'],
            };
        }

        return $gateways;
    }
}
