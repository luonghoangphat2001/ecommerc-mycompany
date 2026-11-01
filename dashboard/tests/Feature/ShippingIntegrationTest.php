<?php

namespace Tests\Feature;

use App\Ecommerce\Checkout\Actions\PlaceOrderAction;
use App\Ecommerce\Order\DTOs\Order\OrderDataDTO;
use App\Ecommerce\Shipping\Carriers\CarrierManager;
use App\Enums\ProductType;
use App\Models\Order;
use App\Models\OrderShipping;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\User;
use Database\Seeders\ShippingZoneSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ShippingIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ShippingZoneSeeder::class);

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin@shippingtest.com'],
            ['name' => 'Admin Tester', 'password' => bcrypt('password')]
        );
        $this->adminUser->assignRole('super_admin');
    }

    /**
     * 1. Test Storefront Cart Shipping Methods API for various locations.
     */
    public function test_cart_shipping_methods_api_domestic_and_international(): void
    {
        $product = Product::create([
            'name' => 'Tennis Racket Pro',
            'slug' => 'tennis-racket-pro-' . uniqid(),
            'sku' => 'RKT-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 300000,
            'qty' => 50,
            'is_visible' => true,
        ]);

        // Domestic: Hanoi (Miền Bắc) - Vietnamese
        $resHanoi = $this->withHeaders(['X-Locale' => 'vi'])->postJson('/api/v1/storefront/cart/shipping-methods', [
            'country' => 'VN',
            'state' => 'Hà Nội',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $resHanoi->assertOk();
        $methodsHanoi = $resHanoi->json('data') ?? $resHanoi->json();
        $this->assertNotEmpty($methodsHanoi);
        $this->assertTrue(collect($methodsHanoi)->contains(fn($m) => str_contains($m['name'], 'Miền Bắc')));

        // Domestic: Hanoi (Northern) - English
        $resHanoiEn = $this->withHeaders(['X-Locale' => 'en'])->postJson('/api/v1/storefront/cart/shipping-methods', [
            'country' => 'VN',
            'state' => 'Hà Nội',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);
        $resHanoiEn->assertOk();
        $methodsHanoiEn = $resHanoiEn->json('data') ?? $resHanoiEn->json();
        $this->assertTrue(collect($methodsHanoiEn)->contains(fn($m) => str_contains($m['name'], 'Northern')));

        // Domestic: Ho Chi Minh (Miền Nam)
        $resHcm = $this->withHeaders(['X-Locale' => 'vi'])->postJson('/api/v1/storefront/cart/shipping-methods', [
            'country' => 'VN',
            'state' => 'Thành phố Hồ Chí Minh',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $resHcm->assertOk();
        $methodsHcm = $resHcm->json('data') ?? $resHcm->json();
        $this->assertTrue(collect($methodsHcm)->contains(fn($m) => str_contains($m['name'], 'Miền Nam')));

        // International: United States
        $resUs = $this->withHeaders(['X-Locale' => 'vi'])->postJson('/api/v1/storefront/cart/shipping-methods', [
            'country' => 'US',
            'state' => 'New York',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $resUs->assertOk();
        $methodsUs = $resUs->json('data') ?? $resUs->json();
        $this->assertTrue(collect($methodsUs)->contains(fn($m) => str_contains($m['name'], 'Bắc Mỹ')));
    }

    /**
     * 2. Test Checkout and Order Fulfillment with Shipping Snapshot.
     */
    public function test_checkout_creates_order_shipping_snapshot(): void
    {
        $product = Product::create([
            'name' => 'Badminton Shuttlecock',
            'slug' => 'shuttlecock-' . uniqid(),
            'sku' => 'SHTL-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 200000,
            'qty' => 100,
            'is_visible' => true,
            'apply_tax' => false,
        ]);

        // Northern Vietnam zone method: "Giao hàng tiêu chuẩn Miền Bắc" = 25,000 VND
        $northZone = ShippingZone::where('code', 'VN_NORTH')->first();
        $method = $northZone->methods()->where('type', 'flat_rate')->first();

        $subtotal = 200000;
        $shippingCost = (int) ($method->settings['cost'] ?? 25000);
        $grandTotal = $subtotal + $shippingCost;

        $dto = new OrderDataDTO(
            customerId: null,
            email: 'customer@example.com',
            phone: '0912345678',
            shippingAddress: [
                'first_name' => 'Nguyễn',
                'last_name' => 'Văn A',
                'phone' => '0912345678',
                'country' => 'VN',
                'state' => 'Hà Nội',
                'city' => 'Quận Cầu Giấy',
                'street' => '123 Đường Cầu Giấy',
            ],
            billingAddress: [
                'first_name' => 'Nguyễn',
                'last_name' => 'Văn A',
                'phone' => '0912345678',
                'country' => 'VN',
                'state' => 'Hà Nội',
                'city' => 'Quận Cầu Giấy',
                'street' => '123 Đường Cầu Giấy',
            ],
            items: [
                ['product_id' => $product->id, 'qty' => 1, 'price' => 200000],
            ],
            shippingMethod: (string) $method->id,
            grandTotal: $grandTotal
        );

        $action = app(PlaceOrderAction::class);
        $order = $action->execute($dto);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($subtotal, $order->subtotal);
        $this->assertEquals($grandTotal, $order->total);

        // Verify OrderShipping record
        $orderShipping = OrderShipping::where('order_id', $order->id)->first();
        $this->assertNotNull($orderShipping);
        $this->assertEquals($shippingCost, $orderShipping->amount);
        $this->assertEquals($method->name, $orderShipping->method);
        $this->assertNotEmpty($orderShipping->tracking_number);
        $this->assertEquals($northZone->id, $orderShipping->metadata['zone_id']);
        $this->assertEquals('VN_NORTH', $orderShipping->metadata['zone_code']);

        // Verify Public Tracking API
        $trackRes = $this->getJson('/api/v1/storefront/orders/track/' . $order->number);
        $trackRes->assertOk();
        $this->assertEquals($order->number, $trackRes->json('data.number') ?? $trackRes->json('number'));
        $this->assertEquals($shippingCost, $trackRes->json('data.shipping_cost') ?? $trackRes->json('shipping_cost'));
    }

    /**
     * 3. Test AI Agent Shipping Tools Endpoints.
     */
    public function test_ai_agent_shipping_endpoints(): void
    {
        $department = \App\Models\Department::firstOrCreate(
            ['code' => 'rnd_shipping'],
            [
                'name' => 'Logistics Department',
                'description' => 'Logistics & Shipping',
                'is_active' => true,
                'risk_level_threshold' => 'low',
            ]
        );

        $rawToken = 'agent-secret-token-12345';
        \App\Models\DepartmentAgent::create([
            'department_id' => $department->id,
            'name' => 'Shipping AI Agent',
            'agent_code' => 'AGENT-SHIPPING-001',
            'status' => \App\Ecommerce\Department\Enums\AgentStatus::ACTIVE,
            'api_token_hash' => \Illuminate\Support\Facades\Hash::make($rawToken),
        ]);

        $headers = [
            'Authorization' => 'Bearer ' . $rawToken,
            'X-Agent-Code' => 'AGENT-SHIPPING-001',
            'Accept' => 'application/json',
        ];

        // 1. Zones
        $resZones = $this->withHeaders($headers)->getJson('/api/v1/storefront/agents/shipping/zones');
        $resZones->assertOk();
        $this->assertTrue($resZones->json('success'));
        $this->assertGreaterThanOrEqual(10, count($resZones->json('data')));

        // 2. Destinations
        $resDest = $this->withHeaders($headers)->getJson('/api/v1/storefront/agents/shipping/destinations');
        $resDest->assertOk();
        $this->assertEquals(63, $resDest->json('data.domestic.total_provinces'));

        // 3. Rate Estimate
        $resEst = $this->withHeaders($headers)->postJson('/api/v1/storefront/agents/shipping/estimate', [
            'country' => 'VN',
            'state' => 'Đà Nẵng',
            'subtotal' => 600000,
            'weight' => 1500,
        ]);
        $resEst->assertOk();
        $this->assertTrue($resEst->json('success'));
        $this->assertNotEmpty($resEst->json('shipping_options'));

        // 4. Policies
        $resPol = $this->withHeaders($headers)->getJson('/api/v1/storefront/agents/shipping/policies');
        $resPol->assertOk();
        $this->assertArrayHasKey('domestic', $resPol->json('data'));
    }

    /**
     * 4. Test Admin Shipping Management Web Routes.
     */
    public function test_admin_shipping_management_routes(): void
    {
        $this->actingAs($this->adminUser);

        // 1. Shipping Zones index
        $resZones = $this->get(route('admin.shipping-zones.index'));
        $resZones->assertOk();

        // 2. Shipping Methods index & edit
        $resMethods = $this->get(route('admin.shipping-methods.index'));
        $resMethods->assertOk();

        $firstMethod = ShippingMethod::first();
        if ($firstMethod) {
            $resEditMethod = $this->get(route('admin.shipping-methods.edit', $firstMethod->id));
            $resEditMethod->assertOk();
            $resEditMethod->assertDontSee('<textarea', false);
            $resEditMethod->assertSee('Cấu hình mức cước và điều kiện');
        }

        // Settings - Payment Gateways without raw JSON
        $resCheckoutSettings = $this->get(route('admin.settings.index', ['tab' => 'checkout']));
        $resCheckoutSettings->assertOk();
        $resCheckoutSettings->assertDontSee('Cổng thanh toán (JSON)');
        $resCheckoutSettings->assertDontSee('Payment gateways JSON');
        $resCheckoutSettings->assertSee('name="settings[enabled_payment_gateways][]"', false);

        // 3. Shipping Carriers index
        $resCarriers = $this->get(route('admin.shipping-carriers.index'));
        $resCarriers->assertOk();
        $resCarriers->assertSee('GHN');
        $resCarriers->assertSee('GHTK');
        $resCarriers->assertSee('Viettel Post');
        $resCarriers->assertSee('DHL Express');

        // 4. Test Carrier connection endpoint
        $resTest = $this->postJson(route('admin.shipping-carriers.test', 'ghn'));
        $resTest->assertOk();
        $this->assertArrayHasKey('success', $resTest->json());

        // 5. Shipping Orders index
        $resOrders = $this->get(route('admin.shipping-orders.index'));
        $resOrders->assertOk();

        // 6. Test Shipping Zone Form UI/UX (No raw JSON textarea)
        $resCreateZone = $this->get(route('admin.shipping-zones.create'));
        $resCreateZone->assertOk();
        $resCreateZone->assertSee('Tỉnh thành Việt Nam');
        $resCreateZone->assertSee('Toàn quốc Việt Nam');
        $resCreateZone->assertSee('Miền Bắc (25)');
        $resCreateZone->assertDontSee('<textarea id="locations_json"', false);

        $northZone = ShippingZone::where('code', 'VN_NORTH')->first();
        $resEditZone = $this->get(route('admin.shipping-zones.edit', $northZone->id));
        $resEditZone->assertOk();
        $resEditZone->assertSee('Chỉnh sửa Khu vực vận chuyển');
        $resEditZone->assertDontSee('<textarea id="locations_json"', false);

        // 7. Store new zone via form
        $resStoreZone = $this->post(route('admin.shipping-zones.store'), [
            'name' => ['vi' => 'Khu vực Đồng Bằng Sông Cửu Long', 'en' => 'Mekong Delta Region'],
            'code' => 'VN_MEKONG_DELTA',
            'sort' => 5,
            'is_active' => '1',
            'locations_json' => json_encode([
                [
                    'country' => 'VN',
                    'provinces' => ['Cần Thơ', 'An Giang', 'Đồng Tháp'],
                ],
            ]),
        ]);
        $resStoreZone->assertRedirect(route('admin.shipping-zones.index'));

        $savedZone = ShippingZone::where('code', 'VN_MEKONG_DELTA')->first();
        $this->assertNotNull($savedZone);
        $this->assertEquals(['Cần Thơ', 'An Giang', 'Đồng Tháp'], $savedZone->locations[0]['provinces']);
        $this->assertStringContainsString('Cần Thơ', $savedZone->formatted_locations);
    }
}
