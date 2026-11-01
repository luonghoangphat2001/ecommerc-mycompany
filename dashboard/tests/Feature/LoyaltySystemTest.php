<?php

namespace Tests\Feature;

use App\Ecommerce\Checkout\Actions\PlaceOrderAction;
use App\Ecommerce\Checkout\DTOs\CheckoutRequestDTO;
use App\Ecommerce\Checkout\DTOs\CheckoutResultDTO;
use App\Ecommerce\Checkout\Services\Pipes\ApplyLoyaltyPoints;
use App\Ecommerce\Loyalty\Contracts\LoyaltyRepositoryInterface;
use App\Ecommerce\Order\DTOs\Order\OrderDataDTO;
use App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface;
use App\Enums\ProductType;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Settings\LoyaltySettings;
use App\Settings\MarketingSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LoyaltySystemTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected LoyaltySettings $loyaltySettings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::firstOrCreate(
            ['email' => 'loyalty-tester@example.com'],
            ['name' => 'Loyalty Tester', 'password' => bcrypt('password123')]
        );

        $this->loyaltySettings = app(LoyaltySettings::class);
        $this->loyaltySettings->enabled = true;
        $this->loyaltySettings->spending_amount_per_point = 10000; // 10.000 VND = 1 point
        $this->loyaltySettings->point_conversion_rate = 1000; // 1 point = 1.000 VND discount
        $this->loyaltySettings->minimum_points_to_redeem = 10;
        $this->loyaltySettings->max_redeem_percentage = 50;
        $this->loyaltySettings->exclude_sale_items = false;
        $this->loyaltySettings->excluded_product_ids = [];
        $this->loyaltySettings->excluded_category_ids = [];
        $this->loyaltySettings->save();

        $marketing = app(MarketingSettings::class);
        $marketing->loyalty_enabled = true;
        $marketing->point_conversion_rate = 1000;
        $marketing->save();

        app(FeaturePolicyServiceInterface::class)->flushCache();

        $zone = \App\Models\ShippingZone::firstOrCreate(['code' => 'VN_LOYALTY'], ['name' => 'VN Zone', 'is_active' => true]);
        \App\Models\ShippingMethod::firstOrCreate(
            ['type' => 'flat_rate', 'shipping_zone_id' => $zone->id],
            ['name' => 'Standard Rate', 'settings' => ['cost' => 30000], 'is_enabled' => true]
        );
    }

    public function test_loyalty_settings_can_be_saved_and_synced(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin-loyalty@example.com'],
            ['name' => 'Admin User', 'password' => bcrypt('secret123')]
        );

        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin->assignRole($role);

        $response = $this->actingAs($admin)->post(route('admin.settings.update-group'), [
            'group' => 'loyalty',
            'settings' => [
                'enabled' => 1,
                'spending_amount_per_point' => 20000,
                'point_conversion_rate' => 2000,
                'minimum_points_to_redeem' => 50,
                'max_redeem_percentage' => 30,
                'exclude_sale_items' => 1,
                'excluded_category_ids' => [],
                'excluded_product_ids' => [],
            ],
        ]);

        $response->assertRedirect();

        $loyalty = app(LoyaltySettings::class);
        $this->assertEquals(20000, $loyalty->spending_amount_per_point);
        $this->assertEquals(2000, $loyalty->point_conversion_rate);
        $this->assertEquals(50, $loyalty->minimum_points_to_redeem);
        $this->assertEquals(30, $loyalty->max_redeem_percentage);
        $this->assertTrue($loyalty->exclude_sale_items);

        // Check synced to marketing
        $marketing = app(MarketingSettings::class);
        $this->assertTrue($marketing->loyalty_enabled);
        $this->assertEquals(2000, $marketing->point_conversion_rate);
    }

    public function test_product_model_supports_loyalty_attributes(): void
    {
        $product = Product::create([
            'name' => ['vi' => 'Sản phẩm tích điểm', 'en' => 'Loyalty Product'],
            'slug' => 'san-pham-tich-diem-' . uniqid(),
            'sku' => 'LOYALTY-' . uniqid(),
            'price' => 100000,
            'is_visible' => true,
            'featured' => false,
            'type' => ProductType::Simple,
            'is_loyalty_earnable' => true,
            'is_loyalty_redeemable' => true,
            'loyalty_points_reward' => 25,
        ]);

        $this->assertTrue($product->is_loyalty_earnable);
        $this->assertTrue($product->is_loyalty_redeemable);
        $this->assertEquals(25, $product->loyalty_points_reward);
    }

    public function test_apply_loyalty_points_respects_minimum_threshold_and_caps(): void
    {
        $loyaltyRepo = app(LoyaltyRepositoryInterface::class);
        $loyaltyRepo->incrementPoints($this->user->id, 100, 100);

        $pipe = new ApplyLoyaltyPoints();

        // 1. Below minimum threshold (minimum is 10)
        $this->actingAs($this->user);
        $request = new CheckoutRequestDTO(items: [], redeemPoints: 5);
        $result = new CheckoutResultDTO(total: 100000);

        $pipe->handle(['request' => $request, 'result' => $result], fn ($p) => $p);
        $this->assertEquals(0, $result->loyaltyDiscountTotal);
        $this->assertEquals(100000, $result->total);

        // 2. Valid redemption: 20 points = 20 * 1000 = 20,000 VND discount
        // Max redeem percentage is 50%, 50% of 100,000 is 50,000 VND
        $request = new CheckoutRequestDTO(items: [], redeemPoints: 20);
        $result = new CheckoutResultDTO(total: 100000);

        $pipe->handle(['request' => $request, 'result' => $result], fn ($p) => $p);
        $this->assertEquals(20000, $result->loyaltyDiscountTotal);
        $this->assertEquals(80000, $result->total);
        $this->assertEquals(20, $request->redeemPoints);

        // 3. Exceeds max percentage cap (requests 80 points = 80,000 VND, but max is 50,000 VND)
        $request = new CheckoutRequestDTO(items: [], redeemPoints: 80);
        $result = new CheckoutResultDTO(total: 100000);

        $pipe->handle(['request' => $request, 'result' => $result], fn ($p) => $p);
        $this->assertEquals(50000, $result->loyaltyDiscountTotal);
        $this->assertEquals(50000, $result->total);
        $this->assertEquals(50, $request->redeemPoints);
    }

    public function test_apply_loyalty_points_respects_non_redeemable_products(): void
    {
        $loyaltyRepo = app(LoyaltyRepositoryInterface::class);
        $loyaltyRepo->incrementPoints($this->user->id, 100, 100);

        $nonRedeemableProduct = Product::create([
            'name' => ['vi' => 'Sản phẩm không cho đổi điểm', 'en' => 'Non Redeemable Product'],
            'slug' => 'non-redeemable-' . uniqid(),
            'sku' => 'NON-RED-' . uniqid(),
            'price' => 80000,
            'is_visible' => true,
            'featured' => false,
            'type' => ProductType::Simple,
            'is_loyalty_earnable' => true,
            'is_loyalty_redeemable' => false, // CANNOT REDEEM
        ]);

        $redeemableProduct = Product::create([
            'name' => ['vi' => 'Sản phẩm được đổi điểm', 'en' => 'Redeemable Product'],
            'slug' => 'redeemable-' . uniqid(),
            'sku' => 'RED-' . uniqid(),
            'price' => 20000,
            'is_visible' => true,
            'featured' => false,
            'type' => ProductType::Simple,
            'is_loyalty_earnable' => true,
            'is_loyalty_redeemable' => true,
        ]);

        $this->actingAs($this->user);
        // Cart total is 100,000, but only 20,000 is redeemable!
        // 50% cap on 20,000 is 10,000 max discount!
        $request = new CheckoutRequestDTO(
            items: [
                ['product_id' => $nonRedeemableProduct->id, 'qty' => 1, 'unit_price' => 80000, 'total' => 80000],
                ['product_id' => $redeemableProduct->id, 'qty' => 1, 'unit_price' => 20000, 'total' => 20000],
            ],
            redeemPoints: 50
        );
        $result = new CheckoutResultDTO(total: 100000);

        $pipe = new ApplyLoyaltyPoints();
        $pipe->handle(['request' => $request, 'result' => $result], fn ($p) => $p);

        $this->assertEquals(10000, $result->loyaltyDiscountTotal);
        $this->assertEquals(90000, $result->total);
        $this->assertEquals(10, $request->redeemPoints);
    }

    public function test_place_order_earns_points_based_on_spending_rate_and_bonus(): void
    {
        $normalProduct = Product::create([
            'name' => ['vi' => 'Sản phẩm tiêu chuẩn', 'en' => 'Standard Product'],
            'slug' => 'standard-' . uniqid(),
            'sku' => 'STD-' . uniqid(),
            'price' => 50000,
            'qty' => 10,
            'total_stock' => 10,
            'is_visible' => true,
            'featured' => false,
            'type' => ProductType::Simple,
            'is_loyalty_earnable' => true,
        ]);

        $bonusProduct = Product::create([
            'name' => ['vi' => 'Sản phẩm thưởng cố định', 'en' => 'Bonus Product'],
            'slug' => 'bonus-' . uniqid(),
            'sku' => 'BNS-' . uniqid(),
            'price' => 30000,
            'qty' => 10,
            'total_stock' => 10,
            'is_visible' => true,
            'featured' => false,
            'type' => ProductType::Simple,
            'is_loyalty_earnable' => true,
            'loyalty_points_reward' => 15, // Fixed 15 bonus points
        ]);

        $excludedProduct = Product::create([
            'name' => ['vi' => 'Sản phẩm không tích điểm', 'en' => 'Excluded Product'],
            'slug' => 'excl-' . uniqid(),
            'sku' => 'EXC-' . uniqid(),
            'price' => 40000,
            'qty' => 10,
            'total_stock' => 10,
            'is_visible' => true,
            'featured' => false,
            'type' => ProductType::Simple,
            'is_loyalty_earnable' => false, // Excluded
        ]);

        // Rate is 10,000 VND = 1 point
        // Normal product: 50,000 / 10,000 = 5 points
        // Bonus product: fixed 15 points
        // Excluded product: 0 points
        // Total points expected: 5 + 15 = 20 points!
        $items = [
            [
                'product_id' => $normalProduct->id,
                'qty' => 1,
                'unit_price' => 50000,
                'original_price' => 50000,
                'total' => 50000,
            ],
            [
                'product_id' => $bonusProduct->id,
                'qty' => 1,
                'unit_price' => 30000,
                'original_price' => 30000,
                'total' => 30000,
            ],
            [
                'product_id' => $excludedProduct->id,
                'qty' => 1,
                'unit_price' => 40000,
                'original_price' => 40000,
                'total' => 40000,
            ],
        ];

        $shippingDto = new \App\Ecommerce\Address\DTOs\Address\AddressDTO(
            first_name: 'John',
            last_name: 'Doe',
            phone: '0901234567',
            address_detail: '123 St',
            country_code: 'VN'
        );

        $calc = app(\App\Ecommerce\Checkout\Services\CheckoutCalculatorService::class)->calculate(
            new \App\Ecommerce\Checkout\DTOs\CheckoutRequestDTO(
                items: [
                    ['product_id' => $normalProduct->id, 'qty' => 1, 'unit_price' => 50000, 'total' => 50000],
                    ['product_id' => $bonusProduct->id, 'qty' => 1, 'unit_price' => 30000, 'total' => 30000],
                    ['product_id' => $excludedProduct->id, 'qty' => 1, 'unit_price' => 40000, 'total' => 40000],
                ],
                shippingMethod: 'flat_rate',
                shippingAddress: $shippingDto,
            )
        );

        $placeOrderAction = app(PlaceOrderAction::class);
        $dto = new OrderDataDTO(
            email: 'loyalty-tester@example.com',
            phone: '0901234567',
            shippingAddress: ['first_name' => 'John', 'last_name' => 'Doe', 'phone' => '0901234567', 'street' => '123 St', 'city' => 'HCM', 'country' => 'VN'],
            billingAddress: ['first_name' => 'John', 'last_name' => 'Doe', 'phone' => '0901234567', 'street' => '123 St', 'city' => 'HCM', 'country' => 'VN'],
            items: [
                ['product_id' => $normalProduct->id, 'qty' => 1, 'price' => 50000],
                ['product_id' => $bonusProduct->id, 'qty' => 1, 'price' => 30000],
                ['product_id' => $excludedProduct->id, 'qty' => 1, 'price' => 40000],
            ],
            customerId: $this->user->id,
            shippingMethod: 'flat_rate',
            paymentMethod: 'cod',
            currency: 'VND',
            grandTotal: $calc->total
        );

        $order = $placeOrderAction->execute($dto);
        $this->assertInstanceOf(Order::class, $order);

        $loyaltyRepo = app(LoyaltyRepositoryInterface::class);
        $pointsRecord = $loyaltyRepo->findOrCreateByUserId($this->user->id);

        $this->assertEquals(20, $pointsRecord->current_points);
        $this->assertEquals('20', $order->metas()->where('key', 'earned_points')->first()?->value);
    }
}
