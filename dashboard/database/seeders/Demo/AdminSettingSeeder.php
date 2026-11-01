<?php

namespace Database\Seeders\Demo;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSettingSeeder extends Seeder
{
    /**
     * Seed club store settings idempotently.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $settings = [
            // General Settings
            ['group' => 'general', 'name' => 'store_name', 'payload' => json_encode('Nova Club Store')],
            ['group' => 'general', 'name' => 'store_email', 'payload' => json_encode('contact@novaclub.vn')],
            ['group' => 'general', 'name' => 'store_phone', 'payload' => json_encode('1900 8888')],
            ['group' => 'general', 'name' => 'store_country', 'payload' => json_encode('VN')],
            ['group' => 'general', 'name' => 'default_currency', 'payload' => json_encode('VND')],
            ['group' => 'general', 'name' => 'currency_symbol', 'payload' => json_encode('₫')],
            ['group' => 'general', 'name' => 'currency_position', 'payload' => json_encode('right')],
            ['group' => 'general', 'name' => 'thousand_separator', 'payload' => json_encode('.')],
            ['group' => 'general', 'name' => 'decimal_separator', 'payload' => json_encode(',')],
            ['group' => 'general', 'name' => 'decimal_places', 'payload' => json_encode(0)],
            ['group' => 'general', 'name' => 'weight_unit', 'payload' => json_encode('kg')],
            ['group' => 'general', 'name' => 'dimension_unit', 'payload' => json_encode('cm')],

            // Product Settings
            ['group' => 'products', 'name' => 'items_per_page', 'payload' => json_encode(12)],
            ['group' => 'products', 'name' => 'add_to_cart_behavior', 'payload' => json_encode('stay')],
            ['group' => 'products', 'name' => 'enable_reviews', 'payload' => json_encode(true)],
            ['group' => 'products', 'name' => 'guest_reviews_allowed', 'payload' => json_encode(true)],
            ['group' => 'products', 'name' => 'review_stars_required', 'payload' => json_encode(true)],
            ['group' => 'products', 'name' => 'low_stock_threshold', 'payload' => json_encode(10)],

            // Checkout Settings
            ['group' => 'checkout', 'name' => 'enable_guest_checkout', 'payload' => json_encode(true)],
            ['group' => 'checkout', 'name' => 'tax_calculation_address', 'payload' => json_encode('shipping')],
            ['group' => 'checkout', 'name' => 'prices_include_tax', 'payload' => json_encode(false)],
            ['group' => 'checkout', 'name' => 'enabled_payment_gateways', 'payload' => json_encode(['cod', 'bank_transfer', 'credit_card'])],

            // Email Settings
            ['group' => 'emails', 'name' => 'sender_name', 'payload' => json_encode('Nova Club Store')],
            ['group' => 'emails', 'name' => 'sender_email', 'payload' => json_encode('orders@novaclub.vn')],
            ['group' => 'emails', 'name' => 'base_color', 'payload' => json_encode('#004687')],
            ['group' => 'emails', 'name' => 'notifications', 'payload' => json_encode(['new_order' => ['enabled' => true, 'recipients' => 'admin@admin.com']])],

            // Footer Settings
            ['group' => 'footer', 'name' => 'copyright', 'payload' => json_encode('© 2026 Nova Club Store. All rights reserved.')],
            ['group' => 'footer', 'name' => 'links', 'payload' => json_encode([
                ['title' => 'Về chúng tôi', 'url' => '/page/about-us'],
                ['title' => 'Chính sách vận chuyển', 'url' => '/page/shipping'],
                ['title' => 'Đổi trả & Bảo hành', 'url' => '/page/returns'],
                ['title' => 'FAQ', 'url' => '/page/faq'],
            ])],

            // Loyalty Settings
            ['group' => 'loyalty', 'name' => 'enabled', 'payload' => json_encode(true)],
            ['group' => 'loyalty', 'name' => 'points_per_amount', 'payload' => json_encode(1000)],
            ['group' => 'loyalty', 'name' => 'point_value', 'payload' => json_encode(10)],

            // Inventory Settings
            ['group' => 'inventory', 'name' => 'manage_stock', 'payload' => json_encode(true)],
            ['group' => 'inventory', 'name' => 'low_stock_threshold', 'payload' => json_encode(10)],
            ['group' => 'inventory', 'name' => 'notify_low_stock', 'payload' => json_encode(true)],

            // Marketing & Primary Colors
            ['group' => 'marketing', 'name' => 'primary_color', 'payload' => json_encode('#004687')],
            ['group' => 'marketing', 'name' => 'accent_color', 'payload' => json_encode('#FBAE17')],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['group' => $setting['group'], 'name' => $setting['name']],
                [
                    'payload' => $setting['payload'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $this->command?->info('Admin settings seeded: ' . count($settings) . ' items.');
    }
}
