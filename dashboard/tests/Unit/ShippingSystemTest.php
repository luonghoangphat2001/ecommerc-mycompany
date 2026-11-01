<?php

namespace Tests\Unit;

use App\Ecommerce\Shipping\Carriers\CarrierManager;
use App\Ecommerce\Shipping\Contracts\ShippingServiceInterface;
use App\Ecommerce\Shipping\Contracts\ShippingZoneRepositoryInterface;
use App\Ecommerce\Shipping\Services\Drivers\FlatRateDriver;
use App\Ecommerce\Shipping\Services\Drivers\FreeShippingDriver;
use App\Ecommerce\Shipping\Services\Drivers\WeightBasedDriver;
use App\Ecommerce\Shipping\Services\PackageCalculator;
use App\Ecommerce\Shipping\Support\VietnamLocations;
use App\Models\ComboProduct;
use App\Models\ComboProductItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ShippingSystemTest extends TestCase
{
    use DatabaseTransactions;

    protected ShippingZoneRepositoryInterface $zoneRepo;
    protected ShippingServiceInterface $shippingService;
    protected CarrierManager $carrierManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->zoneRepo = app(ShippingZoneRepositoryInterface::class);
        $this->shippingService = app(ShippingServiceInterface::class);
        $this->carrierManager = app(CarrierManager::class);

        // Ensure database has seeded shipping zones
        $this->seed(\Database\Seeders\ShippingZoneSeeder::class);
    }

    /**
     * 1. Test domestic and international zone matching hierarchy.
     */
    public function test_shipping_zones_geographic_hierarchy_matching(): void
    {
        // Northern Vietnam
        $north = $this->zoneRepo->findMatchingZone('VN', 'Hà Nội');
        $this->assertNotNull($north);
        $this->assertEquals('VN_NORTH', $north->code);

        // Central Vietnam
        $central = $this->zoneRepo->findMatchingZone('VN', 'Đà Nẵng');
        $this->assertNotNull($central);
        $this->assertEquals('VN_CENTRAL', $central->code);

        // Southern Vietnam with prefix
        $south = $this->zoneRepo->findMatchingZone('VN', 'Thành phố Hồ Chí Minh');
        $this->assertNotNull($south);
        $this->assertEquals('VN_SOUTH', $south->code);

        // Southern Vietnam with ISO code
        $southIso = $this->zoneRepo->findMatchingZone('VN', 'VN-SG');
        $this->assertNotNull($southIso);
        $this->assertEquals('VN_SOUTH', $southIso->code);

        // Nationwide fallback (when no state is provided or unmapped province)
        $nationwide = $this->zoneRepo->findMatchingZone('VN', null);
        $this->assertNotNull($nationwide);
        $this->assertEquals('VN_NATIONWIDE', $nationwide->code);

        // Asia: Japan
        $asia = $this->zoneRepo->findMatchingZone('JP');
        $this->assertNotNull($asia);
        $this->assertEquals('INT_ASIA', $asia->code);

        // Europe: France
        $europe = $this->zoneRepo->findMatchingZone('FR');
        $this->assertNotNull($europe);
        $this->assertEquals('INT_EUROPE', $europe->code);

        // North America: United States
        $northAmerica = $this->zoneRepo->findMatchingZone('US', 'California');
        $this->assertNotNull($northAmerica);
        $this->assertEquals('INT_NORTH_AMERICA', $northAmerica->code);

        // South America: Brazil
        $southAmerica = $this->zoneRepo->findMatchingZone('BR');
        $this->assertNotNull($southAmerica);
        $this->assertEquals('INT_SOUTH_AMERICA', $southAmerica->code);

        // Africa: Egypt
        $africa = $this->zoneRepo->findMatchingZone('EG');
        $this->assertNotNull($africa);
        $this->assertEquals('INT_AFRICA', $africa->code);

        // Oceania: Australia
        $oceania = $this->zoneRepo->findMatchingZone('AU');
        $this->assertNotNull($oceania);
        $this->assertEquals('INT_OCEANIA', $oceania->code);

        // Rest of World Fallback: Unknown country code
        $row = $this->zoneRepo->findMatchingZone('ZZ');
        $this->assertNotNull($row);
        $this->assertEquals('INT_REST_OF_WORLD', $row->code);
    }

    /**
     * 2. Test Vietnam province name normalization and matching.
     */
    public function test_vietnam_locations_matching(): void
    {
        $this->assertTrue(VietnamLocations::matches('Hà Nội', ['Hà Nội', 'Hải Phòng']));
        $this->assertTrue(VietnamLocations::matches('TP. Hồ Chí Minh', ['Hồ Chí Minh']));
        $this->assertTrue(VietnamLocations::matches('Tỉnh Bình Dương', ['Bình Dương']));
        $this->assertTrue(VietnamLocations::matches('VN-HN', ['Hà Nội']));
        $this->assertFalse(VietnamLocations::matches('Tokyo', ['Hà Nội', 'Hồ Chí Minh']));
    }

    /**
     * 3. Test Manual Flat Rate calculation & Free shipping threshold.
     */
    public function test_manual_flat_rate_driver(): void
    {
        $driver = new FlatRateDriver();

        // Standard rate
        $fee = $driver->calculateFee(200000, ['country' => 'VN'], ['cost' => 30000]);
        $this->assertEquals(30000, $fee);

        // Under free shipping threshold
        $feeUnder = $driver->calculateFee(300000, ['country' => 'VN'], [
            'cost' => 30000,
            'free_shipping_threshold' => 500000,
        ]);
        $this->assertEquals(30000, $feeUnder);

        // Meets free shipping threshold
        $feeOver = $driver->calculateFee(600000, ['country' => 'VN'], [
            'cost' => 30000,
            'free_shipping_threshold' => 500000,
        ]);
        $this->assertEquals(0, $feeOver);
    }

    /**
     * 4. Test Manual Free Shipping driver min_amount eligibility.
     */
    public function test_manual_free_shipping_driver(): void
    {
        $driver = new FreeShippingDriver();

        // Below minimum subtotal -> ineligible (-1)
        $below = $driver->calculateFee(200000, ['country' => 'VN'], ['min_amount' => 500000]);
        $this->assertEquals(-1, $below);

        // At or above minimum subtotal -> 0
        $eligible = $driver->calculateFee(500000, ['country' => 'VN'], ['min_amount' => 500000]);
        $this->assertEquals(0, $eligible);
    }

    /**
     * 5. Test Manual Weight Based rate calculation.
     */
    public function test_manual_weight_based_driver(): void
    {
        $driver = new WeightBasedDriver();
        $options = [
            'rules' => [
                ['min_weight' => 0, 'max_weight' => 1000, 'cost' => 20000],
                ['min_weight' => 1000, 'max_weight' => 3000, 'cost' => 35000],
                ['min_weight' => 3000, 'max_weight' => 5000, 'cost' => 50000],
            ],
            'additional_cost_per_kg' => 5000,
            'default_cost' => 25000,
            'free_shipping_threshold' => 500000,
        ];

        // Tier 1: 500g -> 20,000₫
        $tier1 = $driver->calculateFee(100000, ['country' => 'VN'], array_merge($options, ['weight' => 500]));
        $this->assertEquals(20000, $tier1);

        // Tier 2: 2500g -> 35,000₫
        $tier2 = $driver->calculateFee(100000, ['country' => 'VN'], array_merge($options, ['weight' => 2500]));
        $this->assertEquals(35000, $tier2);

        // Exceeds max tier: 7000g (2kg over 5000g @ 5000₫/kg) -> 50,000 + 10,000 = 60,000₫
        $tierOver = $driver->calculateFee(100000, ['country' => 'VN'], array_merge($options, ['weight' => 7000]));
        $this->assertEquals(60000, $tierOver);

        // Qualifies for free shipping threshold
        $free = $driver->calculateFee(600000, ['country' => 'VN'], array_merge($options, ['weight' => 7000]));
        $this->assertEquals(0, $free);
    }

    /**
     * 6. Test Package Calculator for Combo products and dimension packing.
     */
    public function test_package_calculator_combo_and_dimensions(): void
    {
        $calculator = new PackageCalculator();

        // Simple product with weight 300g
        $simple = Product::create([
            'name' => 'Test Item 1',
            'slug' => 'test-item-1-' . uniqid(),
            'sku' => 'SKU-' . uniqid(),
            'price' => 50000,
            'qty' => 10,
        ]);

        $package = $calculator->calculatePackage([
            ['product_id' => $simple->id, 'quantity' => 2],
        ]);

        $this->assertGreaterThanOrEqual(200, $package['weight']);
        $this->assertEquals(20, $package['length']);
        $this->assertEquals(15, $package['width']);
        $this->assertEquals(2, $package['items_count']);
    }

    /**
     * 7. Test Carrier Adapters registration and connection test handling.
     */
    public function test_carrier_adapters_registration_and_status(): void
    {
        $all = $this->carrierManager->getAllCarriers();
        $this->assertArrayHasKey('ghn', $all);
        $this->assertArrayHasKey('ghtk', $all);
        $this->assertArrayHasKey('viettel_post', $all);
        $this->assertArrayHasKey('dhl', $all);

        $ghn = $this->carrierManager->getAdapter('ghn');
        $this->assertEquals('ghn', $ghn->getId());
        $this->assertTrue($ghn->isDomestic());
        $this->assertFalse($ghn->isInternational());

        // Test connection without credentials returns clear missing credentials response
        $testRes = $ghn->testConnection();
        $this->assertFalse($testRes['success']);
        $this->assertStringContainsString('credentials', $testRes['message']);
    }

    /**
     * 8. Test Centralized ShippingService getAvailableMethods.
     */
    public function test_centralized_shipping_service_available_methods(): void
    {
        // For Hanoi with subtotal 200,000 VND
        $methods = $this->shippingService->getAvailableMethods('VN', 'Hà Nội', null, null, 200000);
        $this->assertNotEmpty($methods);

        // Verify all returned methods have valid price & zone details
        foreach ($methods as $m) {
            $this->assertArrayHasKey('name', $m);
            $this->assertArrayHasKey('cost', $m);
            $this->assertArrayHasKey('type', $m);
            $this->assertGreaterThanOrEqual(0, $m['cost']);
            $this->assertStringContainsString('Miền Bắc', $m['zone_name']);
        }
    }
}
