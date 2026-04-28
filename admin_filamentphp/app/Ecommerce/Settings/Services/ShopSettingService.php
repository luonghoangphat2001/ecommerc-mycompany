<?php

namespace App\Ecommerce\Settings\Services;

use App\Ecommerce\Settings\Contracts\ShopSettingServiceInterface;
use App\Settings\GeneralSettings;
use App\Settings\ProductSettings;
use App\Settings\CheckoutSettings;
use App\Settings\EmailSettings;
use App\Settings\AdvancedSettings;
use App\Settings\ApiSettings;
use App\Settings\WebhookSettings;

class ShopSettingService implements ShopSettingServiceInterface
{
    /**
     * @inheritDoc
     */
    public function updateAllSettings(array $data): void
    {
        if (isset($data['general'])) {
            $this->updateGeneralSettings($data['general']);
        }

        $groups = [
            'products' => ProductSettings::class,
            'checkout' => CheckoutSettings::class,
            'emails' => EmailSettings::class,
            'advanced' => AdvancedSettings::class,
            'api' => ApiSettings::class,
            'webhook' => WebhookSettings::class,
        ];

        foreach ($groups as $key => $class) {
            if (isset($data[$key])) {
                app($class)->fill($data[$key])->save();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function updateGeneralSettings(array $data): void
    {
        $settings = app(GeneralSettings::class);
        
        $settings->store_name = $data['store_name'] ?? $settings->store_name;
        $settings->store_email = $data['store_email'] ?? $settings->store_email;
        $settings->store_phone = $data['store_phone'] ?? $settings->store_phone;
        $settings->store_country = $data['store_country'] ?? $settings->store_country;
        $settings->default_currency = $data['default_currency'] ?? $settings->default_currency;
        $settings->currency_symbol = $data['currency_symbol'] ?? $settings->currency_symbol;
        $settings->currency_position = $data['currency_position'] ?? $settings->currency_position;
        $settings->thousand_separator = $data['thousand_separator'] ?? $settings->thousand_separator;
        $settings->decimal_separator = $data['decimal_separator'] ?? $settings->decimal_separator;
        $settings->decimal_places = $data['decimal_places'] ?? $settings->decimal_places;
        $settings->weight_unit = $data['weight_unit'] ?? $settings->weight_unit;
        $settings->dimension_unit = $data['dimension_unit'] ?? $settings->dimension_unit;
        
        $settings->save();
    }

    /**
     * @inheritDoc
     */
    public function getLocalizationInfo(string $countryCode): array
    {
        $locales = config('locale-info');
        
        if (!isset($locales[$countryCode])) {
            return [];
        }

        $info = $locales[$countryCode];

        return [
            'default_currency' => $info['currency_code'] ?? 'VND',
            'currency_symbol' => $info['short_symbol'] ?? '$',
            'currency_position' => $info['currency_pos'] ?? 'left',
            'thousand_separator' => $info['thousand_sep'] ?? ',',
            'decimal_separator' => $info['decimal_sep'] ?? '.',
            'decimal_places' => $info['num_decimals'] ?? 2,
        ];
    }
}
