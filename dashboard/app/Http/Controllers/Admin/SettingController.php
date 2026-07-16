<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Settings\ApiSettings;
use App\Settings\AdvancedSettings;
use App\Settings\CouponSettings;
use App\Settings\CustomSettings;
use App\Settings\DBSettings;
use App\Settings\EmailSettings;
use App\Settings\FooterSettings;
use App\Settings\GeneralSettings;
use App\Settings\InventorySettings;
use App\Settings\LoyaltySettings;
use App\Settings\MailSettings;
use App\Settings\MaintenanceSettings;
use App\Settings\MarketingSettings;
use App\Settings\ProductSettings;
use App\Settings\WebhookSettings;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class SettingController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return Setting::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.settings';
    }

    protected function routePrefix(): string
    {
        return 'admin.settings';
    }

    protected function searchable(): array
    {
        return ['group', 'name'];
    }

    public function index(Request $request): View
    {
        $schema = $this->settingsSchema();
        
        $ecommerceSettings = app(\App\Settings\DBSettings::class); 
        // Note: ecommerce settings are actually stored via Setting model, let's fetch it via query to be safe
        $shippingEnabled = \App\Models\Setting::where('group', 'ecommerce')->where('name', 'shipping_enabled')->value('payload');
        if (isset($shippingEnabled) && !(bool) $shippingEnabled) {
            unset($schema['shipping']);
        }

        $activeTab = $request->query('tab');

        if (! is_string($activeTab) || ! isset($schema[$activeTab])) {
            $activeTab = array_key_first($schema);
        }

        return view('admin.settings.tabs', [
            'title' => __('admin.sidebar.settings'),
            'schema' => $schema,
            'activeTab' => $activeTab,
            'values' => $this->settingValues(),
        ]);
    }

    public function updateGroup(Request $request): RedirectResponse
    {
        $schema = $this->settingsSchema();
        $group = (string) $request->input('group');

        abort_unless(isset($schema[$group]), 404);

        $submitted = (array) $request->input('settings', []);
        $settingsClass = $this->settingsClassForGroup($group);

        if ($settingsClass) {
            $settings = app($settingsClass);

            foreach ($schema[$group]['fields'] as $name => $field) {
                $type = $field['type'] ?? 'text';

                if ($type === 'password' && blank($submitted[$name] ?? null) && ! $request->hasFile("settings.$name")) {
                    continue;
                }

                $currentValue = data_get($settings, $name);
                $value = $this->normalizeSettingValue($submitted[$name] ?? null, $field, $request, $group, $name, $currentValue);

                data_set($settings, $name, $value);
            }

            $settings->save();
        } else {
            foreach ($schema[$group]['fields'] as $name => $field) {
                if (($field['type'] ?? 'text') === 'password' && blank($submitted[$name] ?? null)) {
                    continue;
                }

                $value = $this->normalizeSettingValue($submitted[$name] ?? null, $field, $request, $group, $name);

                Setting::updateOrCreate(
                    ['group' => $group, 'name' => $name],
                    ['payload' => $value, 'locked' => false]
                );
            }
        }

        $this->flushStorefrontCaches();

        return redirect()
            ->route('admin.settings.index', ['tab' => $group])
            ->with('status', 'Đã lưu nhóm setting ' . ($schema[$group]['label'] ?? $group));
    }

    protected function fields(): array
    {
        return [
            'group' => ['label' => 'Group', 'rules' => ['required', 'string', 'max:255']],
            'name' => ['label' => 'Name', 'rules' => ['required', 'string', 'max:255']],
            'payload' => ['label' => 'Payload JSON/String', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
            'locked' => ['label' => 'Locked (0/1)', 'type' => 'number', 'rules' => ['nullable', 'boolean']],
        ];
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        if (isset($data['payload']) && is_string($data['payload']) && $data['payload'] !== '') {
            $decoded = json_decode($data['payload'], true);
            $data['payload'] = json_last_error() === JSON_ERROR_NONE ? $decoded : $data['payload'];
        }

        return $data;
    }

    protected function formData(?Model $record = null): array
    {
        if (! $record) {
            return [];
        }

        return [
            'payload' => is_array($record->payload)
                ? json_encode($record->payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                : $record->payload,
        ];
    }

    private function settingValues(): array
    {
        $values = [];

        foreach ($this->settingsSchema() as $group => $section) {
            $settingsClass = $this->settingsClassForGroup($group);

            if ($settingsClass) {
                $settings = app($settingsClass);
                $values[$group] = method_exists($settings, 'toArray') ? $settings->toArray() : get_object_vars($settings);
                continue;
            }

            $values[$group] = Setting::query()
                ->where('group', $group)
                ->get(['name', 'payload'])
                ->pluck('payload', 'name')
                ->toArray();
        }

        return $values;
    }

    private function normalizeSettingValue(mixed $value, array $field, ?Request $request = null, ?string $group = null, ?string $name = null, mixed $currentValue = null): mixed
    {
        return match ($field['type'] ?? 'text') {
            'image' => $this->storeUploadedSettingFile($request, $group, $name, $currentValue),
            'boolean' => (bool) $value,
            'number' => $value === null || $value === '' ? null : (int) $value,
            'decimal' => $value === null || $value === '' ? null : (float) $value,
            'json' => $this->decodeJsonSetting($value),
            default => $value === '' ? null : $value,
        };
    }

    private function storeUploadedSettingFile(?Request $request, ?string $group, ?string $name, mixed $currentValue = null): mixed
    {
        if (! $request || ! $group || ! $name) {
            return $currentValue;
        }

        $file = $request->file("settings.$name");
        if (! $file) {
            return $currentValue;
        }

        return $file->storePublicly("settings/{$group}", 'public');
    }

    private function decodeJsonSetting(mixed $value): mixed
    {
        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    private function settingsSchema(): array
    {
        return [
            'settings' => [
                'label' => 'admin.sidebar.storefront_settings',
                'description' => 'admin.settings.storefront_description',
                'fields' => [
                    'logo' => ['label' => __('admin.settings.field_labels.logo'), 'type' => 'image', 'default' => null],
                    'logo_favicon' => ['label' => __('admin.settings.field_labels.favicon'), 'type' => 'image', 'default' => null],
                    'primary_color' => ['label' => __('admin.settings.field_labels.primary_color'), 'type' => 'color', 'default' => '#4f46e5'],
                    'name' => ['label' => __('admin.settings.field_labels.system_name'), 'default' => 'My E-commerce'],
                    'about' => ['label' => __('admin.settings.field_labels.description'), 'type' => 'textarea', 'default' => ''],
                    'timezone' => ['label' => __('admin.settings.field_labels.timezone'), 'default' => 'Asia/Ho_Chi_Minh'],
                    'default_language' => ['label' => __('admin.settings.field_labels.default_language'), 'default' => 'Vietnamese'],
                    'currency' => ['label' => __('admin.settings.field_labels.currency'), 'default' => 'VND'],
                    'currency_symbol' => ['label' => __('admin.settings.field_labels.currency_symbol'), 'default' => 'đ'],
                    'new_user_role' => ['label' => __('admin.settings.field_labels.new_user_role'), 'default' => 'User'],
                    'send_welcome_email' => ['label' => __('admin.settings.field_labels.send_welcome_email'), 'type' => 'boolean', 'default' => false],
                    'exchange_rates' => ['label' => __('admin.settings.field_labels.exchange_rates'), 'type' => 'json', 'default' => ['VND' => 1, 'USD' => 25000]],
                ],
                'management_actions' => [
                    ['label' => 'admin.sidebar.roles', 'route' => 'admin.roles.index'],
                    ['label' => 'admin.sidebar.permissions', 'route' => 'admin.permissions.index'],
                ],
            ],
            'ecommerce' => [
                'label' => 'admin.sidebar.ecommerce_settings',
                'description' => 'admin.settings.ecommerce_description',
                'fields' => [
                    'enabled' => ['label' => __('admin.settings.field_labels.enabled'), 'type' => 'boolean', 'default' => true],
                    'catalog_mode' => ['label' => __('admin.settings.field_labels.catalog_mode'), 'type' => 'boolean', 'default' => false],
                    'enable_wishlist' => ['label' => __('admin.settings.field_labels.enable_wishlist'), 'type' => 'boolean', 'default' => true],
                    'enable_compare' => ['label' => __('admin.settings.field_labels.enable_compare'), 'type' => 'boolean', 'default' => false],
                    'order_number_prefix' => ['label' => __('admin.settings.field_labels.order_number_prefix'), 'default' => 'ORD'],
                    'low_stock_threshold' => ['label' => __('admin.settings.field_labels.low_stock_threshold'), 'type' => 'number', 'default' => 5],
                    'tax_enabled' => ['label' => __('admin.settings.field_labels.tax_enabled'), 'type' => 'boolean', 'default' => true],
                    'shipping_enabled' => ['label' => __('admin.settings.field_labels.shipping_enabled'), 'type' => 'boolean', 'default' => true],
                ],
                'management_actions' => [
                    ['label' => 'admin.sidebar.classes', 'route' => 'admin.tax-classes.index', 'visible_when' => 'tax_enabled'],
                    ['label' => 'admin.sidebar.rates', 'route' => 'admin.tax-rates.index', 'visible_when' => 'tax_enabled'],
                ],
            ],
            'general' => [
                'label' => 'admin.sidebar.general_settings',
                'description' => 'admin.settings.general_description',
                'fields' => [
                    'store_name' => ['label' => __('admin.settings.field_labels.store_name'), 'default' => 'My E-commerce Store'],
                    'store_email' => ['label' => __('admin.settings.field_labels.store_email'), 'type' => 'email', 'default' => 'admin@example.com'],
                    'store_phone' => ['label' => __('admin.settings.field_labels.store_phone'), 'default' => '0123456789'],
                    'store_country' => ['label' => __('admin.settings.field_labels.store_country'), 'default' => 'VN'],
                    'logo' => ['label' => __('admin.settings.field_labels.logo'), 'type' => 'image', 'default' => null],
                    'favicon' => ['label' => __('admin.settings.field_labels.favicon'), 'type' => 'image', 'default' => null],
                    'default_currency' => ['label' => __('admin.settings.field_labels.default_currency'), 'default' => 'VND'],
                    'currency_position' => ['label' => __('admin.settings.field_labels.currency_position'), 'type' => 'select', 'default' => 'right_space', 'options' => ['left' => 'Left', 'right' => 'Right', 'left_space' => 'Left space', 'right_space' => 'Right space']],
                    'decimal_places' => ['label' => __('admin.settings.field_labels.decimal_places'), 'type' => 'number', 'default' => 0],
                    'weight_unit' => ['label' => __('admin.settings.field_labels.weight_unit'), 'default' => 'kg'],
                    'dimension_unit' => ['label' => __('admin.settings.field_labels.dimension_unit'), 'default' => 'cm'],
                ],
            ],
            'products' => [
                'label' => 'admin.sidebar.product_settings',
                'description' => 'admin.settings.product_description',
                'fields' => [
                    'add_to_cart_behavior' => ['label' => __('admin.settings.field_labels.add_to_cart_behavior'), 'type' => 'select', 'default' => 'ajax', 'options' => ['ajax' => 'Ajax', 'redirect' => 'Redirect']],
                    'enable_reviews' => ['label' => __('admin.settings.field_labels.enable_reviews'), 'type' => 'boolean', 'default' => true],
                    'guest_reviews_allowed' => ['label' => __('admin.settings.field_labels.guest_reviews_allowed'), 'type' => 'boolean', 'default' => false],
                    'review_stars_required' => ['label' => __('admin.settings.field_labels.review_stars_required'), 'type' => 'boolean', 'default' => true],
                ],
            ],
            'checkout' => [
                'label' => 'admin.sidebar.checkout_settings',
                'description' => 'admin.settings.checkout_description',
                'fields' => [
                    'enable_guest_checkout' => ['label' => __('admin.settings.field_labels.enable_guest_checkout'), 'type' => 'boolean', 'default' => true],
                    'tax_calculation_address' => ['label' => __('admin.settings.field_labels.tax_calculation_address'), 'type' => 'select', 'default' => 'shipping', 'options' => ['shipping' => 'Shipping', 'billing' => 'Billing', 'base' => 'Base']],
                    'prices_include_tax' => ['label' => __('admin.settings.field_labels.prices_include_tax'), 'type' => 'boolean', 'default' => false],
                    'enabled_payment_gateways' => ['label' => __('admin.settings.field_labels.enabled_payment_gateways'), 'type' => 'json', 'default' => ['cod', 'bank_transfer']],
                    'stripe_public_key' => ['label' => __('admin.settings.field_labels.stripe_public_key'), 'default' => null],
                    'stripe_secret_key' => ['label' => __('admin.settings.field_labels.stripe_secret_key'), 'type' => 'password', 'default' => null],
                    'paypal_mode' => ['label' => __('admin.settings.field_labels.paypal_mode'), 'type' => 'select', 'default' => 'sandbox', 'options' => ['sandbox' => 'Sandbox', 'live' => 'Live']],
                    'vnpay_tmn_code' => ['label' => __('admin.settings.field_labels.vnpay_tmn_code'), 'default' => null],
                    'momo_partner_code' => ['label' => __('admin.settings.field_labels.momo_partner_code'), 'default' => null],
                ],
            ],
            'shipping' => [
                'label' => 'admin.sidebar.shipping_settings',
                'description' => 'admin.settings.shipping_description',
                'fields' => [],
                'management_actions' => [
                    ['label' => 'admin.settings.manage_shipping_zones', 'route' => 'admin.shipping-zones.index'],
                    ['label' => 'admin.settings.manage_shipping_methods', 'route' => 'admin.shipping-methods.index'],
                ],
            ],
            'inventory' => [
                'label' => 'admin.sidebar.inventory_settings',
                'description' => 'admin.settings.inventory_description',
                'fields' => [
                    'multi_warehouse_enabled' => ['label' => __('admin.settings.field_labels.multi_warehouse_enabled'), 'type' => 'boolean', 'default' => false],
                    'split_shipping_enabled' => ['label' => __('admin.settings.field_labels.split_shipping_enabled'), 'type' => 'boolean', 'default' => false],
                    'warehouse_selection_strategy' => ['label' => __('admin.settings.field_labels.warehouse_selection_strategy'), 'type' => 'select', 'default' => 'stock_volume', 'options' => ['stock_volume' => 'Stock volume', 'proximity' => 'Proximity']],
                    'reservation_expiry_minutes' => ['label' => __('admin.settings.field_labels.reservation_expiry_minutes'), 'type' => 'number', 'default' => 15],
                ],
                'management_actions' => [
                    ['label' => 'admin.settings.manage_inventories', 'route' => 'admin.inventories.index'],
                    ['label' => 'admin.settings.manage_inventory_records', 'route' => 'admin.inventory-records.index'],
                    ['label' => 'admin.settings.view_inventory_movements', 'route' => 'admin.inventory-movements.index'],
                ],
            ],
            'marketing' => [
                'label' => 'admin.sidebar.marketing_settings',
                'description' => 'admin.settings.marketing_description',
                'fields' => [
                    'upsell_enabled' => ['label' => __('admin.settings.field_labels.upsell_enabled'), 'type' => 'boolean', 'default' => false],
                    'cross_sell_enabled' => ['label' => __('admin.settings.field_labels.cross_sell_enabled'), 'type' => 'boolean', 'default' => false],
                    'combo_enabled' => ['label' => __('admin.settings.field_labels.combo_enabled'), 'type' => 'boolean', 'default' => false],
                    'loyalty_enabled' => ['label' => __('admin.settings.field_labels.loyalty_enabled'), 'type' => 'boolean', 'default' => false],
                    'points_per_currency' => ['label' => __('admin.settings.field_labels.points_per_currency'), 'type' => 'number', 'default' => 1],
                    'point_conversion_rate' => ['label' => __('admin.settings.field_labels.point_conversion_rate'), 'type' => 'number', 'default' => 1000],
                    'enable_coupons' => ['label' => __('admin.settings.field_labels.enable_coupons'), 'type' => 'boolean', 'default' => true],
                    'allow_multiple_coupons' => ['label' => __('admin.settings.field_labels.allow_multiple_coupons'), 'type' => 'boolean', 'default' => false],
                    'calculate_tax_after_coupon' => ['label' => __('admin.settings.field_labels.calculate_tax_after_coupon'), 'type' => 'boolean', 'default' => true],
                ],
                'management_actions' => [
                    ['label' => 'admin.settings.manage_upsell', 'route' => 'admin.upsell-products.index', 'visible_when' => 'upsell_enabled'],
                    ['label' => 'admin.settings.manage_cross_sell', 'route' => 'admin.cross-sell-products.index', 'visible_when' => 'cross_sell_enabled'],
                    ['label' => 'admin.settings.manage_combo', 'route' => 'admin.combo-products.index', 'visible_when' => 'combo_enabled'],
                    ['label' => 'admin.loyalty.management', 'route' => 'admin.loyalty-points.index', 'visible_when' => 'loyalty_enabled'],
                    ['label' => 'admin.coupon.label', 'route' => 'admin.coupons.index', 'visible_when' => 'enable_coupons'],
                ],
            ],
            'footer' => [
                'label' => 'admin.sidebar.footer_settings',
                'description' => 'admin.settings.footer_description',
                'fields' => [
                    'copyright' => ['label' => __('admin.settings.field_labels.copyright'), 'default' => '© 2025 Company Name'],
                    'links' => ['label' => __('admin.settings.field_labels.links'), 'type' => 'json', 'default' => []],
                ],
            ],
            'mail' => [
                'label' => 'admin.sidebar.mail_settings',
                'description' => 'admin.settings.mail_description',
                'fields' => [
                    'email_from_address' => ['label' => __('admin.settings.field_labels.email_from_address'), 'type' => 'email', 'default' => null],
                    'email_from_name' => ['label' => __('admin.settings.field_labels.email_from_name'), 'default' => null],
                    'email_host' => ['label' => __('admin.settings.field_labels.email_host'), 'default' => null],
                    'email_port' => ['label' => __('admin.settings.field_labels.email_port'), 'type' => 'number', 'default' => null],
                    'email_username' => ['label' => __('admin.settings.field_labels.email_username'), 'default' => null],
                    'email_password' => ['label' => __('admin.settings.field_labels.email_password'), 'type' => 'password', 'default' => null],
                    'email_encryption' => ['label' => __('admin.settings.field_labels.email_encryption'), 'type' => 'select', 'default' => null, 'options' => ['' => 'None', 'tls' => 'TLS', 'ssl' => 'SSL']],
                    'use_queue_for_emails' => ['label' => __('admin.settings.field_labels.use_queue_for_emails'), 'type' => 'boolean', 'default' => false],
                    'base_color' => ['label' => __('admin.settings.field_labels.base_color'), 'type' => 'color', 'default' => '#4f46e5'],
                    'notifications' => ['label' => __('admin.settings.field_labels.notifications'), 'type' => 'json', 'default' => []],
                ],
                'management_actions' => [
                    ['label' => 'admin.sidebar.mail_logs', 'route' => 'admin.mail-logs.index'],
                ],
            ],
            'webhook' => [
                'label' => 'admin.sidebar.webhooks',
                'description' => 'admin.settings.webhook_description',
                'fields' => [
                    'enabled' => ['label' => __('admin.settings.field_labels.enabled'), 'type' => 'boolean', 'default' => false],
                    'log_retention_days' => ['label' => __('admin.settings.field_labels.log_retention_days'), 'type' => 'number', 'default' => 30],
                    'allowed_roles' => ['label' => __('admin.settings.field_labels.allowed_roles'), 'type' => 'json', 'default' => []],
                ],
                'management_actions' => [
                    ['label' => 'admin.webhook.management', 'route' => 'admin.webhooks.index', 'visible_when' => 'enabled'],
                    ['label' => 'admin.webhook.logs', 'route' => 'admin.webhook-logs.index', 'visible_when' => 'enabled'],
                ],
            ],
            'api' => [
                'label' => 'admin.sidebar.api_settings',
                'description' => 'admin.settings.api_description',
                'fields' => [
                    'enabled' => ['label' => __('admin.settings.field_labels.enabled'), 'type' => 'boolean', 'default' => true],
                    'idempotency_ttl' => ['label' => __('admin.settings.field_labels.idempotency_ttl'), 'type' => 'number', 'default' => 86400],
                    'hmac_secret' => ['label' => __('admin.settings.field_labels.hmac_secret'), 'type' => 'password', 'default' => null],
                    'allowed_roles' => ['label' => __('admin.settings.field_labels.allowed_roles'), 'type' => 'json', 'default' => []],
                ],
            ],
            'advanced' => [
                'label' => 'admin.sidebar.advanced_settings',
                'description' => 'admin.settings.advanced_description',
                'fields' => [
                    'cart_page_id' => ['label' => __('admin.settings.field_labels.cart_page_id'), 'type' => 'number', 'default' => null],
                    'checkout_page_id' => ['label' => __('admin.settings.field_labels.checkout_page_id'), 'type' => 'number', 'default' => null],
                    'account_page_id' => ['label' => __('admin.settings.field_labels.account_page_id'), 'type' => 'number', 'default' => null],
                ],
            ],
            'custom' => [
                'label' => 'admin.sidebar.custom_settings',
                'description' => 'admin.settings.custom_description',
                'fields' => [
                    'custom_css' => ['label' => __('admin.settings.field_labels.custom_css'), 'type' => 'textarea', 'default' => ''],
                    'custom_js' => ['label' => __('admin.settings.field_labels.custom_js'), 'type' => 'textarea', 'default' => ''],
                ],
            ],
        ];
    }

    private function settingsClassForGroup(string $group): ?string
    {
        return match ($group) {
            'settings' => DBSettings::class,
            'general' => GeneralSettings::class,
            'products' => ProductSettings::class,
            'checkout' => \App\Settings\CheckoutSettings::class,
            'inventory' => InventorySettings::class,
            'marketing' => MarketingSettings::class,
            'footer' => FooterSettings::class,
            'mail' => MailSettings::class,
            'webhook' => WebhookSettings::class,
            'api' => ApiSettings::class,
            'advanced' => AdvancedSettings::class,
            'custom' => CustomSettings::class,
            'maintenance' => MaintenanceSettings::class,
            default => null,
        };
    }

    private function flushStorefrontCaches(): void
    {
        foreach (config('app.supported_locales', ['vi', 'en']) as $localeConfig) {
            $locale = is_array($localeConfig) ? ($localeConfig['code'] ?? 'vi') : (string) $localeConfig;
            Cache::forget('storefront_settings_v1_' . $locale);
        }
    }
}
