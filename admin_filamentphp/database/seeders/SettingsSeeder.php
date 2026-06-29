<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        
        // Clear existing settings
        DB::table('settings')->delete();
        
        // General Settings
        $generalSettings = [
            ['group' => 'general', 'name' => 'store_name', 'payload' => json_encode('My E-commerce'), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'general', 'name' => 'store_email', 'payload' => json_encode('contact@myecommerce.com'), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'general', 'name' => 'store_phone', 'payload' => json_encode('1900 1234'), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'general', 'name' => 'store_country', 'payload' => json_encode('VN'), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'general', 'name' => 'default_currency', 'payload' => json_encode('VND'), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'general', 'name' => 'currency_symbol', 'payload' => json_encode('₫'), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'general', 'name' => 'currency_position', 'payload' => json_encode('right'), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'general', 'name' => 'thousand_separator', 'payload' => json_encode('.'), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'general', 'name' => 'decimal_separator', 'payload' => json_encode(','), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'general', 'name' => 'decimal_places', 'payload' => json_encode(0), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'general', 'name' => 'weight_unit', 'payload' => json_encode('kg'), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'general', 'name' => 'dimension_unit', 'payload' => json_encode('cm'), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'general', 'name' => 'logo', 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'general', 'name' => 'favicon', 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
        ];
        
        DB::table('settings')->insert($generalSettings);
        $this->command->info('GeneralSettings seeded: ' . count($generalSettings) . ' items');
        
        // Other settings groups
        $otherSettings = [
            // Product Settings (group must be 'products' to match ProductSettings::group())
            ['group' => 'products', 'name' => 'items_per_page', 'payload' => json_encode(12), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'products', 'name' => 'add_to_cart_behavior', 'payload' => json_encode('stay'), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'products', 'name' => 'enable_reviews', 'payload' => json_encode(true), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'products', 'name' => 'guest_reviews_allowed', 'payload' => json_encode(true), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'products', 'name' => 'review_stars_required', 'payload' => json_encode(true), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'products', 'name' => 'low_stock_threshold', 'payload' => json_encode(5), 'created_at' => $now, 'updated_at' => $now],
            
            // Checkout Settings
            ['group' => 'checkout', 'name' => 'enable_guest_checkout', 'payload' => json_encode(true), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'checkout', 'name' => 'tax_calculation_address', 'payload' => json_encode('shipping'), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'checkout', 'name' => 'prices_include_tax', 'payload' => json_encode(false), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'checkout', 'name' => 'enabled_payment_gateways', 'payload' => json_encode(['cod', 'bank_transfer']), 'created_at' => $now, 'updated_at' => $now],
            
            // Email Settings
            ['group' => 'emails', 'name' => 'sender_name', 'payload' => json_encode('My E-commerce'), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'emails', 'name' => 'sender_email', 'payload' => json_encode('noreply@myecommerce.com'), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'emails', 'name' => 'base_color', 'payload' => json_encode('#3b82f6'), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'emails', 'name' => 'notifications', 'payload' => json_encode(['new_order' => ['enabled' => true, 'recipients' => 'admin@example.com']]), 'created_at' => $now, 'updated_at' => $now],
            
            // Footer Settings
            ['group' => 'footer', 'name' => 'copyright', 'payload' => json_encode('© 2025 My E-commerce'), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'footer', 'name' => 'links', 'payload' => json_encode([]), 'created_at' => $now, 'updated_at' => $now],
            
            // Advanced Settings
            ['group' => 'advanced', 'name' => 'cart_page_id', 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'advanced', 'name' => 'checkout_page_id', 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'advanced', 'name' => 'account_page_id', 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
            
            // API Settings
            ['group' => 'api', 'name' => 'idempotency_ttl', 'payload' => json_encode(86400), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'api', 'name' => 'hmac_secret', 'payload' => json_encode(''), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'api', 'name' => 'enabled', 'payload' => json_encode(true), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'api', 'name' => 'allowed_roles', 'payload' => json_encode([]), 'created_at' => $now, 'updated_at' => $now],
            
            // Webhook Settings
            ['group' => 'webhook', 'name' => 'enabled', 'payload' => json_encode(true), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'webhook', 'name' => 'log_retention_days', 'payload' => json_encode(30), 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'webhook', 'name' => 'allowed_roles', 'payload' => json_encode([]), 'created_at' => $now, 'updated_at' => $now],
        ];
        
        DB::table('settings')->insert($otherSettings);
        $this->command->info('Other settings seeded: ' . count($otherSettings) . ' items');
        
        $this->command->info('Total settings: ' . (count($generalSettings) + count($otherSettings)));
    }
}
