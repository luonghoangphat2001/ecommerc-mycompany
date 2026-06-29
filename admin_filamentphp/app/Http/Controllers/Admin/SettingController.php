<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return Setting::class;
    }

    protected function title(): string
    {
        return 'Settings';
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
        $activeTab = $request->query('tab');

        if (! is_string($activeTab) || ! isset($schema[$activeTab])) {
            $activeTab = array_key_first($schema);
        }

        return view('admin.settings.tabs', [
            'title' => 'Settings',
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
        foreach ($schema[$group]['fields'] as $name => $field) {
            if (($field['type'] ?? 'text') === 'password' && blank($submitted[$name] ?? null)) {
                continue;
            }

            $value = $this->normalizeSettingValue($submitted[$name] ?? null, $field);

            Setting::updateOrCreate(
                ['group' => $group, 'name' => $name],
                ['payload' => $value, 'locked' => false]
            );
        }

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
        return Setting::query()
            ->get(['group', 'name', 'payload'])
            ->groupBy('group')
            ->map(fn ($items) => $items->pluck('payload', 'name')->toArray())
            ->toArray();
    }

    private function normalizeSettingValue(mixed $value, array $field): mixed
    {
        return match ($field['type'] ?? 'text') {
            'boolean' => (bool) $value,
            'number' => $value === null || $value === '' ? null : (int) $value,
            'decimal' => $value === null || $value === '' ? null : (float) $value,
            'json' => $this->decodeJsonSetting($value),
            default => $value === '' ? null : $value,
        };
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
                'label' => 'Storefront',
                'description' => 'Thông tin hiển thị cơ bản của admin/storefront.',
                'fields' => [
                    'name' => ['label' => 'Tên hệ thống', 'default' => 'My E-commerce'],
                    'about' => ['label' => 'Mô tả', 'type' => 'textarea', 'default' => ''],
                    'timezone' => ['label' => 'Timezone', 'default' => 'Asia/Ho_Chi_Minh'],
                    'default_language' => ['label' => 'Ngôn ngữ mặc định', 'default' => 'Vietnamese'],
                    'currency' => ['label' => 'Currency', 'default' => 'VND'],
                    'currency_symbol' => ['label' => 'Currency symbol', 'default' => 'đ'],
                    'new_user_role' => ['label' => 'Role user mới', 'default' => 'User'],
                    'send_welcome_email' => ['label' => 'Gửi welcome email', 'type' => 'boolean', 'default' => false],
                    'exchange_rates' => ['label' => 'Exchange rates JSON', 'type' => 'json', 'default' => ['VND' => 1, 'USD' => 25000]],
                ],
            ],
            'ecommerce' => [
                'label' => 'Ecommerce',
                'description' => 'Nhóm setting ecommerce tổng quát bị thiếu sau khi bỏ Filament.',
                'fields' => [
                    'enabled' => ['label' => 'Bật ecommerce', 'type' => 'boolean', 'default' => true],
                    'catalog_mode' => ['label' => 'Catalog mode', 'type' => 'boolean', 'default' => false],
                    'enable_wishlist' => ['label' => 'Wishlist', 'type' => 'boolean', 'default' => true],
                    'enable_compare' => ['label' => 'Compare products', 'type' => 'boolean', 'default' => false],
                    'order_number_prefix' => ['label' => 'Order prefix', 'default' => 'ORD'],
                    'low_stock_threshold' => ['label' => 'Low stock threshold', 'type' => 'number', 'default' => 5],
                    'tax_enabled' => ['label' => 'Bật thuế', 'type' => 'boolean', 'default' => true],
                    'shipping_enabled' => ['label' => 'Bật shipping', 'type' => 'boolean', 'default' => true],
                ],
            ],
            'general' => [
                'label' => 'General',
                'description' => 'Cấu hình cửa hàng, tiền tệ, đơn vị đo.',
                'fields' => [
                    'store_name' => ['label' => 'Store name', 'default' => 'My E-commerce Store'],
                    'store_email' => ['label' => 'Store email', 'type' => 'email', 'default' => 'admin@example.com'],
                    'store_phone' => ['label' => 'Store phone', 'default' => '0123456789'],
                    'store_country' => ['label' => 'Country', 'default' => 'VN'],
                    'default_currency' => ['label' => 'Default currency', 'default' => 'VND'],
                    'currency_position' => ['label' => 'Currency position', 'type' => 'select', 'default' => 'right_space', 'options' => ['left' => 'Left', 'right' => 'Right', 'left_space' => 'Left space', 'right_space' => 'Right space']],
                    'decimal_places' => ['label' => 'Decimal places', 'type' => 'number', 'default' => 0],
                    'weight_unit' => ['label' => 'Weight unit', 'default' => 'kg'],
                    'dimension_unit' => ['label' => 'Dimension unit', 'default' => 'cm'],
                ],
            ],
            'products' => [
                'label' => 'Products',
                'description' => 'Hành vi sản phẩm và review.',
                'fields' => [
                    'add_to_cart_behavior' => ['label' => 'Add to cart behavior', 'type' => 'select', 'default' => 'ajax', 'options' => ['ajax' => 'Ajax', 'redirect' => 'Redirect']],
                    'enable_reviews' => ['label' => 'Bật review', 'type' => 'boolean', 'default' => true],
                    'guest_reviews_allowed' => ['label' => 'Guest được review', 'type' => 'boolean', 'default' => false],
                    'review_stars_required' => ['label' => 'Bắt buộc sao review', 'type' => 'boolean', 'default' => true],
                ],
            ],
            'checkout' => [
                'label' => 'Checkout',
                'description' => 'Checkout, tax và payment gateways.',
                'fields' => [
                    'enable_guest_checkout' => ['label' => 'Guest checkout', 'type' => 'boolean', 'default' => true],
                    'tax_calculation_address' => ['label' => 'Tax address', 'type' => 'select', 'default' => 'shipping', 'options' => ['shipping' => 'Shipping', 'billing' => 'Billing', 'base' => 'Base']],
                    'prices_include_tax' => ['label' => 'Giá đã gồm thuế', 'type' => 'boolean', 'default' => false],
                    'enabled_payment_gateways' => ['label' => 'Payment gateways JSON', 'type' => 'json', 'default' => ['cod', 'bank_transfer']],
                    'stripe_public_key' => ['label' => 'Stripe public key', 'default' => null],
                    'stripe_secret_key' => ['label' => 'Stripe secret key', 'type' => 'password', 'default' => null],
                    'paypal_mode' => ['label' => 'PayPal mode', 'type' => 'select', 'default' => 'sandbox', 'options' => ['sandbox' => 'Sandbox', 'live' => 'Live']],
                    'vnpay_tmn_code' => ['label' => 'VNPay TMN code', 'default' => null],
                    'momo_partner_code' => ['label' => 'Momo partner code', 'default' => null],
                ],
            ],
            'shipping' => [
                'label' => 'Shipping',
                'description' => 'Quản lý khu vực và phương thức vận chuyển giống flow cũ.',
                'fields' => [],
                'management_actions' => [
                    ['label' => 'Quản lý Shipping Zones', 'route' => 'admin.shipping-zones.index'],
                    ['label' => 'Quản lý Shipping Methods', 'route' => 'admin.shipping-methods.index'],
                ],
            ],
            'inventory' => [
                'label' => 'Inventory',
                'description' => 'Theo flow InventoryService/Checkout PlaceOrder: multi-warehouse, split shipping, reservation.',
                'fields' => [
                    'multi_warehouse_enabled' => ['label' => 'Multi warehouse', 'type' => 'boolean', 'default' => false],
                    'split_shipping_enabled' => ['label' => 'Split shipping', 'type' => 'boolean', 'default' => false],
                    'warehouse_selection_strategy' => ['label' => 'Warehouse strategy', 'type' => 'select', 'default' => 'stock_volume', 'options' => ['stock_volume' => 'Stock volume', 'proximity' => 'Proximity']],
                    'reservation_expiry_minutes' => ['label' => 'Reservation expiry minutes', 'type' => 'number', 'default' => 15],
                ],
                'management_actions' => [
                    ['label' => 'Quản lý Kho', 'route' => 'admin.inventories.index'],
                    ['label' => 'Quản lý Phiếu Kho', 'route' => 'admin.inventory-records.index'],
                    ['label' => 'Xem Biến Động Kho', 'route' => 'admin.inventory-movements.index'],
                ],
            ],
            'coupon' => [
                'label' => 'Coupons',
                'description' => 'Khuyến mãi và tính thuế coupon.',
                'fields' => [
                    'enable_coupons' => ['label' => 'Bật coupons', 'type' => 'boolean', 'default' => true],
                    'allow_multiple_coupons' => ['label' => 'Cho nhiều coupon', 'type' => 'boolean', 'default' => false],
                    'calculate_tax_after_coupon' => ['label' => 'Tính thuế sau coupon', 'type' => 'boolean', 'default' => true],
                ],
            ],
            'loyalty' => [
                'label' => 'Loyalty',
                'description' => 'Điểm thưởng khách hàng.',
                'fields' => [
                    'enabled' => ['label' => 'Bật loyalty', 'type' => 'boolean', 'default' => false],
                    'points_per_currency' => ['label' => 'Points per currency', 'type' => 'number', 'default' => 1],
                    'point_conversion_rate' => ['label' => 'Point conversion rate', 'type' => 'number', 'default' => 1000],
                ],
            ],
            'marketing' => [
                'label' => 'Combo / Cross-sell',
                'description' => 'Theo flow cũ của ComboService, CrossSellService, UpsellService qua MarketingSettings.',
                'fields' => [
                    'upsell_enabled' => ['label' => 'Bật upsell', 'type' => 'boolean', 'default' => false],
                    'cross_sell_enabled' => ['label' => 'Bật cross-sell', 'type' => 'boolean', 'default' => false],
                    'combo_enabled' => ['label' => 'Bật combo products', 'type' => 'boolean', 'default' => false],
                ],
                'management_actions' => [
                    ['label' => 'Quản lý Upsell', 'route' => 'admin.upsell-products.index', 'visible_when' => 'upsell_enabled'],
                    ['label' => 'Quản lý Cross-sell', 'route' => 'admin.cross-sell-products.index', 'visible_when' => 'cross_sell_enabled'],
                    ['label' => 'Quản lý Combo', 'route' => 'admin.combo-products.index', 'visible_when' => 'combo_enabled'],
                ],
            ],
            'emails' => [
                'label' => 'Emails',
                'description' => 'EmailSettings cũ: sender và notification template.',
                'fields' => [
                    'sender_name' => ['label' => 'Sender name', 'default' => 'Admin'],
                    'sender_email' => ['label' => 'Sender email', 'type' => 'email', 'default' => 'admin@admin.com'],
                    'base_color' => ['label' => 'Base color', 'default' => '#4f46e5'],
                    'notifications' => ['label' => 'Notifications JSON', 'type' => 'json', 'default' => []],
                ],
            ],
            'footer' => [
                'label' => 'Footer',
                'description' => 'FooterSettings cho StorefrontSettingsService.',
                'fields' => [
                    'copyright' => ['label' => 'Copyright', 'default' => '© 2025 Company Name'],
                    'links' => ['label' => 'Footer links JSON', 'type' => 'json', 'default' => []],
                ],
            ],
            'mail' => [
                'label' => 'SMTP',
                'description' => 'Thông tin SMTP dùng bởi Laravel mailer.',
                'fields' => [
                    'email_from_address' => ['label' => 'From address', 'type' => 'email', 'default' => null],
                    'email_from_name' => ['label' => 'From name', 'default' => null],
                    'email_host' => ['label' => 'SMTP host', 'default' => null],
                    'email_port' => ['label' => 'SMTP port', 'type' => 'number', 'default' => null],
                    'email_username' => ['label' => 'SMTP username', 'default' => null],
                    'email_password' => ['label' => 'SMTP password', 'type' => 'password', 'default' => null],
                    'email_encryption' => ['label' => 'Encryption', 'type' => 'select', 'default' => null, 'options' => ['' => 'None', 'tls' => 'TLS', 'ssl' => 'SSL']],
                    'use_queue_for_emails' => ['label' => 'Queue emails', 'type' => 'boolean', 'default' => false],
                ],
            ],
            'webhook' => [
                'label' => 'Webhooks',
                'description' => 'Cấu hình webhook delivery.',
                'fields' => [
                    'enabled' => ['label' => 'Bật webhooks', 'type' => 'boolean', 'default' => false],
                    'log_retention_days' => ['label' => 'Log retention days', 'type' => 'number', 'default' => 30],
                    'allowed_roles' => ['label' => 'Allowed roles JSON', 'type' => 'json', 'default' => []],
                ],
            ],
            'api' => [
                'label' => 'API',
                'description' => 'Cấu hình API bảo mật và idempotency.',
                'fields' => [
                    'enabled' => ['label' => 'Bật API', 'type' => 'boolean', 'default' => true],
                    'idempotency_ttl' => ['label' => 'Idempotency TTL', 'type' => 'number', 'default' => 86400],
                    'hmac_secret' => ['label' => 'HMAC secret', 'type' => 'password', 'default' => null],
                    'allowed_roles' => ['label' => 'Allowed roles JSON', 'type' => 'json', 'default' => []],
                ],
            ],
            'advanced' => [
                'label' => 'Advanced',
                'description' => 'Page mapping và cấu hình nâng cao.',
                'fields' => [
                    'cart_page_id' => ['label' => 'Cart page ID', 'type' => 'number', 'default' => null],
                    'checkout_page_id' => ['label' => 'Checkout page ID', 'type' => 'number', 'default' => null],
                    'account_page_id' => ['label' => 'Account page ID', 'type' => 'number', 'default' => null],
                ],
            ],
            'custom' => [
                'label' => 'Custom Code',
                'description' => 'CSS/JS custom cho storefront.',
                'fields' => [
                    'custom_css' => ['label' => 'Custom CSS', 'type' => 'textarea', 'default' => ''],
                    'custom_js' => ['label' => 'Custom JS', 'type' => 'textarea', 'default' => ''],
                ],
            ],
        ];
    }
}
