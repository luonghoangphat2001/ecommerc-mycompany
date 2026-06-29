<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Brand;
use App\Models\ComboProduct;
use App\Models\ComboProductItem;
use App\Models\Comment;
use App\Models\Coupon;
use App\Models\CrossSellProduct;
use App\Models\Inventory;
use App\Models\LoyaltyLog;
use App\Models\LoyaltyPoint;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderCoupon;
use App\Models\OrderItem;
use App\Models\OrderShipping;
use App\Models\OrderTax;
use App\Models\Payment;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\TaxClass;
use App\Models\TaxRate;
use App\Models\UpsellProduct;
use App\Models\User;
use App\Settings\DBSettings;
use App\Settings\GeneralSettings;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Seeder;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ImportDatabaseSeeder extends Seeder
{
    protected ?TaxRate $defaultTaxRate = null;
    protected ?ShippingMethod $defaultShippingMethod = null;
    /** @var SupportCollection<int, Coupon> */
    protected SupportCollection $coupons;
    
    /** @var EloquentCollection<int, Inventory> */
    protected EloquentCollection $warehouses;

    public function run(): void
    {
        ini_set('memory_limit', '1024M');

        try {
            // Disable events và queue để tránh webhooks khi seeding
            Event::fake();
            Queue::fake();
            
            $this->cleanupDatabase();
            DB::beginTransaction();

            // 1. Core Settings
            $this->seedSettings();
            
            // 2. Admin & Customers
            $admin = $this->ensureAdminExists();
            $customers = $this->seedCustomers();
            
            // 3. Tax System (trước products để có thuế)
            $this->seedTaxSystem();
            
            // 4. Shipping System (trước orders)
            $this->seedShippingSystem();
            
            // 5. Inventory/Warehouses
            $this->warehouses = $this->seedInventories();
            
            // 6. Product Data
            $brands = $this->seedBrands();
            $categories = $this->seedCategories();
            $products = $this->seedProducts($brands, $categories, $customers);
            
            // 7. Inventory Stocks cho products
            $this->seedInventoryStocks($products);
            
            // 8. Coupons
            $this->coupons = $this->seedCoupons($products, $categories);
            
            // 9. Product Relationships (Upsell, Cross-sell, Combo)
            $this->seedProductRelationships($products);
            $this->seedComboProducts($products);
            
            // 10. Loyalty System
            $this->seedLoyaltyPoints($customers);
            
            // 11. Orders (có thuế, coupon, shipping đầy đủ)
            $this->seedOrders($products, $customers, $admin);
            
            // 12. Blog
            $this->seedBlog($customers);

            DB::commit();

            $this->command?->info('Import Database thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ImportDatabaseSeeder Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->command?->error('Lỗi Import Database: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function cleanupDatabase(): void
    {
        // Detect database driver for proper foreign key handling
        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        
        $tables = [
            // Product & Catalog
            'shop_brands', 'shop_categories', 'shop_category_product', 'shop_products',
            'shop_product_inventory_stocks', 'shop_stock_reservations',
            // Orders & Related
            'shop_orders', 'shop_order_items', 'shop_order_coupons', 'shop_order_taxes',
            'shop_order_shippings', 'shop_order_metas', 'shop_order_refunds', 'shop_order_addresses',
            'shop_payments',
            // Marketing
            'shop_coupons', 'shop_upsell_products', 'shop_cross_sell_products',
            'shop_combo_products', 'shop_combo_product_items',
            // Inventory
            'shop_inventories', 'shop_inventory_records', 'shop_inventory_record_items', 'shop_inventory_movements',
            // Tax & Shipping
            'shop_tax_rates', 'shop_tax_classes', 'shop_shipping_methods', 'shop_shipping_zones',
            // Blog
            'blog_categories', 'blog_posts', 'blog_category_post',
            // Comments & Reviews
            'comments', 'reviews',
            // Loyalty
            'shop_loyalty_points', 'shop_loyalty_logs',
            // Addresses
            'addresses', 'addressables',
            // Settings
            'settings', 'shop_settings',
        ];

        // Delete users and related data (keep super_admin) - MUST be before enabling FKs
        $nonAdminUserIds = User::whereDoesntHave('roles', fn($q) => $q->where('name', 'super_admin'))->pluck('id');
        if ($nonAdminUserIds->isNotEmpty()) {
            DB::table('model_has_roles')->whereIn('model_id', $nonAdminUserIds)->where('model_type', User::class)->delete();
            DB::table('model_has_permissions')->whereIn('model_id', $nonAdminUserIds)->where('model_type', User::class)->delete();
            DB::table('password_reset_tokens')->whereIn('email', function($q) use ($nonAdminUserIds) {
                $q->select('email')->from('users')->whereIn('id', $nonAdminUserIds);
            })->delete();
            DB::table('users')->whereIn('id', $nonAdminUserIds)->delete();
        }
        
        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->delete();
            }
        }
        
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    protected function seedSettings(): void
    {
        $this->command->info('Seeding Settings...');
        
        // Kiểm tra xem bảng settings đã có chưa
        $settingsTableExists = DB::getSchemaBuilder()->hasTable('settings');
        
        if ($settingsTableExists) {
            $now = now();
            
            // Insert GeneralSettings trực tiếp (format của Spatie Laravel Settings)
            DB::table('settings')->insert([
                ['group' => 'general', 'name' => 'store_name', 'locked' => 0, 'payload' => json_encode('My E-commerce'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'general', 'name' => 'store_email', 'locked' => 0, 'payload' => json_encode('admin@example.com'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'general', 'name' => 'store_phone', 'locked' => 0, 'payload' => json_encode('0123456789'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'general', 'name' => 'store_country', 'locked' => 0, 'payload' => json_encode('VN'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'general', 'name' => 'default_currency', 'locked' => 0, 'payload' => json_encode('VND'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'general', 'name' => 'currency_symbol', 'locked' => 0, 'payload' => json_encode('₫'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'general', 'name' => 'currency_position', 'locked' => 0, 'payload' => json_encode('right_space'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'general', 'name' => 'thousand_separator', 'locked' => 0, 'payload' => json_encode('.'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'general', 'name' => 'decimal_separator', 'locked' => 0, 'payload' => json_encode(','), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'general', 'name' => 'decimal_places', 'locked' => 0, 'payload' => json_encode(0), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'general', 'name' => 'weight_unit', 'locked' => 0, 'payload' => json_encode('kg'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'general', 'name' => 'dimension_unit', 'locked' => 0, 'payload' => json_encode('cm'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'general', 'name' => 'logo', 'locked' => 0, 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'general', 'name' => 'favicon', 'locked' => 0, 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
            ]);
            $this->command->info('  ✓ GeneralSettings seeded');
            
            // Insert DBSettings
            DB::table('settings')->insert([
                ['group' => 'settings', 'name' => 'logo', 'locked' => 0, 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'settings', 'name' => 'logo_favicon', 'locked' => 0, 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'settings', 'name' => 'name', 'locked' => 0, 'payload' => json_encode('My E-commerce'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'settings', 'name' => 'about', 'locked' => 0, 'payload' => json_encode('Hệ thống e-commerce hàng đầu Việt Nam'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'settings', 'name' => 'timezone', 'locked' => 0, 'payload' => json_encode('Asia/Ho_Chi_Minh'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'settings', 'name' => 'default_language', 'locked' => 0, 'payload' => json_encode('Vietnamese'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'settings', 'name' => 'currency', 'locked' => 0, 'payload' => json_encode('VND'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'settings', 'name' => 'currency_symbol', 'locked' => 0, 'payload' => json_encode('₫'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'settings', 'name' => 'new_user_role', 'locked' => 0, 'payload' => json_encode('Thành viên đăng ký'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'settings', 'name' => 'send_welcome_email', 'locked' => 0, 'payload' => json_encode(true), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'settings', 'name' => 'exchange_rates', 'locked' => 0, 'payload' => json_encode(['VND' => 1, 'USD' => 25000, 'EUR' => 27000]), 'created_at' => $now, 'updated_at' => $now],
            ]);
            $this->command->info('  ✓ DBSettings seeded');
            
            // Insert ProductSettings
            DB::table('settings')->insert([
                ['group' => 'products', 'name' => 'add_to_cart_behavior', 'locked' => 0, 'payload' => json_encode('ajax'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'products', 'name' => 'enable_reviews', 'locked' => 0, 'payload' => json_encode(true), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'products', 'name' => 'guest_reviews_allowed', 'locked' => 0, 'payload' => json_encode(false), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'products', 'name' => 'review_stars_required', 'locked' => 0, 'payload' => json_encode(true), 'created_at' => $now, 'updated_at' => $now],
            ]);
            $this->command->info('  ✓ ProductSettings seeded');
            
            // Insert CheckoutSettings
            DB::table('settings')->insert([
                ['group' => 'checkout', 'name' => 'enable_guest_checkout', 'locked' => 0, 'payload' => json_encode(true), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'checkout', 'name' => 'tax_calculation_address', 'locked' => 0, 'payload' => json_encode('shipping'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'checkout', 'name' => 'prices_include_tax', 'locked' => 0, 'payload' => json_encode(false), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'checkout', 'name' => 'enabled_payment_gateways', 'locked' => 0, 'payload' => json_encode(['cod', 'bank_transfer']), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'checkout', 'name' => 'stripe_public_key', 'locked' => 0, 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'checkout', 'name' => 'stripe_secret_key', 'locked' => 0, 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'checkout', 'name' => 'stripe_webhook_secret', 'locked' => 0, 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'checkout', 'name' => 'paypal_client_id', 'locked' => 0, 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'checkout', 'name' => 'paypal_secret', 'locked' => 0, 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'checkout', 'name' => 'paypal_mode', 'locked' => 0, 'payload' => json_encode('sandbox'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'checkout', 'name' => 'vnpay_tmn_code', 'locked' => 0, 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'checkout', 'name' => 'vnpay_hash_secret', 'locked' => 0, 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'checkout', 'name' => 'momo_partner_code', 'locked' => 0, 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'checkout', 'name' => 'momo_access_key', 'locked' => 0, 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'checkout', 'name' => 'momo_secret_key', 'locked' => 0, 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
            ]);
            $this->command->info('  ✓ CheckoutSettings seeded');
            
            // Insert EmailSettings
            DB::table('settings')->insert([
                ['group' => 'emails', 'name' => 'sender_name', 'locked' => 0, 'payload' => json_encode('My E-commerce'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'emails', 'name' => 'sender_email', 'locked' => 0, 'payload' => json_encode('noreply@myecommerce.com'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'emails', 'name' => 'base_color', 'locked' => 0, 'payload' => json_encode('#3b82f6'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'emails', 'name' => 'notifications', 'locked' => 0, 'payload' => json_encode(['new_order' => ['enabled' => true, 'recipients' => 'admin@example.com', 'template' => 'new_order']]), 'created_at' => $now, 'updated_at' => $now],
            ]);
            $this->command->info('  ✓ EmailSettings seeded');
            
            // Insert AdvancedSettings
            DB::table('settings')->insert([
                ['group' => 'advanced', 'name' => 'cart_page_id', 'locked' => 0, 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'advanced', 'name' => 'checkout_page_id', 'locked' => 0, 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'advanced', 'name' => 'account_page_id', 'locked' => 0, 'payload' => json_encode(null), 'created_at' => $now, 'updated_at' => $now],
            ]);
            $this->command->info('  ✓ AdvancedSettings seeded');
            
            // Insert ApiSettings
            DB::table('settings')->insert([
                ['group' => 'api', 'name' => 'idempotency_ttl', 'locked' => 0, 'payload' => json_encode(86400), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'api', 'name' => 'hmac_secret', 'locked' => 0, 'payload' => json_encode(''), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'api', 'name' => 'enabled', 'locked' => 0, 'payload' => json_encode(true), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'api', 'name' => 'allowed_roles', 'locked' => 0, 'payload' => json_encode([]), 'created_at' => $now, 'updated_at' => $now],
            ]);
            $this->command->info('  ✓ ApiSettings seeded');
            
            // Insert WebhookSettings
            DB::table('settings')->insert([
                ['group' => 'webhook', 'name' => 'enabled', 'locked' => 0, 'payload' => json_encode(false), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'webhook', 'name' => 'log_retention_days', 'locked' => 0, 'payload' => json_encode(30), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'webhook', 'name' => 'allowed_roles', 'locked' => 0, 'payload' => json_encode([]), 'created_at' => $now, 'updated_at' => $now],
            ]);
            $this->command->info('  ✓ WebhookSettings seeded');
            
            // Insert CouponSettings
            DB::table('settings')->insert([
                ['group' => 'coupon', 'name' => 'enable_coupons', 'locked' => 0, 'payload' => json_encode(true), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'coupon', 'name' => 'allow_multiple_coupons', 'locked' => 0, 'payload' => json_encode(false), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'coupon', 'name' => 'calculate_tax_after_coupon', 'locked' => 0, 'payload' => json_encode(true), 'created_at' => $now, 'updated_at' => $now],
            ]);
            $this->command->info('  ✓ CouponSettings seeded');
            
            // Insert LoyaltySettings
            DB::table('settings')->insert([
                ['group' => 'loyalty', 'name' => 'enabled', 'locked' => 0, 'payload' => json_encode(true), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'loyalty', 'name' => 'points_per_currency', 'locked' => 0, 'payload' => json_encode(1), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'loyalty', 'name' => 'point_conversion_rate', 'locked' => 0, 'payload' => json_encode(1000), 'created_at' => $now, 'updated_at' => $now],
            ]);
            $this->command->info('  ✓ LoyaltySettings seeded');
            
            // Insert InventorySettings
            DB::table('settings')->insert([
                ['group' => 'inventory', 'name' => 'multi_warehouse_enabled', 'locked' => 0, 'payload' => json_encode(true), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'inventory', 'name' => 'split_shipping_enabled', 'locked' => 0, 'payload' => json_encode(false), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'inventory', 'name' => 'warehouse_selection_strategy', 'locked' => 0, 'payload' => json_encode('stock_volume'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'inventory', 'name' => 'reservation_expiry_minutes', 'locked' => 0, 'payload' => json_encode(15), 'created_at' => $now, 'updated_at' => $now],
            ]);
            $this->command->info('  ✓ InventorySettings seeded');
            
            // Insert MarketingSettings
            DB::table('settings')->insert([
                ['group' => 'marketing', 'name' => 'upsell_enabled', 'locked' => 0, 'payload' => json_encode(true), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'marketing', 'name' => 'cross_sell_enabled', 'locked' => 0, 'payload' => json_encode(true), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'marketing', 'name' => 'combo_enabled', 'locked' => 0, 'payload' => json_encode(true), 'created_at' => $now, 'updated_at' => $now],
            ]);
            $this->command->info('  ✓ MarketingSettings seeded');
            
            // Insert FooterSettings
            DB::table('settings')->insert([
                ['group' => 'footer', 'name' => 'copyright', 'locked' => 0, 'payload' => json_encode('© 2025 My E-commerce'), 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'footer', 'name' => 'links', 'locked' => 0, 'payload' => json_encode([]), 'created_at' => $now, 'updated_at' => $now],
            ]);
            $this->command->info('  ✓ FooterSettings seeded');
        } else {
            $this->command->warn('Settings table chưa tồn tại');
        }
        
        // Legacy settings nếu có
        if (DB::getSchemaBuilder()->hasTable('shop_settings')) {
            DB::table('shop_settings')->insert([
                ['key' => 'store_name', 'value' => json_encode('My E-commerce'), 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'store_email', 'value' => json_encode('contact@myecommerce.com'), 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'store_phone', 'value' => json_encode('1900 1234'), 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'tax_enabled', 'value' => json_encode(true), 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        
    }

    protected function seedTaxSystem(): void
    {
        $this->command->info('Seeding Tax System...');
        
        // Tax Classes
        $standardClass = TaxClass::create(['name' => 'Thuế chuẩn', 'slug' => 'standard']);
        $reducedClass = TaxClass::create(['name' => 'Thuế giảm', 'slug' => 'reduced']);
        $zeroClass = TaxClass::create(['name' => 'Không thuế', 'slug' => 'zero']);
        
        // Tax Rates cho Việt Nam
        $this->defaultTaxRate = TaxRate::create([
            'tax_class_id' => $standardClass->id,
            'country' => 'VN',
            'state' => null,
            'city' => null,
            'rate' => 10.0000, // VAT 10%
            'name' => 'VAT 10%',
            'priority' => 1,
            'is_compound' => false,
            'is_shipping' => true,
        ]);
        
        TaxRate::create([
            'tax_class_id' => $reducedClass->id,
            'country' => 'VN',
            'state' => null,
            'city' => null,
            'rate' => 5.0000, // VAT 5%
            'name' => 'VAT 5%',
            'priority' => 1,
            'is_compound' => false,
            'is_shipping' => true,
        ]);
        
        TaxRate::create([
            'tax_class_id' => $zeroClass->id,
            'country' => 'VN',
            'rate' => 0.0000,
            'name' => 'Không thuế 0%',
            'priority' => 1,
            'is_shipping' => true,
        ]);
    }

    protected function seedShippingSystem(): void
    {
        $this->command->info('Seeding Shipping System...');
        
        // Shipping Zones (format cho Repeater: ['country', 'provinces'])
        $allZones = ShippingZone::create([
            'name' => 'Toàn quốc',
            'locations' => [['country' => 'VN', 'provinces' => []]],
            'sort' => 0,
        ]);
        
        $hnHcmZone = ShippingZone::create([
            'name' => 'Hà Nội & TP.HCM',
            'locations' => [['country' => 'VN', 'provinces' => ['VN-HN', 'VN-SG']]],
            'sort' => 1,
        ]);
        
        // Shipping Methods (Model có cast 'settings' => 'array' nên không cần json_encode)
        $this->defaultShippingMethod = ShippingMethod::create([
            'shipping_zone_id' => $allZones->id,
            'type' => 'flat_rate',
            'name' => 'Giao hàng tiêu chuẩn',
            'settings' => ['cost' => 30000, 'min_amount' => 0],
            'is_enabled' => true,
        ]);
        
        ShippingMethod::create([
            'shipping_zone_id' => $allZones->id,
            'type' => 'free_shipping',
            'name' => 'Miễn phí vận chuyển',
            'settings' => ['min_amount' => 500000],
            'is_enabled' => true,
        ]);
        
        ShippingMethod::create([
            'shipping_zone_id' => $hnHcmZone->id,
            'type' => 'flat_rate',
            'name' => 'Giao nhanh 2h',
            'settings' => ['cost' => 50000],
            'is_enabled' => true,
        ]);
    }

    protected function seedInventories(): EloquentCollection
    {
        $this->command->info('Seeding Inventories...');
        
        $warehouses = collect([
            ['name' => 'Kho chính Hà Nội', 'slug' => 'kho-chinh-ha-noi', 'location' => 'Số 1, Đường ABC, Hà Nội', 'is_active' => true],
            ['name' => 'Kho TP.HCM', 'slug' => 'kho-tp-hcm', 'location' => 'Số 2, Đường XYZ, TP.HCM', 'is_active' => true],
            ['name' => 'Kho Đà Nẵng', 'slug' => 'kho-da-nang', 'location' => 'Số 3, Đường DEF, Đà Nẵng', 'is_active' => true],
        ])->map(fn($data) => Inventory::create($data));
        
        return new EloquentCollection($warehouses);
    }

    /**
     * @param EloquentCollection<int, Product> $products
     */
    protected function seedInventoryStocks(EloquentCollection $products): void
    {
        $this->command->info('Seeding Inventory Stocks...');
        
        foreach ($products as $product) {
            foreach ($this->warehouses as $warehouse) {
                DB::table('shop_product_inventory_stocks')->insert([
                    'shop_product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'stock_quantity' => rand(50, 500),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * @param EloquentCollection<int, Product> $products
     * @param EloquentCollection<int, ProductCategory> $categories
     * @return SupportCollection<int, Coupon>
     */
    protected function seedCoupons(EloquentCollection $products, EloquentCollection $categories): SupportCollection
    {
        $this->command->info('Seeding Coupons...');
        
        $coupons = collect();
        
        // Fixed cart discount
        $coupons->push(Coupon::factory()->fixedCart(50000)->create([
            'code' => 'SALE50K',
            'minimum_order_amount' => 200000,
            'expiry_date' => now()->addMonths(1),
        ]));
        
        // Percentage discount
        $coupons->push(Coupon::factory()->percentage(20)->create([
            'code' => 'SALE20',
            'minimum_order_amount' => 500000,
            'usage_limit' => 100,
            'expiry_date' => now()->addMonths(2),
        ]));
        
        // Product specific coupon
        $coupons->push(Coupon::factory()->fixedCart(100000)->create([
            'code' => 'VIP100',
            'product_ids' => [$products->random()->id],
            'minimum_order_amount' => 500000,
        ]));
        
        // Category specific coupon
        $coupons->push(Coupon::factory()->percentage(15)->create([
            'code' => 'CATE15',
            'category_ids' => [$categories->random()->id],
            'expiry_date' => now()->addWeeks(2),
        ]));
        
        // Expired coupon (để test)
        $coupons->push(Coupon::factory()->expired()->create([
            'code' => 'EXPIRED',
            'amount' => 50000,
        ]));
        
        // Limited usage coupon
        $coupons->push(Coupon::factory()->limitedUsage(10)->create([
            'code' => 'LIMITED10',
            'amount' => 30000,
        ]));
        
        // First time buyer coupon
        $coupons->push(Coupon::factory()->fixedCart(20000)->create([
            'code' => 'WELCOME',
            'usage_limit_per_user' => 1,
            'individual_use' => true,
        ]));
        
        return $coupons;
    }

    /**
     * @param EloquentCollection<int, Product> $products
     */
    protected function seedProductRelationships(EloquentCollection $products): void
    {
        $this->command->info('Seeding Upsell & Cross-sell...');
        
        $productIds = $products->pluck('id')->toArray();
        
        // Upsell products (nâng cấp)
        foreach ($products->random(15) as $product) {
            $upsellIds = array_diff($productIds, [$product->id]);
            if (empty($upsellIds)) continue;
            
            UpsellProduct::create([
                'shop_product_id' => $product->id,
                'upsell_product_id' => $upsellIds[array_rand($upsellIds)],
                'sort_order' => rand(0, 10),
                'is_active' => true,
            ]);
        }
        
        // Cross-sell products (mua kèm)
        foreach ($products->random(20) as $product) {
            $crossIds = array_diff($productIds, [$product->id]);
            if (empty($crossIds)) continue;
            
            CrossSellProduct::create([
                'shop_product_id' => $product->id,
                'cross_sell_product_id' => $crossIds[array_rand($crossIds)],
                'sort_order' => rand(0, 10),
                'is_active' => true,
            ]);
        }
    }

    /**
     * @param EloquentCollection<int, Product> $products
     */
    protected function seedComboProducts(EloquentCollection $products): void
    {
        $this->command->info('Seeding Combo Products...');
        
        $comboData = [
            ['name' => 'Combo Khởi Nghiệp', 'slug' => 'combo-khoi-nghiep', 'combo_price' => 500000, 'discount_percent' => 15],
            ['name' => 'Combo Gia Đình', 'slug' => 'combo-gia-dinh', 'combo_price' => 1200000, 'discount_percent' => 20],
            ['name' => 'Combo Văn Phòng', 'slug' => 'combo-van-phong', 'combo_price' => 800000, 'discount_percent' => 18],
            ['name' => 'Combo Học Tập', 'slug' => 'combo-hoc-tap', 'combo_price' => 600000, 'discount_percent' => 12],
            ['name' => 'Combo Du Lịch', 'slug' => 'combo-du-lich', 'combo_price' => 1500000, 'discount_percent' => 25],
        ];
        
        foreach ($comboData as $data) {
            $combo = ComboProduct::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => 'Combo tiết kiệm với nhiều ưu đãi hấp dẫn',
                'combo_price' => $data['combo_price'],
                'original_price' => 0, // Will update after adding items
                'discount_percent' => $data['discount_percent'],
                'is_active' => true,
                'start_date' => now()->subDays(7),
                'end_date' => now()->addMonths(3),
                'sort_order' => rand(0, 100),
            ]);
            
            $comboProducts = $products->random(rand(2, 4));
            $totalOriginal = 0;
            
            foreach ($comboProducts as $index => $product) {
                ComboProductItem::create([
                    'combo_product_id' => $combo->id,
                    'shop_product_id' => $product->id,
                    'quantity' => rand(1, 2),
                    'sort_order' => $index,
                ]);
                $totalOriginal += ($product->price ?? 100000) * rand(1, 2);
            }
            
            // Cập nhật giá gốc và phần trăm giảm
            $combo->update([
                'original_price' => $totalOriginal,
                'discount_percent' => (int) ((($totalOriginal - $combo->combo_price) / $totalOriginal) * 100),
            ]);
        }
    }

    /**
     * @param EloquentCollection<int, User> $customers
     */
    protected function seedLoyaltyPoints(EloquentCollection $customers): void
    {
        $this->command->info('Seeding Loyalty Points...');
        
        foreach ($customers as $customer) {
            $currentPoints = rand(0, 5000);
            $lifetimePoints = $currentPoints + rand(0, 2000);
            
            LoyaltyPoint::create([
                'user_id' => $customer->id,
                'current_points' => $currentPoints,
                'lifetime_points' => $lifetimePoints,
            ]);
            
            // Tạo logs
            $actions = ['earn', 'earn', 'earn', 'redeem'];
            $numLogs = rand(1, 4);
            for ($i = 0; $i < $numLogs; $i++) {
                $action = $actions[$i];
                LoyaltyLog::create([
                    'user_id' => $customer->id,
                    'points_changed' => $action === 'earn' ? rand(100, 500) : -rand(50, 200),
                    'action_type' => $action,
                    'order_id' => null,
                    'expired_at' => $action === 'earn' ? now()->addYear() : null,
                    'created_at' => now()->subDays(rand(1, 90)),
                ]);
            }
        }
    }

    protected function ensureAdminExists(): User
    {
        return User::role('super_admin')->first() ?? User::factory()->create()->assignRole('super_admin');
    }

    protected function seedCustomers(): EloquentCollection
    {
        $this->command->warn('Seeding customers...');
        return User::factory(20)->create()->each(fn($u) => $u->assignRole('Thành viên đăng ký'));
    }

    protected function seedBrands(): EloquentCollection
    {
        $this->command->warn('Seeding brands...');
        return Brand::factory(10)
            ->has(Address::factory()->count(1))
            ->create();
    }

    protected function seedCategories(): EloquentCollection
    {
        $this->command->warn('Seeding categories...');
        return ProductCategory::factory(5)
            ->has(ProductCategory::factory()->count(2), 'children')
            ->create();
    }

    /**
     * @param EloquentCollection<int, Brand> $brands
     * @param EloquentCollection<int, ProductCategory> $categories
     * @param EloquentCollection<int, User> $customers
     */
    protected function seedProducts(EloquentCollection $brands, EloquentCollection $categories, EloquentCollection $customers): EloquentCollection
    {
        $this->command->warn('Seeding products...');
        return Product::factory(40)
            ->create()
            ->each(function ($product) use ($brands, $categories, $customers) {
                $product->update(['shop_brand_id' => $brands->random()->id]);
                $product->categories()->attach($categories->random(rand(1, 2))->pluck('id'));
                Comment::factory(rand(1, 3))->create([
                    'commentable_type' => Product::class,
                    'commentable_id' => $product->id,
                    'user_id' => $customers->random()->id,
                ]);
            });
    }

    protected function seedOrders(EloquentCollection $products, EloquentCollection $customers, User $admin): void
    {
        $this->command->info('Seeding Orders (có thuế, shipping, coupon)...');
        
        $statuses = ['new', 'processing', 'completed', 'cancelled', 'refunded'];
        $statusWeights = [20, 25, 40, 10, 5]; // Tỷ lệ phân bổ
        
        $orders = Order::factory(50)->make()->each(function ($order) use ($customers, $statuses, $statusWeights) {
            $customer = $customers->random();
            $order->user_id = $customer->id;
            $order->status = $this->weightedRandomChoice($statuses, $statusWeights);
            $order->currency = 'VND';
            $order->exchange_rate = 1.0;
        });
        
        foreach ($orders as $order) {
            $order->save();
            $this->createOrderDetails($order, $products, $customers);
        }
    }
    
    /**
     * @param EloquentCollection<int, Product> $products
     * @param EloquentCollection<int, User> $customers
     */
    protected function createOrderDetails(Order $order, EloquentCollection $products, EloquentCollection $customers): void
    {
        // 1. Tạo Order Items
        $selectedProducts = $products->random(rand(1, 4));
        $subtotal = 0;
        
        foreach ($selectedProducts as $product) {
            $qty = rand(1, 3);
            $unitPrice = $product->price ?? 100000;
            $itemTotal = $unitPrice * $qty;
            $subtotal += $itemTotal;
            
            OrderItem::create([
                'order_id' => $order->id,
                'shop_product_id' => $product->id,
                'type' => 'product',
                'name' => $product->getTranslation('name', 'vi') ?? $product->name ?? 'Sản phẩm',
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'total' => $itemTotal,
                'metadata' => json_encode(['sku' => $product->sku ?? '']),
            ]);
        }
        
        // 2. Tính thuế VAT 10%
        $taxRate = 0.10;
        $taxAmount = (int) round($subtotal * $taxRate);
        
        // Tạo OrderTax records
        if ($this->defaultTaxRate) {
            OrderTax::create([
                'order_id' => $order->id,
                'shop_order_item_id' => null,
                'is_shipping' => false,
                'shop_tax_rate_id' => $this->defaultTaxRate->id,
                'name' => 'VAT 10%',
                'amount' => $taxAmount,
                'metadata' => json_encode(['rate' => 10, 'base' => $subtotal]),
            ]);
        }
        
        // 3. Tạo Shipping
        $shippingAmount = $subtotal >= 500000 ? 0 : 30000; // Free shipping >= 500k
        OrderShipping::create([
            'order_id' => $order->id,
            'shop_shipping_method_id' => $this->defaultShippingMethod?->id,
            'method' => $shippingAmount === 0 ? 'Miễn phí vận chuyển' : 'Giao hàng tiêu chuẩn',
            'amount' => $shippingAmount,
            'tax_amount' => (int) round($shippingAmount * $taxRate),
            'tracking_number' => 'VN' . rand(100000000, 999999999) . 'VN',
            'metadata' => json_encode(['estimated_days' => 3]),
        ]);
        
        // 4. Áp dụng Coupon (30% đơn hàng có coupon)
        $discountAmount = 0;
        $appliedCoupon = null;
        
        if (rand(1, 100) <= 30 && $this->coupons->isNotEmpty()) {
            $validCoupons = $this->coupons->filter(fn($c) => 
                $c->is_active && 
                ($c->expiry_date === null || $c->expiry_date > now()) &&
                ($c->minimum_order_amount === null || $subtotal >= $c->minimum_order_amount)
            );
            
            if ($validCoupons->isNotEmpty()) {
                $appliedCoupon = $validCoupons->random();
                
                // Tính discount
                if ($appliedCoupon->type === 'percentage') {
                    $discountAmount = (int) round($subtotal * ($appliedCoupon->amount / 100));
                } else {
                    $discountAmount = min($appliedCoupon->amount, $subtotal * 0.5); // Max 50% subtotal
                }
                
                // Lưu order coupon
                OrderCoupon::create([
                    'order_id' => $order->id,
                    'coupon_code' => $appliedCoupon->code,
                    'discount_amount' => $discountAmount,
                ]);
                
                // Tăng usage count
                $appliedCoupon->increment('usage_count');
            }
        }
        
        // 5. Tạo địa chỉ
        OrderAddress::factory()->create([
            'addressable_type' => Order::class,
            'addressable_id' => $order->id,
            'type' => 'shipping',
        ]);
        
        OrderAddress::factory()->create([
            'addressable_type' => Order::class,
            'addressable_id' => $order->id,
            'type' => 'billing',
        ]);
        
        // 6. Tạo Payment
        $paymentStatus = match($order->status) {
            'new' => 'pending',
            'processing', 'completed' => 'completed',
            'cancelled' => 'failed',
            'refunded' => 'refunded',
            default => 'pending',
        };
        
        Payment::factory()->create([
            'order_id' => $order->id,
            'status' => $paymentStatus,
            'amount' => $subtotal + $taxAmount + $shippingAmount - $discountAmount,
        ]);
        
        // 7. Cập nhật Order totals
        $total = $subtotal + $taxAmount + $shippingAmount - $discountAmount;
        
        $order->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => max(0, $total),
        ]);
        
        // 8. Cập nhật Loyalty Points cho đơn hàng completed (1 point = 1000 VND)
        if ($order->status === 'completed' && $order->user_id) {
            $pointsEarned = (int) ($subtotal / 1000);
            
            LoyaltyLog::create([
                'user_id' => $order->user_id,
                'points_changed' => $pointsEarned,
                'action_type' => 'earn',
                'order_id' => $order->id,
                'expired_at' => now()->addYear(),
            ]);
            
            // Cập nhật hoặc tạo loyalty point record
            $loyaltyPoint = LoyaltyPoint::firstOrCreate(
                ['user_id' => $order->user_id],
                ['current_points' => 0, 'lifetime_points' => 0]
            );
            
            $loyaltyPoint->increment('current_points', $pointsEarned);
            $loyaltyPoint->increment('lifetime_points', $pointsEarned);
        }
    }
    
    protected function weightedRandomChoice(array $items, array $weights): string
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($items as $index => $item) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $item;
            }
        }
        
        return $items[0];
    }

    /**
     * @param EloquentCollection<int, User> $customers
     */
    protected function seedBlog(EloquentCollection $customers): void
    {
        $this->command->warn('Seeding blog...');
        $categories = PostCategory::factory(5)->create();
        Post::factory(20)->create()->each(function ($post) use ($categories, $customers) {
            $post->categories()->attach($categories->random(rand(1, 2))->pluck('id'));
            Comment::factory(rand(2, 5))->create([
                'commentable_type' => Post::class,
                'commentable_id' => $post->id,
                'user_id' => $customers->random()->id,
            ]);
        });
    }
}
