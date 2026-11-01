<?php

namespace Tests\Feature;

use App\Ecommerce\Audit\Contracts\FinancialAuditServiceInterface;
use App\Ecommerce\Checkout\Actions\PlaceOrderAction;
use App\Ecommerce\Coupon\Contracts\CouponServiceInterface;
use App\Ecommerce\Order\DTOs\Order\OrderDataDTO;
use App\Ecommerce\Product\Contracts\TaxServiceInterface;
use App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface;
use App\Ecommerce\Shipping\Contracts\ShippingServiceInterface;
use App\Enums\ProductType;
use App\Enums\TaxRateStatus;
use App\Exceptions\CouponValidationException;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\TaxClass;
use App\Models\TaxRate;
use App\Models\User;
use App\Settings\CheckoutSettings;
use App\Settings\EcommerceSettings;
use App\Settings\InventorySettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class SystemSettingsEnforcementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected FeaturePolicyServiceInterface $featurePolicy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::firstOrCreate(
            ['email' => 'test-settings-enforcement@example.com'],
            ['name' => 'Settings Tester', 'password' => bcrypt('secret123')]
        );

        $this->featurePolicy = app(FeaturePolicyServiceInterface::class);
        $this->featurePolicy->flushCache();

        // Ensure baseline tax classes exist
        $std = TaxClass::firstOrCreate(['slug' => 'standard'], ['name' => 'Standard VAT']);
        TaxRate::firstOrCreate(
            ['tax_class_id' => $std->id, 'country' => 'VN'],
            ['name' => 'VAT 10%', 'rate' => 10.0, 'priority' => 1, 'is_compound' => false, 'is_shipping' => true]
        );

        // Ensure baseline shipping exists
        $zone = ShippingZone::firstOrCreate(['code' => 'VN_TEST'], ['name' => 'Vietnam Test Zone', 'is_active' => true]);
        ShippingMethod::firstOrCreate(
            ['type' => 'flat_rate', 'shipping_zone_id' => $zone->id],
            ['name' => 'Standard Rate', 'settings' => ['cost' => 30000], 'is_enabled' => true]
        );
    }

    protected function tearDown(): void
    {
        $this->featurePolicy->flushCache();
        parent::tearDown();
    }

    /**
     * Test 1: FeaturePolicyService provides authoritative defaults and can be toggled.
     */
    public function test_feature_policy_service_returns_authoritative_states(): void
    {
        $this->assertTrue($this->featurePolicy->isEcommerceEnabled());
        $this->assertFalse($this->featurePolicy->isCatalogMode());

        $summary = $this->featurePolicy->getFeatureSummary();
        $this->assertIsArray($summary);
        $this->assertArrayHasKey('ecommerce_enabled', $summary);
        $this->assertArrayHasKey('inventory_tracking', $summary);
        $this->assertArrayHasKey('tax_calculation', $summary);
        $this->assertArrayHasKey('shipping_calculation', $summary);
    }

    /**
     * Test 2: Inventory Tracking DISABLED allows checkout with zero stock and bypasses decrementing stock.
     */
    public function test_inventory_disabled_allows_zero_stock_checkout_and_does_not_decrement_stock(): void
    {
        $invSettings = app(InventorySettings::class);
        $origManageStock = $invSettings->manage_stock;
        $invSettings->manage_stock = false;
        $invSettings->save();
        $this->featurePolicy->flushCache();

        $product = Product::create([
            'name' => 'Zero Stock Product',
            'slug' => 'zero-stock-' . uniqid(),
            'sku' => 'ZERO-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 100000,
            'qty' => 0,
            'total_stock' => 0,
            'is_visible' => true,
            'apply_tax' => false,
        ]);

        $dto = new OrderDataDTO(
            customerId: $this->user->id,
            email: 'tester@example.com',
            phone: '0901234567',
            shippingAddress: ['first_name' => 'Tester', 'last_name' => 'User', 'phone' => '0901234567', 'country' => 'VN', 'street' => '123 Test St', 'city' => 'Hanoi'],
            billingAddress: ['first_name' => 'Tester', 'last_name' => 'User', 'phone' => '0901234567', 'country' => 'VN', 'street' => '123 Test St', 'city' => 'Hanoi'],
            items: [
                ['product_id' => $product->id, 'qty' => 2, 'price' => 100000],
            ],
            shippingMethod: 'flat_rate',
            paymentMethod: 'cod',
            grandTotal: 235000 // 200,000 subtotal + 35,000 flat shipping
        );

        $action = app(PlaceOrderAction::class);
        $order = $action->execute($dto);

        $this->assertInstanceOf(Order::class, $order);
        $product->refresh();
        $this->assertEquals(0, $product->qty, 'Stock must not decrement below zero when tracking is disabled');
        $this->assertEquals(0, $product->total_stock);

        // Restore
        $invSettings->manage_stock = $origManageStock;
        $invSettings->save();
        $this->featurePolicy->flushCache();
    }

    /**
     * Test 3: Inventory Tracking ENABLED blocks order placement when stock is insufficient.
     */
    public function test_inventory_enabled_blocks_checkout_on_insufficient_stock(): void
    {
        $invSettings = app(InventorySettings::class);
        $origManageStock = $invSettings->manage_stock;
        $invSettings->manage_stock = true;
        $invSettings->save();
        $this->featurePolicy->flushCache();

        $product = Product::create([
            'name' => 'Limited Stock Product',
            'slug' => 'ltd-stock-' . uniqid(),
            'sku' => 'LTD-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 50000,
            'qty' => 1,
            'total_stock' => 1,
            'is_visible' => true,
            'apply_tax' => false,
        ]);

        $dto = new OrderDataDTO(
            customerId: $this->user->id,
            email: 'tester@example.com',
            phone: '0901234567',
            shippingAddress: ['first_name' => 'Tester', 'last_name' => 'User', 'phone' => '0901234567', 'country' => 'VN', 'street' => '123 St', 'city' => 'Hanoi'],
            billingAddress: ['first_name' => 'Tester', 'last_name' => 'User', 'phone' => '0901234567', 'country' => 'VN', 'street' => '123 St', 'city' => 'Hanoi'],
            items: [
                ['product_id' => $product->id, 'qty' => 5, 'price' => 50000],
            ],
            shippingMethod: 'flat_rate',
            paymentMethod: 'cod',
            grandTotal: 280000
        );

        $this->expectException(ValidationException::class);
        $action = app(PlaceOrderAction::class);
        $action->execute($dto);

        // Restore
        $invSettings->manage_stock = $origManageStock;
        $invSettings->save();
        $this->featurePolicy->flushCache();
    }

    /**
     * Test 4: Tax Calculation DISABLED returns 0 tax and does not add tax charges.
     */
    public function test_tax_disabled_returns_zero_tax(): void
    {
        $ecomSettings = app(EcommerceSettings::class);
        $checkoutSettings = app(CheckoutSettings::class);
        $origEcomTax = $ecomSettings->tax_enabled;
        $origCheckoutTax = $checkoutSettings->enable_tax;

        $ecomSettings->tax_enabled = false;
        $ecomSettings->save();
        $checkoutSettings->enable_tax = false;
        $checkoutSettings->save();
        $this->featurePolicy->flushCache();

        $product = Product::create([
            'name' => 'Taxable Product',
            'slug' => 'taxable-' . uniqid(),
            'sku' => 'TAX-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 100000,
            'qty' => 10,
            'total_stock' => 10,
            'is_visible' => true,
            'apply_tax' => true,
            'tax_class_id' => TaxClass::where('slug', 'standard')->first()?->id,
        ]);

        $taxService = app(TaxServiceInterface::class);
        $calculation = $taxService->calculateProductTax($product, null, 100000, 'VN');

        $this->assertEquals(0, $calculation['amount']);
        $this->assertEquals(TaxRateStatus::TaxDisabled->value, $calculation['status']);

        // Restore
        $ecomSettings->tax_enabled = $origEcomTax;
        $ecomSettings->save();
        $checkoutSettings->enable_tax = $origCheckoutTax;
        $checkoutSettings->save();
        $this->featurePolicy->flushCache();
    }

    /**
     * Test 5: Shipping DISABLED returns empty available shipping methods.
     */
    public function test_shipping_disabled_returns_empty_methods(): void
    {
        $ecomSettings = app(EcommerceSettings::class);
        $checkoutSettings = app(CheckoutSettings::class);
        $origEcomShip = $ecomSettings->shipping_enabled;
        $origCheckoutShip = $checkoutSettings->enable_shipping;

        $ecomSettings->shipping_enabled = false;
        $ecomSettings->save();
        $checkoutSettings->enable_shipping = false;
        $checkoutSettings->save();
        $this->featurePolicy->flushCache();

        $shippingService = app(ShippingServiceInterface::class);
        $methods = $shippingService->getAvailableMethods('VN', 'SG', null, null, 100000);

        $this->assertCount(0, $methods);

        // Restore
        $ecomSettings->shipping_enabled = $origEcomShip;
        $ecomSettings->save();
        $checkoutSettings->enable_shipping = $origCheckoutShip;
        $checkoutSettings->save();
        $this->featurePolicy->flushCache();
    }

    /**
     * Test 6: Coupon DISABLED throws CouponValidationException on validation.
     */
    public function test_coupon_disabled_throws_exception(): void
    {
        config(['marketing.enable_coupons' => false]);
        config(['coupon.enable_coupons' => false]);
        $this->featurePolicy->flushCache();

        $couponService = app(CouponServiceInterface::class);

        $this->expectException(CouponValidationException::class);
        $couponService->validateCoupon('ANYCODE', [], 100000, $this->user->id);
    }

    /**
     * Test 7: Catalog mode blocks placing orders.
     */
    public function test_catalog_mode_blocks_order_placement(): void
    {
        $ecomSettings = app(EcommerceSettings::class);
        $origCatalogMode = $ecomSettings->catalog_mode;

        $ecomSettings->catalog_mode = true;
        $ecomSettings->save();
        $this->featurePolicy->flushCache();

        $product = Product::create([
            'name' => 'Catalog Product',
            'slug' => 'cat-prod-' . uniqid(),
            'sku' => 'CAT-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 50000,
            'qty' => 10,
            'total_stock' => 10,
            'is_visible' => true,
        ]);

        $dto = new OrderDataDTO(
            customerId: $this->user->id,
            email: 'tester@example.com',
            phone: '0901234567',
            shippingAddress: ['first_name' => 'Tester', 'last_name' => 'User', 'phone' => '0901234567', 'country' => 'VN', 'street' => '123 St', 'city' => 'Hanoi'],
            billingAddress: ['first_name' => 'Tester', 'last_name' => 'User', 'phone' => '0901234567', 'country' => 'VN', 'street' => '123 St', 'city' => 'Hanoi'],
            items: [
                ['product_id' => $product->id, 'qty' => 1, 'price' => 50000],
            ],
            shippingMethod: 'flat_rate',
            paymentMethod: 'cod',
            grandTotal: 50000
        );

        $this->expectException(ValidationException::class);

        $action = app(PlaceOrderAction::class);
        $action->execute($dto);

        // Restore
        $ecomSettings->catalog_mode = $origCatalogMode;
        $ecomSettings->save();
        $this->featurePolicy->flushCache();
    }

    /**
     * Test 8: Disabled payment method is rejected during order placement.
     */
    public function test_disabled_payment_method_is_rejected(): void
    {
        $checkoutSettings = app(CheckoutSettings::class);
        $origGateways = $checkoutSettings->enabled_payment_gateways ?? ['cod'];

        $checkoutSettings->enabled_payment_gateways = ['vnpay'];
        $checkoutSettings->save();
        $this->featurePolicy->flushCache();

        $product = Product::create([
            'name' => 'Payment Test Product',
            'slug' => 'pay-prod-' . uniqid(),
            'sku' => 'PAY-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 50000,
            'qty' => 10,
            'total_stock' => 10,
            'is_visible' => true,
        ]);

        $dto = new OrderDataDTO(
            customerId: $this->user->id,
            email: 'tester@example.com',
            phone: '0901234567',
            shippingAddress: ['first_name' => 'Tester', 'last_name' => 'User', 'phone' => '0901234567', 'country' => 'VN', 'street' => '123 St', 'city' => 'Hanoi'],
            billingAddress: ['first_name' => 'Tester', 'last_name' => 'User', 'phone' => '0901234567', 'country' => 'VN', 'street' => '123 St', 'city' => 'Hanoi'],
            items: [
                ['product_id' => $product->id, 'qty' => 1, 'price' => 50000],
            ],
            shippingMethod: 'flat_rate',
            paymentMethod: 'cod',
            grandTotal: 80000
        );

        $this->expectException(ValidationException::class);

        $action = app(PlaceOrderAction::class);
        $action->execute($dto);

        // Restore
        $checkoutSettings->enabled_payment_gateways = $origGateways;
        $checkoutSettings->save();
        $this->featurePolicy->flushCache();
    }

    /**
     * Test 9: Financial Audit Service logs setting modifications and masks sensitive keys.
     */
    public function test_financial_audit_service_masks_secrets(): void
    {
        $auditService = app(FinancialAuditServiceInterface::class);

        $auditService->logSettingChange(
            group: 'payment',
            key: 'stripe_secret_key',
            oldValue: 'sk_test_1234567890',
            newValue: 'sk_test_0987654321',
            reason: 'Rotated test secret'
        );

        $lastActivity = Activity::where('event', 'setting.changed')->latest('id')->first();
        $this->assertNotNull($lastActivity);
        $this->assertEquals('••••••••', $lastActivity->properties['old_value']);
        $this->assertEquals('••••••••', $lastActivity->properties['new_value']);
    }

    /**
     * Test 10: Combo disabled blocks combo order placement.
     */
    public function test_combo_disabled_blocks_order_placement(): void
    {
        $marketingSettings = app(\App\Settings\MarketingSettings::class);
        $origCombo = $marketingSettings->combo_enabled;

        $marketingSettings->combo_enabled = false;
        $marketingSettings->save();
        $this->featurePolicy->flushCache();

        $c1 = Product::create([
            'name' => 'Comp 1',
            'slug' => 'comp-1-' . uniqid(),
            'sku' => 'CP1-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 50000,
            'qty' => 10,
            'total_stock' => 10,
            'is_visible' => true,
        ]);

        $combo = Product::create([
            'name' => 'Combo Pack',
            'slug' => 'combo-pack-' . uniqid(),
            'sku' => 'CMB-' . uniqid(),
            'type' => ProductType::Combo,
            'price' => 45000,
            'qty' => 10,
            'total_stock' => 10,
            'is_visible' => true,
        ]);

        \App\Models\ComboProductItem::create([
            'parent_product_id' => $combo->id,
            'shop_product_id' => $c1->id,
            'quantity' => 1,
            'sort_order' => 0,
        ]);

        $dto = new OrderDataDTO(
            customerId: $this->user->id,
            email: 'tester@example.com',
            phone: '0901234567',
            shippingAddress: ['first_name' => 'Tester', 'last_name' => 'User', 'phone' => '0901234567', 'country' => 'VN', 'street' => '123 St', 'city' => 'Hanoi'],
            billingAddress: ['first_name' => 'Tester', 'last_name' => 'User', 'phone' => '0901234567', 'country' => 'VN', 'street' => '123 St', 'city' => 'Hanoi'],
            items: [
                ['product_id' => $combo->id, 'qty' => 1, 'price' => 45000],
            ],
            shippingMethod: 'flat_rate',
            paymentMethod: 'cod',
            grandTotal: 80000
        );

        $this->expectException(ValidationException::class);

        $action = app(PlaceOrderAction::class);
        $action->execute($dto);

        // Restore
        $marketingSettings->combo_enabled = $origCombo;
        $marketingSettings->save();
        $this->featurePolicy->flushCache();
    }

    /**
     * Test 11: Admin Setting Controller update synchronizes cross-group settings.
     */
    public function test_admin_setting_controller_syncs_cross_group_settings(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin-settings@example.com'],
            ['name' => 'Admin User', 'password' => bcrypt('password')]
        );

        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin->assignRole($role);

        $response = $this->actingAs($admin)->post(route('admin.settings.update-group'), [
            'group' => 'ecommerce',
            'settings' => [
                'enabled' => '1',
                'catalog_mode' => '0',
                'enable_wishlist' => '1',
                'enable_compare' => '1',
                'order_number_prefix' => 'ORD-',
                'low_stock_threshold' => '5',
                'tax_enabled' => '0',
                'shipping_enabled' => '0',
            ],
        ]);

        $response->assertRedirect();

        $checkoutSettings = app(CheckoutSettings::class);
        $this->assertFalse($checkoutSettings->enable_tax, 'ecommerce.tax_enabled=0 must sync checkout.enable_tax=false');
        $this->assertFalse($checkoutSettings->enable_shipping, 'ecommerce.shipping_enabled=0 must sync checkout.enable_shipping=false');
        $this->assertFalse($this->featurePolicy->isTaxCalculationEnabled());
        $this->assertFalse($this->featurePolicy->isShippingEnabled());
    }

    /**
     * Test 12: Webhook settings allowed_roles is multiselect and can be updated as an array.
     */
    public function test_webhook_settings_allowed_roles_is_multiselect_and_saves_correctly(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin-settings-webhook@example.com'],
            ['name' => 'Admin User Webhook', 'password' => bcrypt('password')]
        );

        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin->assignRole($role);

        // Verify schema field definition
        $controller = app(\App\Http\Controllers\Admin\SettingController::class);
        $request = \Illuminate\Http\Request::create('/admin/settings', 'GET', ['tab' => 'webhook']);
        $view = $controller->index($request);
        $schema = $view->getData()['schema'];

        $this->assertSame('multiselect', $schema['webhook']['fields']['allowed_roles']['type']);
        $this->assertArrayHasKey('super_admin', $schema['webhook']['fields']['allowed_roles']['options']);
        $this->assertSame('multiselect', $schema['api']['fields']['allowed_roles']['type']);

        // Update via POST
        $response = $this->actingAs($admin)->post(route('admin.settings.update-group'), [
            'group' => 'webhook',
            'settings' => [
                'enabled' => '1',
                'log_retention_days' => '30',
                'allowed_roles' => ['', 'super_admin'],
            ],
        ]);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'webhook']));

        $webhookSettings = app(\App\Settings\WebhookSettings::class);
        $this->assertSame(['super_admin'], $webhookSettings->allowed_roles);
    }
}
