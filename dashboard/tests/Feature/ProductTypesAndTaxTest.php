<?php

namespace Tests\Feature;

use App\Ecommerce\Checkout\Actions\PlaceOrderAction;
use App\Ecommerce\Order\Contracts\OrderServiceInterface;
use App\Ecommerce\Order\DTOs\Order\OrderDataDTO;
use App\Ecommerce\Product\Contracts\TaxServiceInterface;
use App\Enums\OrderItemType;
use App\Enums\ProductType;
use App\Models\ComboProduct;
use App\Models\ComboProductItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\TaxClass;
use App\Models\TaxRate;
use App\Models\User;
use App\Settings\CheckoutSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductTypesAndTaxTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure standard tax classes and rates exist
        $std = TaxClass::firstOrCreate(['slug' => 'standard'], ['name' => 'Standard VAT']);
        $red = TaxClass::firstOrCreate(['slug' => 'reduced'], ['name' => 'Reduced VAT']);

        TaxRate::firstOrCreate(
            ['tax_class_id' => $std->id, 'country' => 'VN'],
            ['name' => 'VAT 10%', 'rate' => 10.0, 'priority' => 1, 'is_compound' => false, 'is_shipping' => true]
        );
        TaxRate::firstOrCreate(
            ['tax_class_id' => $red->id, 'country' => 'VN'],
            ['name' => 'VAT 8%', 'rate' => 8.0, 'priority' => 1, 'is_compound' => false, 'is_shipping' => true]
        );

        // Ensure flat rate shipping method exists with standard 35,000 VND fee
        $zone = ShippingZone::firstOrCreate(['code' => 'VN_STANDARD'], ['name' => 'Vietnam Standard Zone', 'is_active' => true]);
        ShippingMethod::firstOrCreate(
            ['type' => 'flat_rate'],
            ['shipping_zone_id' => $zone->id, 'name' => 'Flat Rate Standard', 'settings' => ['cost' => 35000], 'is_enabled' => true]
        );
    }

    /**
     * 1. Create and purchase a Simple Product.
     */
    public function test_1_create_and_purchase_simple_product(): void
    {
        $product = Product::create([
            'name' => 'Simple Tennis Ball',
            'slug' => 'simple-tennis-ball-' . uniqid(),
            'sku' => 'BALL-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 50000,
            'qty' => 20,
            'total_stock' => 20,
            'is_visible' => true,
            'apply_tax' => false,
        ]);

        $this->assertTrue($product->isSimple());
        $this->assertEquals(20, $product->available_stock);
        $this->assertEquals(50000, $product->effective_price);

        $dto = new OrderDataDTO(
            customerId: null,
            email: 'guest@example.com',
            phone: '0901234567',
            shippingAddress: ['first_name' => 'John', 'last_name' => 'Doe', 'phone' => '0901234567', 'country' => 'VN', 'street' => '123 St', 'city' => 'Hanoi'],
            billingAddress: ['first_name' => 'John', 'last_name' => 'Doe', 'phone' => '0901234567', 'country' => 'VN', 'street' => '123 St', 'city' => 'Hanoi'],
            items: [
                ['product_id' => $product->id, 'qty' => 3, 'price' => 50000],
            ],
            shippingMethod: 'flat_rate',
            grandTotal: 185000
        );

        $placeOrderAction = app(PlaceOrderAction::class);
        $order = $placeOrderAction->execute($dto);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(150000, $order->subtotal);

        // Verify stock deducted
        $product->refresh();
        $this->assertEquals(17, $product->available_stock);
    }

    /**
     * 2. Create a Variable Product with multiple combinations.
     */
    public function test_2_create_variable_product_with_multiple_combinations(): void
    {
        $product = Product::create([
            'name' => 'Pro Running Shoes',
            'slug' => 'pro-running-shoes-' . uniqid(),
            'sku' => 'SHOE-' . uniqid(),
            'type' => ProductType::Variable,
            'price' => 1000000,
            'qty' => 0,
            'total_stock' => 0,
            'is_visible' => true,
        ]);

        $this->assertTrue($product->isVariable());

        // Create product attributes: Size (40, 41) and Color (Red, Blue)
        ProductAttribute::create([
            'product_id' => $product->id,
            'name' => 'Size',
            'options' => ['40', '41'],
            'position' => 0,
        ]);
        ProductAttribute::create([
            'product_id' => $product->id,
            'name' => 'Color',
            'options' => ['Red', 'Blue'],
            'position' => 1,
        ]);

        // Create 4 variants
        $v1 = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $product->sku . '-40-RED',
            'price' => 1000000,
            'qty' => 5,
            'attributes' => ['Size' => '40', 'Color' => 'Red'],
            'is_active' => true,
        ]);
        $v2 = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $product->sku . '-40-BLUE',
            'price' => 1000000,
            'qty' => 8,
            'attributes' => ['Size' => '40', 'Color' => 'Blue'],
            'is_active' => true,
        ]);
        $v3 = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $product->sku . '-41-RED',
            'price' => 1050000,
            'sale_price' => 990000,
            'qty' => 10,
            'attributes' => ['Size' => '41', 'Color' => 'Red'],
            'is_active' => true,
        ]);
        $v4 = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $product->sku . '-41-BLUE',
            'price' => 1050000,
            'qty' => 0,
            'attributes' => ['Size' => '41', 'Color' => 'Blue'],
            'is_active' => false,
        ]);

        $product->refresh();
        $this->assertCount(4, $product->variants);
        $this->assertCount(3, $product->activeVariants);
        // Total active stock: 5 + 8 + 10 = 23
        $this->assertEquals(23, $product->available_stock);
        // Check sale price calculation on variant 3
        $this->assertEquals(990000, $v3->effective_price);
        $this->assertTrue($v3->is_on_sale);
    }

    /**
     * 3. Select a variant and purchase it.
     */
    public function test_3_select_variant_and_purchase_it(): void
    {
        $product = Product::create([
            'name' => 'Club Jersey',
            'slug' => 'club-jersey-' . uniqid(),
            'sku' => 'JERSEY-' . uniqid(),
            'type' => ProductType::Variable,
            'price' => 300000,
            'qty' => 15,
            'total_stock' => 15,
            'is_visible' => true,
            'apply_tax' => false,
        ]);

        $variantM = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $product->sku . '-M',
            'price' => 300000,
            'sale_price' => 250000,
            'qty' => 10,
            'attributes' => ['Size' => 'M'],
            'is_active' => true,
        ]);

        $dto = new OrderDataDTO(
            customerId: null,
            email: 'buyer@example.com',
            phone: '0912345678',
            shippingAddress: ['first_name' => 'Alice', 'last_name' => 'Smith', 'phone' => '0912345678', 'country' => 'VN', 'street' => '456 Le Loi', 'city' => 'HCMC'],
            billingAddress: ['first_name' => 'Alice', 'last_name' => 'Smith', 'phone' => '0912345678', 'country' => 'VN', 'street' => '456 Le Loi', 'city' => 'HCMC'],
            items: [
                ['product_id' => $product->id, 'variant_id' => $variantM->id, 'qty' => 2, 'price' => 250000],
            ],
            shippingMethod: 'flat_rate',
            grandTotal: 535000
        );

        $order = app(PlaceOrderAction::class)->execute($dto);
        $this->assertInstanceOf(Order::class, $order);

        // Verify order item captures variant details
        $orderItem = $order->items->first();
        $this->assertEquals($variantM->id, $orderItem->variant_id);
        $this->assertEquals('variable', $orderItem->metadata['product_type']);
        $this->assertEquals($variantM->sku, $orderItem->metadata['variant_sku']);

        // Verify variant stock deducted: 10 - 2 = 8
        $variantM->refresh();
        $this->assertEquals(8, $variantM->qty);
    }

    /**
     * 4. Create a Combo containing Simple and Variable Products.
     * 5. Validate combo component stock.
     */
    public function test_4_and_5_combo_creation_and_component_stock_validation(): void
    {
        // Component 1: Simple Shorts (Stock = 5)
        $shorts = Product::create([
            'name' => 'Club Shorts',
            'slug' => 'club-shorts-' . uniqid(),
            'sku' => 'SHORTS-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 150000,
            'qty' => 5,
            'total_stock' => 5,
            'is_visible' => true,
        ]);

        // Component 2: Variable Jersey with Size M variant (Stock = 10)
        $jersey = Product::create([
            'name' => 'Club Training Jersey',
            'slug' => 'club-training-jersey-' . uniqid(),
            'sku' => 'TJERSEY-' . uniqid(),
            'type' => ProductType::Variable,
            'price' => 200000,
            'qty' => 10,
            'total_stock' => 10,
            'is_visible' => true,
        ]);
        $jerseyM = ProductVariant::create([
            'product_id' => $jersey->id,
            'sku' => $jersey->sku . '-M',
            'price' => 200000,
            'qty' => 10,
            'attributes' => ['Size' => 'M'],
            'is_active' => true,
        ]);

        // Component 3: Simple Cap (Stock = 20)
        $cap = Product::create([
            'name' => 'Club Cap',
            'slug' => 'club-cap-' . uniqid(),
            'sku' => 'CAP-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 80000,
            'qty' => 20,
            'total_stock' => 20,
            'is_visible' => true,
        ]);

        // Create Combo Product
        $combo = Product::create([
            'name' => 'Club Training Combo',
            'slug' => 'club-training-combo-' . uniqid(),
            'sku' => 'COMBO-' . uniqid(),
            'type' => ProductType::Combo,
            'price' => 380000, // Discounted combo price (150k + 200k + 80k = 430k nominal)
            'old_price' => 430000,
            'is_visible' => true,
            'apply_tax' => false,
        ]);

        $this->assertTrue($combo->isCombo());

        // Attach components: 1x Shorts, 1x Jersey M, 1x Cap
        ComboProductItem::create([
            'parent_product_id' => $combo->id,
            'shop_product_id' => $shorts->id,
            'quantity' => 1,
            'sort_order' => 0,
        ]);
        ComboProductItem::create([
            'parent_product_id' => $combo->id,
            'shop_product_id' => $jersey->id,
            'product_variant_id' => $jerseyM->id,
            'quantity' => 1,
            'sort_order' => 1,
        ]);
        ComboProductItem::create([
            'parent_product_id' => $combo->id,
            'shop_product_id' => $cap->id,
            'quantity' => 1,
            'sort_order' => 2,
        ]);

        $combo->refresh();
        $this->assertCount(3, $combo->comboComponents);

        // 5. Validate combo component stock:
        // Shorts: 5, Jersey M: 10, Cap: 20 -> minimum available is 5!
        $this->assertEquals(5, $combo->available_stock);
        $this->assertEquals(5, $combo->calculateComboStock());
    }

    /**
     * 6. Purchase multiple combos and verify inventory deduction.
     */
    public function test_6_purchase_combos_and_deduct_component_stock(): void
    {
        $itemA = Product::create([
            'name' => 'Item A',
            'slug' => 'item-a-' . uniqid(),
            'sku' => 'ITA-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 100000,
            'qty' => 10,
            'total_stock' => 10,
            'is_visible' => true,
            'apply_tax' => false,
        ]);
        $itemB = Product::create([
            'name' => 'Item B',
            'slug' => 'item-b-' . uniqid(),
            'sku' => 'ITB-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 100000,
            'qty' => 15,
            'total_stock' => 15,
            'is_visible' => true,
            'apply_tax' => false,
        ]);

        $combo = Product::create([
            'name' => 'Pack of A & B',
            'slug' => 'pack-ab-' . uniqid(),
            'sku' => 'PACK-' . uniqid(),
            'type' => ProductType::Combo,
            'price' => 180000,
            'is_visible' => true,
            'apply_tax' => false,
        ]);

        ComboProductItem::create([
            'parent_product_id' => $combo->id,
            'shop_product_id' => $itemA->id,
            'quantity' => 2, // 2x Item A per combo
            'sort_order' => 0,
        ]);
        ComboProductItem::create([
            'parent_product_id' => $combo->id,
            'shop_product_id' => $itemB->id,
            'quantity' => 1, // 1x Item B per combo
            'sort_order' => 1,
        ]);

        // Item A stock = 10 (needs 2 per combo -> max 5 combos)
        // Item B stock = 15 (needs 1 per combo -> max 15 combos)
        $this->assertEquals(5, $combo->available_stock);

        // Purchase 2 combos:
        // Item A should deduct 2 * 2 = 4 units -> remaining 6
        // Item B should deduct 2 * 1 = 2 units -> remaining 13
        $dto = new OrderDataDTO(
            customerId: null,
            email: 'combo_buyer@example.com',
            phone: '0933333333',
            shippingAddress: ['first_name' => 'Bob', 'last_name' => 'Builder', 'phone' => '0933333333', 'country' => 'VN', 'street' => '789 D', 'city' => 'Danang'],
            billingAddress: ['first_name' => 'Bob', 'last_name' => 'Builder', 'phone' => '0933333333', 'country' => 'VN', 'street' => '789 D', 'city' => 'Danang'],
            items: [
                ['product_id' => $combo->id, 'qty' => 2, 'price' => 180000],
            ],
            shippingMethod: 'flat_rate',
            grandTotal: 395000
        );

        $order = app(PlaceOrderAction::class)->execute($dto);
        $this->assertInstanceOf(Order::class, $order);

        $itemA->refresh();
        $itemB->refresh();
        $this->assertEquals(6, $itemA->qty);
        $this->assertEquals(13, $itemB->qty);

        // New combo available stock: 6 / 2 = 3
        $combo->refresh();
        $this->assertEquals(3, $combo->available_stock);
    }

    /**
     * 7. Cancel an order and verify applicable stock restoration.
     */
    public function test_7_cancel_order_and_verify_stock_restoration(): void
    {
        $comp = Product::create([
            'name' => 'Component X',
            'slug' => 'comp-x-' . uniqid(),
            'sku' => 'CX-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 50000,
            'qty' => 10,
            'total_stock' => 10,
            'is_visible' => true,
            'apply_tax' => false,
        ]);

        $combo = Product::create([
            'name' => 'Combo X2',
            'slug' => 'combo-x2-' . uniqid(),
            'sku' => 'CX2-' . uniqid(),
            'type' => ProductType::Combo,
            'price' => 90000,
            'is_visible' => true,
            'apply_tax' => false,
        ]);

        ComboProductItem::create([
            'parent_product_id' => $combo->id,
            'shop_product_id' => $comp->id,
            'quantity' => 2,
            'sort_order' => 0,
        ]);

        $dto = new OrderDataDTO(
            customerId: null,
            email: 'cancel_test@example.com',
            phone: '0944444444',
            shippingAddress: ['first_name' => 'C', 'last_name' => 'Test', 'phone' => '0944444444', 'country' => 'VN', 'street' => 'St', 'city' => 'Hanoi'],
            billingAddress: ['first_name' => 'C', 'last_name' => 'Test', 'phone' => '0944444444', 'country' => 'VN', 'street' => 'St', 'city' => 'Hanoi'],
            items: [
                ['product_id' => $combo->id, 'qty' => 3, 'price' => 90000], // Deducts 3*2 = 6 units
            ],
            shippingMethod: 'flat_rate',
            grandTotal: 305000
        );

        $order = app(PlaceOrderAction::class)->execute($dto);
        $comp->refresh();
        $this->assertEquals(4, $comp->qty);

        // Now cancel order
        $orderService = app(OrderServiceInterface::class);
        $cancelled = $orderService->cancel($order, 'Customer requested cancellation');

        $this->assertTrue($cancelled);
        $order->refresh();
        $this->assertEquals(\App\Ecommerce\Order\Enums\OrderStatus::Cancelled, $order->status);

        // Verify stock is restored! 4 + 6 = 10
        $comp->refresh();
        $this->assertEquals(10, $comp->qty);
        $this->assertEquals(10, $comp->total_stock);
    }

    /**
     * 8. Apply tax settings to all three product types.
     */
    public function test_8_apply_tax_settings_to_all_three_product_types(): void
    {
        $taxService = app(TaxServiceInterface::class);
        $standardClass = TaxClass::where('slug', 'standard')->first();
        $this->assertNotNull($standardClass);

        // Simple Product with Standard Tax (10%)
        $simple = Product::create([
            'name' => 'Simple Tax Product',
            'slug' => 'simple-tax-' . uniqid(),
            'sku' => 'STAX-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 100000,
            'is_visible' => true,
            'apply_tax' => true,
            'tax_class_id' => $standardClass->id,
        ]);

        $simpleTax = $taxService->calculateProductTax($simple, null, 100000, 'VN');
        $this->assertGreaterThan(0, $simpleTax['amount']);
        $this->assertEquals(10.0, $simpleTax['rate_percent']);

        // Variable Product with Variant override
        $reducedClass = TaxClass::where('slug', 'reduced')->first();
        $variable = Product::create([
            'name' => 'Variable Tax Product',
            'slug' => 'var-tax-' . uniqid(),
            'sku' => 'VARTAX-' . uniqid(),
            'type' => ProductType::Variable,
            'price' => 200000,
            'is_visible' => true,
            'apply_tax' => true,
            'tax_class_id' => $standardClass->id,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $variable->id,
            'sku' => $variable->sku . '-VAR',
            'price' => 200000,
            'qty' => 5,
            'attributes' => ['Type' => 'Special'],
            'tax_class_id' => $reducedClass->id, // Reduced 8%
        ]);

        $varTax = $taxService->calculateProductTax($variable, $variant->id, 200000, 'VN');
        $this->assertEquals(8.0, $varTax['rate_percent']);
    }

    /**
     * 9. Test a combo containing different tax classes.
     */
    public function test_9_combo_containing_different_tax_classes(): void
    {
        $taxService = app(TaxServiceInterface::class);
        $standardClass = TaxClass::where('slug', 'standard')->first();
        $reducedClass = TaxClass::where('slug', 'reduced')->first();

        // Component 1: Taxable 10% (Nominal 100,000)
        $p1 = Product::create([
            'name' => 'Beverage 10%',
            'slug' => 'bev-' . uniqid(),
            'sku' => 'BEV-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 100000,
            'is_visible' => true,
            'apply_tax' => true,
            'tax_class_id' => $standardClass->id,
        ]);

        // Component 2: Taxable 8% (Nominal 100,000)
        $p2 = Product::create([
            'name' => 'Snack 8%',
            'slug' => 'snk-' . uniqid(),
            'sku' => 'SNK-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 100000,
            'is_visible' => true,
            'apply_tax' => true,
            'tax_class_id' => $reducedClass->id,
        ]);

        // Combo sold at 180,000 (Allocates 90k to p1, 90k to p2)
        $combo = Product::create([
            'name' => 'Mixed Tax Combo',
            'slug' => 'mixed-tax-combo-' . uniqid(),
            'sku' => 'MTC-' . uniqid(),
            'type' => ProductType::Combo,
            'price' => 180000,
            'is_visible' => true,
            'apply_tax' => true,
        ]);

        ComboProductItem::create([
            'parent_product_id' => $combo->id,
            'shop_product_id' => $p1->id,
            'quantity' => 1,
            'sort_order' => 0,
        ]);
        ComboProductItem::create([
            'parent_product_id' => $combo->id,
            'shop_product_id' => $p2->id,
            'quantity' => 1,
            'sort_order' => 1,
        ]);

        $calc = $taxService->calculateProductTax($combo, null, 180000, 'VN');
        $this->assertNotEmpty($calc['breakdown']);
        $this->assertCount(2, $calc['breakdown']);

        // Check allocation
        $part1 = collect($calc['breakdown'])->firstWhere('product_id', $p1->id);
        $part2 = collect($calc['breakdown'])->firstWhere('product_id', $p2->id);

        $this->assertEquals(90000, $part1['allocated_amount']);
        $this->assertEquals(10.0, $part1['rate_percent']);

        $this->assertEquals(90000, $part2['allocated_amount']);
        $this->assertEquals(8.0, $part2['rate_percent']);
    }

    /**
     * 10. Verify Admin and Website display consistent prices.
     */
    public function test_10_verify_price_consistency(): void
    {
        $product = Product::create([
            'name' => 'Consistent Price Item',
            'slug' => 'consistent-item-' . uniqid(),
            'sku' => 'CPI-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 200000,
            'sale_price' => 180000,
            'qty' => 50,
            'is_visible' => true,
        ]);

        $this->assertEquals(180000, $product->effective_price);

        $resource = new \App\Http\Resources\Api\ProductResource($product);
        $resArray = $resource->toArray(request());

        $this->assertEquals(200000, $resArray['price']);
        $this->assertEquals(180000, $resArray['sale_price']);
        $this->assertEquals(180000, $resArray['effective_price']);
        $this->assertTrue($resArray['is_on_sale']);
    }

    /**
     * 11. Verify guest and registered customer checkout.
     */
    public function test_11_verify_guest_and_registered_customer_checkout(): void
    {
        $prod = Product::create([
            'name' => 'Checkout Item',
            'slug' => 'chk-item-' . uniqid(),
            'sku' => 'CHK-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 100000,
            'qty' => 50,
            'total_stock' => 50,
            'is_visible' => true,
            'apply_tax' => false,
        ]);

        $user = User::factory()->create();

        // Registered checkout
        $dtoRegistered = new OrderDataDTO(
            customerId: $user->id,
            email: $user->email,
            phone: '0988888888',
            shippingAddress: ['first_name' => 'Reg', 'last_name' => 'User', 'phone' => '0988888888', 'country' => 'VN', 'street' => '1 Main', 'city' => 'Hanoi'],
            billingAddress: ['first_name' => 'Reg', 'last_name' => 'User', 'phone' => '0988888888', 'country' => 'VN', 'street' => '1 Main', 'city' => 'Hanoi'],
            items: [['product_id' => $prod->id, 'qty' => 1, 'price' => 100000]],
            shippingMethod: 'flat_rate',
            grandTotal: 135000
        );
        $orderReg = app(PlaceOrderAction::class)->execute($dtoRegistered);
        $this->assertEquals($user->id, $orderReg->user_id);

        // Guest checkout
        $dtoGuest = new OrderDataDTO(
            customerId: null,
            email: 'guest_user@example.com',
            phone: '0977777777',
            shippingAddress: ['first_name' => 'Guest', 'last_name' => 'User', 'phone' => '0977777777', 'country' => 'VN', 'street' => '2 Main', 'city' => 'Hanoi'],
            billingAddress: ['first_name' => 'Guest', 'last_name' => 'User', 'phone' => '0977777777', 'country' => 'VN', 'street' => '2 Main', 'city' => 'Hanoi'],
            items: [['product_id' => $prod->id, 'qty' => 1, 'price' => 100000]],
            shippingMethod: 'flat_rate',
            grandTotal: 135000
        );
        $orderGuest = app(PlaceOrderAction::class)->execute($dtoGuest);
        $this->assertNull($orderGuest->user_id);
    }

    /**
     * 12. Verify AI Agents retrieve accurate product data.
     */
    public function test_12_verify_ai_agent_product_retrieval(): void
    {
        $product = Product::create([
            'name' => 'AI Validated Product',
            'slug' => 'ai-val-' . uniqid(),
            'sku' => 'AIV-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 350000,
            'qty' => 12,
            'is_visible' => true,
        ]);

        $controller = app(\App\Http\Controllers\Api\Agent\AgentProductController::class);
        $response = $controller->show($product->id);

        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true)['data'];

        $this->assertEquals($product->id, $content['id']);
        $this->assertEquals('simple', $content['product_type']);
        $this->assertEquals(350000, $content['price']);
        $this->assertEquals(12, $content['available_stock']);
    }

    /**
     * 13. Verify historical orders retain original prices and taxes.
     */
    public function test_13_historical_orders_retain_snapshots(): void
    {
        $product = Product::create([
            'name' => 'Price Volatile Product',
            'slug' => 'volatile-' . uniqid(),
            'sku' => 'VOL-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 100000,
            'qty' => 20,
            'total_stock' => 20,
            'is_visible' => true,
            'apply_tax' => false,
        ]);

        $dto = new OrderDataDTO(
            customerId: null,
            email: 'history@example.com',
            phone: '0966666666',
            shippingAddress: ['first_name' => 'H', 'last_name' => 'O', 'phone' => '0966666666', 'country' => 'VN', 'street' => 'St', 'city' => 'Hanoi'],
            billingAddress: ['first_name' => 'H', 'last_name' => 'O', 'phone' => '0966666666', 'country' => 'VN', 'street' => 'St', 'city' => 'Hanoi'],
            items: [['product_id' => $product->id, 'qty' => 2, 'price' => 100000]],
            shippingMethod: 'flat_rate',
            grandTotal: 235000
        );

        $order = app(PlaceOrderAction::class)->execute($dto);
        $this->assertEquals(200000, $order->subtotal);

        // Now modify current product price in database to 300,000
        $product->update(['price' => 300000]);

        // Historical order item must retain 100,000 unit_price and 200,000 total!
        $order->refresh();
        $this->assertEquals(200000, $order->subtotal);
        $orderItem = $order->items->first();
        $this->assertEquals(100000, $orderItem->unit_price);
        $this->assertEquals(200000, $orderItem->total);
    }

    /**
     * 14. Verify existing products remain compatible after migrations.
     */
    public function test_14_existing_products_remain_compatible(): void
    {
        // Deliverable type products in legacy schema
        $legacy = Product::create([
            'name' => 'Legacy Deliverable Product',
            'slug' => 'legacy-deliv-' . uniqid(),
            'sku' => 'LEGACY-' . uniqid(),
            'type' => ProductType::Deliverable,
            'price' => 120000,
            'qty' => 15,
            'total_stock' => 15,
            'is_visible' => true,
        ]);

        $this->assertTrue($legacy->isSimple());
        $this->assertEquals(15, $legacy->available_stock);
        $this->assertEquals(120000, $legacy->effective_price);
    }
}
