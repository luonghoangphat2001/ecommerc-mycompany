<?php

namespace App\Services;

use App\Contracts\Services\StorefrontSettingsServiceInterface;
use App\Contracts\Services\SettingServiceInterface;
use App\Settings\GeneralSettings;
use App\Settings\ProductSettings;
use App\Settings\CheckoutSettings;
use App\Settings\FooterSettings;
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
        activity('system')
            ->log('Storefront settings fetched via API');
            
        return Cache::remember('storefront_settings_v1', 3600, function () {
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
                ],
                'products' => [
                    'add_to_cart_behavior' => $product->add_to_cart_behavior,
                    'enable_reviews' => $product->enable_reviews,
                ],
                'checkout' => [
                    'enable_guest_checkout' => $checkout->enable_guest_checkout,
                    'enabled_payment_gateways' => $checkout->enabled_payment_gateways,
                ],
                'footer' => [
                    'copyright' => $footer->copyright,
                    'links' => $footer->links,
                ],
                'languages' => $languages,
                'translations' => $translations,
            ];
        });
    }
}
