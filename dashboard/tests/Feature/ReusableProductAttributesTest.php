<?php

namespace Tests\Feature;

use App\Ecommerce\Product\Services\ProductAttributeService;
use App\Models\Attribute;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReusableProductAttributesTest extends TestCase
{
    use DatabaseTransactions;

    protected ProductAttributeService $service;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ProductAttributeService::class);
        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_attr_test@example.com'],
            ['name' => 'Admin Test', 'password' => bcrypt('password')]
        );
        $this->adminUser->assignRole('super_admin');
    }

    /**
     * 1. Global Attribute Creation & Value Deduplication
     */
    public function test_can_create_global_attribute_and_deduplicate_values(): void
    {
        $attr = $this->service->findOrCreateAttribute('Kích cỡ Test', null, ['S', 'M', 'L', 'S', 'M']);

        $this->assertDatabaseHas('shop_attributes', [
            'id' => $attr->id,
            'name' => 'Kích cỡ Test',
            'is_enabled' => true,
        ]);

        $this->assertEquals(['S', 'M', 'L'], $attr->fresh()->values);
    }

    /**
     * 2. Merge newly added values into an existing attribute
     */
    public function test_can_merge_new_values_into_existing_attribute(): void
    {
        $attr = $this->service->findOrCreateAttribute('Chất liệu Vải', null, ['Cotton', 'Polyester']);
        $this->assertEquals(['Cotton', 'Polyester'], $attr->fresh()->values);

        // Merge existing and new values
        $this->service->findOrCreateAttribute('Chất liệu Vải', $attr->id, ['Polyester', 'Linen', 'Kaki']);

        $attr->refresh();
        $this->assertEquals(['Cotton', 'Polyester', 'Linen', 'Kaki'], $attr->values);
    }

    /**
     * 3. Toggle attribute status (enable / disable)
     */
    public function test_can_toggle_attribute_status(): void
    {
        $attr = Attribute::create([
            'name' => 'Độ dài tay áo',
            'slug' => 'do-dai-tay-ao',
            'type' => 'button',
            'values' => ['Ngắn tay', 'Dài tay'],
            'is_enabled' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.attributes.toggle-status', $attr->id));
        $response->assertStatus(302);

        $this->assertFalse($attr->fresh()->is_enabled);

        // Toggle back to enabled
        $this->actingAs($this->adminUser)->post(route('admin.attributes.toggle-status', $attr->id));
        $this->assertTrue($attr->fresh()->is_enabled);
    }

    /**
     * 4. Product A creates an inline attribute -> saved globally -> Product B reuses it
     */
    public function test_product_a_creates_inline_attribute_and_product_b_reuses_it(): void
    {
        $prodA = Product::create([
            'name' => 'Áo Thun Polo A',
            'slug' => 'ao-thun-polo-a-' . uniqid(),
            'sku' => 'POLO-A-' . uniqid(),
            'price' => 250000,
            'type' => 'variable',
        ]);

        // Product A defines a new attribute 'Form áo' with values 'Slim Fit', 'Regular'
        $this->service->syncProductAttributes($prodA, [
            [
                'name' => 'Form áo',
                'options' => ['Slim Fit', 'Regular'],
                'is_variation' => true,
            ]
        ]);

        // Assert attribute is stored in shop_attributes globally
        $globalAttr = Attribute::where('name', 'Form áo')->first();
        $this->assertNotNull($globalAttr);
        $this->assertEquals(['Slim Fit', 'Regular'], $globalAttr->values);

        // Assert Product A links to this global attribute
        $this->assertDatabaseHas('shop_product_attributes', [
            'product_id' => $prodA->id,
            'attribute_id' => $globalAttr->id,
            'name' => 'Form áo',
        ]);

        // Product B comes along and selects the existing 'Form áo' attribute and adds 'Oversized'
        $prodB = Product::create([
            'name' => 'Áo Sơ Mi B',
            'slug' => 'ao-so-mi-b-' . uniqid(),
            'sku' => 'SOMI-B-' . uniqid(),
            'price' => 350000,
            'type' => 'variable',
        ]);

        $this->service->syncProductAttributes($prodB, [
            [
                'attribute_id' => $globalAttr->id,
                'name' => 'Form áo',
                'options' => ['Slim Fit', 'Oversized'],
                'is_variation' => true,
            ]
        ]);

        // Both products must reference the SAME global attribute ID
        $prodAAttr = ProductAttribute::where('product_id', $prodA->id)->first();
        $prodBAttr = ProductAttribute::where('product_id', $prodB->id)->first();

        $this->assertEquals($globalAttr->id, $prodAAttr->attribute_id);
        $this->assertEquals($globalAttr->id, $prodBAttr->attribute_id);

        // The global attribute now contains all combined values: ['Slim Fit', 'Regular', 'Oversized']
        $globalAttr->refresh();
        $this->assertEquals(['Slim Fit', 'Regular', 'Oversized'], $globalAttr->values);

        // Product A's options remain strictly ['Slim Fit', 'Regular']
        $this->assertEquals(['Slim Fit', 'Regular'], $prodAAttr->fresh()->options);

        // Product B's options remain strictly ['Slim Fit', 'Oversized']
        $this->assertEquals(['Slim Fit', 'Oversized'], $prodBAttr->fresh()->options);
    }

    /**
     * 5. Variant Generation and Regeneration Safety: preserving price and stock
     */
    public function test_variant_generation_preserves_existing_custom_prices_and_stock(): void
    {
        $product = Product::create([
            'name' => 'Giày Thể Thao Pro',
            'slug' => 'giay-pro-' . uniqid(),
            'sku' => 'SHOE-PRO',
            'price' => 1200000,
            'type' => 'variable',
        ]);

        // 1. Initial variants setup
        $initialVariants = [
            [
                'sku' => 'SHOE-PRO-40',
                'price' => 1200000,
                'sale_price' => 1100000,
                'qty' => 15,
                'attributes' => ['Size' => '40'],
                'is_active' => true,
            ],
            [
                'sku' => 'SHOE-PRO-41',
                'price' => 1250000,
                'sale_price' => null,
                'qty' => 20,
                'attributes' => ['Size' => '41'],
                'is_active' => true,
            ],
        ];

        $this->service->syncProductVariants($product, $initialVariants);
        $var40 = ProductVariant::where('product_id', $product->id)->whereJsonContains('attributes->Size', '40')->first();
        $this->assertNotNull($var40);
        $this->assertEquals(15, $var40->qty);
        $this->assertEquals(1200000, $var40->price);
        $this->assertEquals(1100000, $var40->sale_price);

        // 2. Admin adds size 42. Existing size 40 and 41 must preserve their prices, stock, and IDs!
        $updatedVariants = [
            [
                'sku' => 'SHOE-PRO-40',
                'price' => 1200000,
                'sale_price' => 1100000,
                'qty' => 15,
                'attributes' => ['Size' => '40'],
                'is_active' => true,
            ],
            [
                'sku' => 'SHOE-PRO-41',
                'price' => 1250000,
                'sale_price' => null,
                'qty' => 20,
                'attributes' => ['Size' => '41'],
                'is_active' => true,
            ],
            [
                'sku' => 'SHOE-PRO-42',
                'price' => 1300000,
                'sale_price' => null,
                'qty' => 10,
                'attributes' => ['Size' => '42'],
                'is_active' => true,
            ],
        ];

        $this->service->syncProductVariants($product, $updatedVariants);

        $var40After = ProductVariant::where('product_id', $product->id)->whereJsonContains('attributes->Size', '40')->first();
        // Crucial: ID must be preserved, not wiped and re-incremented
        $this->assertEquals($var40->id, $var40After->id);
        $this->assertEquals(15, $var40After->qty);
        $this->assertEquals(1100000, $var40After->sale_price);

        $this->assertEquals(3, $product->variants()->count());
        $this->assertEquals(45, $product->fresh()->qty);
    }

    /**
     * 6. Deletion safety: Variants referenced by existing orders must NOT be hard-deleted
     */
    public function test_variants_referenced_in_orders_are_deactivated_not_deleted(): void
    {
        $product = Product::create([
            'name' => 'Áo Thun Limited',
            'slug' => 'ao-thun-limited-' . uniqid(),
            'sku' => 'SHIRT-LTD',
            'price' => 200000,
            'type' => 'variable',
        ]);

        $this->service->syncProductVariants($product, [
            [
                'sku' => 'SHIRT-LTD-S',
                'price' => 200000,
                'qty' => 10,
                'attributes' => ['Size' => 'S'],
            ],
            [
                'sku' => 'SHIRT-LTD-M',
                'price' => 200000,
                'qty' => 10,
                'attributes' => ['Size' => 'M'],
            ],
        ]);

        $varS = ProductVariant::where('sku', 'SHIRT-LTD-S')->first();
        $varM = ProductVariant::where('sku', 'SHIRT-LTD-M')->first();

        // Simulate an order placing variant S
        $order = Order::create([
            'number' => 'ORD-ATTR-' . uniqid(),
            'user_id' => $this->adminUser->id,
            'status' => 'completed',
            'subtotal' => 200000,
            'total' => 200000,
            'tax_amount' => 0,
            'currency' => 'VND',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'shop_product_id' => $product->id,
            'variant_id' => $varS->id,
            'name' => $product->name,
            'qty' => 1,
            'unit_price' => 200000,
            'total' => 200000,
        ]);

        // Now admin removes Size S and keeps only Size M
        $this->service->syncProductVariants($product, [
            [
                'sku' => 'SHIRT-LTD-M',
                'price' => 200000,
                'qty' => 10,
                'attributes' => ['Size' => 'M'],
            ],
        ]);

        // Var S MUST NOT be hard-deleted because it is referenced in an order! It must be disabled (is_active = false)
        $this->assertDatabaseHas('shop_product_variants', [
            'id' => $varS->id,
            'is_active' => false,
        ]);

        // Order item variant link remains intact
        $this->assertEquals($varS->id, OrderItem::where('order_id', $order->id)->first()->variant_id);
    }

    /**
     * 7. Admin AJAX inlineStore endpoint
     */
    public function test_admin_ajax_inline_store_attribute(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson(route('admin.attributes.inline-store'), [
            'name' => 'Họa tiết áo',
            'type' => 'button',
            'values' => 'Kẻ sọc, Chấm bi, Trơn',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'attribute' => [
                    'name' => 'Họa tiết áo',
                    'values' => ['Kẻ sọc', 'Chấm bi', 'Trơn'],
                ],
            ]);

        $this->assertDatabaseHas('shop_attributes', [
            'name' => 'Họa tiết áo',
        ]);
    }

    /**
     * 8. API endpoints for global attributes
     */
    public function test_api_can_list_and_generate_variants(): void
    {
        Attribute::create([
            'name' => 'Bộ nhớ RAM',
            'slug' => 'ram',
            'type' => 'button',
            'values' => ['8GB', '16GB', '32GB'],
            'is_enabled' => true,
        ]);

        // 1. List attributes
        $resList = $this->getJson('/api/v1/storefront/attributes');
        $resList->assertStatus(200);

        // 2. Generate variants preview API
        $resPreview = $this->postJson('/api/v1/storefront/attributes/generate-variants', [
            'attributes' => [
                ['name' => 'RAM', 'options' => ['8GB', '16GB']],
                ['name' => 'Storage', 'options' => ['256GB', '512GB']],
            ],
            'base_sku' => 'LAPTOP',
            'base_price' => 15000000,
        ]);

        $resPreview->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'combinations_count' => 4,
                ]
            ]);
    }

    /**
     * 9. Product can have multiple brands synced correctly
     */
    public function test_product_can_have_multiple_brands_and_sync_correctly(): void
    {
        $brand1 = \App\Models\Brand::create(['name' => 'Brand Alpha ' . uniqid(), 'slug' => 'brand-alpha-' . uniqid()]);
        $brand2 = \App\Models\Brand::create(['name' => 'Brand Beta ' . uniqid(), 'slug' => 'brand-beta-' . uniqid()]);

        $product = Product::create([
            'name' => 'Multi Brand Product ' . uniqid(),
            'sku' => 'MBP-' . uniqid(),
            'slug' => 'multi-brand-prod-' . uniqid(),
            'type' => 'simple',
            'price' => 100000,
        ]);

        $product->brands()->sync([$brand1->id, $brand2->id]);

        $this->assertCount(2, $product->fresh()->brands);
        $this->assertTrue($product->fresh()->brands->contains('id', $brand1->id));
        $this->assertTrue($product->fresh()->brands->contains('id', $brand2->id));

        $this->assertDatabaseHas('shop_brand_product', [
            'shop_product_id' => $product->id,
            'shop_brand_id' => $brand1->id,
        ]);
        $this->assertDatabaseHas('shop_brand_product', [
            'shop_product_id' => $product->id,
            'shop_brand_id' => $brand2->id,
        ]);
    }
}
