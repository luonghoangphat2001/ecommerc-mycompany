<?php

namespace Tests\Feature;

use App\Ecommerce\Checkout\Actions\PlaceOrderAction;
use App\Ecommerce\Order\Actions\RefundOrderAction;
use App\Ecommerce\Order\Contracts\OrderServiceInterface;
use App\Ecommerce\Order\DTOs\Order\OrderDataDTO;
use App\Enums\ProductType;
use App\Models\ComboProductItem;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\TaxClass;
use App\Models\TaxRate;
use App\Settings\InventorySettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WarehouseCheckoutMovementTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $taxClass = TaxClass::firstOrCreate(['slug' => 'warehouse-test'], ['name' => 'Warehouse Test']);
        TaxRate::firstOrCreate(
            ['tax_class_id' => $taxClass->id, 'country' => 'VN'],
            ['name' => 'Warehouse VAT', 'rate' => 0, 'priority' => 1, 'is_compound' => false, 'is_shipping' => false]
        );
        $zone = ShippingZone::firstOrCreate(['code' => 'WAREHOUSE_TEST'], ['name' => 'Warehouse Test Zone', 'is_active' => true]);
        ShippingMethod::firstOrCreate(
            ['type' => 'flat_rate'],
            ['shipping_zone_id' => $zone->id, 'name' => 'Warehouse Test Shipping', 'settings' => ['cost' => 35000], 'is_enabled' => true]
        );

        $settings = app(InventorySettings::class);
        $settings->multi_warehouse_enabled = true;
        $settings->save();
    }

    public function test_checkout_records_actual_warehouse_and_restores_from_same_source(): void
    {
        $product = Product::create([
            'name' => 'Warehouse checkout product',
            'slug' => 'warehouse-checkout-' . uniqid(),
            'sku' => 'WCH-' . uniqid(),
            'type' => ProductType::Simple,
            'price' => 100000,
            'qty' => 27,
            'total_stock' => 27,
            'is_visible' => true,
            'apply_tax' => false,
        ]);
        $main = Inventory::create(['name' => 'Test Main', 'slug' => 'test-main-' . uniqid(), 'location' => 'HCM', 'is_active' => true]);
        $hcm = Inventory::create(['name' => 'Test HCM', 'slug' => 'test-hcm-' . uniqid(), 'location' => 'HCM', 'is_active' => true]);
        DB::table('shop_product_inventory_stocks')->insert([
            ['shop_product_id' => $product->id, 'warehouse_id' => $main->id, 'variant_id' => null, 'stock_quantity' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['shop_product_id' => $product->id, 'warehouse_id' => $hcm->id, 'variant_id' => null, 'stock_quantity' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $order = app(PlaceOrderAction::class)->execute(new OrderDataDTO(
            customerId: null,
            email: 'warehouse-checkout@example.com',
            phone: '0900000000',
            shippingAddress: ['first_name' => 'Warehouse', 'last_name' => 'Test', 'phone' => '0900000000', 'country' => 'VN', 'street' => 'Test', 'city' => 'HCMC'],
            billingAddress: ['first_name' => 'Warehouse', 'last_name' => 'Test', 'phone' => '0900000000', 'country' => 'VN', 'street' => 'Test', 'city' => 'HCMC'],
            items: [['product_id' => $product->id, 'qty' => 1, 'price' => 100000]],
            shippingMethod: 'flat_rate',
            grandTotal: 135000,
        ));

        $movement = DB::table('shop_inventory_movements')
            ->join('shop_inventory_movement_details as movement_details', 'movement_details.inventory_movement_id', '=', 'shop_inventory_movements.id')
            ->where('movement_details.source_type', \App\Models\OrderItem::class)
            ->where('movement_details.source_id', $order->items()->first()->id)
            ->select('shop_inventory_movements.id as movement_id', 'shop_inventory_movements.quantity_changed', 'movement_details.*')
            ->first();
        $item = $order->items()->first();
        $this->assertNotNull($movement);
        $this->assertSame($item->id, (int) $movement->source_id);
        $this->assertSame($main->id, (int) $movement->warehouse_id);
        $this->assertSame($main->id, (int) $movement->inventory_id);
        $this->assertSame(-1, (int) $movement->quantity_changed);
        $this->assertSame(19, (int) DB::table('shop_product_inventory_stocks')->where('id', DB::table('shop_product_inventory_stocks')->where('warehouse_id', $main->id)->value('id'))->value('stock_quantity'));

        $refund = app(RefundOrderAction::class)->execute(
            $order,
            100000,
            'Returned sellable unit',
            ['disposition' => 'restockable', 'items' => [['order_item_id' => $item->id, 'quantity' => 1]]],
            'warehouse-refund-1'
        );
        $this->assertNotNull($refund);
        $this->assertSame(20, (int) DB::table('shop_product_inventory_stocks')->where('warehouse_id', $main->id)->value('stock_quantity'));
        $this->assertSame(1, DB::table('shop_inventory_movement_details')->where('restoration_of_movement_id', $movement->movement_id)->count());

        $duplicateRefund = app(RefundOrderAction::class)->execute(
            $order,
            100000,
            'Duplicate callback',
            ['disposition' => 'restockable', 'items' => [['order_item_id' => $item->id, 'quantity' => 1]]],
            'warehouse-refund-1'
        );
        $this->assertSame($refund->id, $duplicateRefund->id);
        $this->assertSame(20, (int) DB::table('shop_product_inventory_stocks')->where('warehouse_id', $main->id)->value('stock_quantity'));

        app(OrderServiceInterface::class)->cancel($order, 'Warehouse test cancellation');

        $restoration = DB::table('shop_inventory_movement_details')
            ->join('shop_inventory_movements', 'shop_inventory_movements.id', '=', 'shop_inventory_movement_details.inventory_movement_id')
            ->where('shop_inventory_movements.movement_type', 'order_stock_restoration')
            ->first();
        $this->assertNotNull($restoration);
        $this->assertSame($movement->movement_id, (int) $restoration->restoration_of_movement_id);
        $this->assertSame(20, (int) DB::table('shop_product_inventory_stocks')->where('warehouse_id', $main->id)->value('stock_quantity'));
        $this->assertSame(5, (int) DB::table('shop_product_inventory_stocks')->where('warehouse_id', $hcm->id)->value('stock_quantity'));
    }

    public function test_variant_inventory_movement_uses_variant_source(): void
    {
        $product = Product::create([
            'name' => 'Warehouse variant product', 'slug' => 'warehouse-variant-' . uniqid(), 'sku' => 'WVAR-' . uniqid(),
            'type' => ProductType::Variable, 'price' => 100000, 'qty' => 10, 'total_stock' => 10, 'is_visible' => true, 'apply_tax' => false,
        ]);
        $variant = $product->variants()->create(['sku' => 'WVAR-M-' . uniqid(), 'price' => 100000, 'qty' => 10, 'attributes' => ['size' => 'M'], 'is_active' => true]);
        $warehouse = Inventory::create(['name' => 'Variant Warehouse', 'slug' => 'variant-wh-' . uniqid(), 'location' => 'HCM', 'is_active' => true]);
        DB::table('shop_product_inventory_stocks')->insert([
            'shop_product_id' => $product->id, 'variant_id' => $variant->id, 'warehouse_id' => $warehouse->id,
            'stock_quantity' => 10, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $order = $this->placeOrder($product->id, $variant->id, 100000, 'variant@example.com');
        $movement = DB::table('shop_inventory_movement_details')
            ->where('source_type', \App\Models\OrderItem::class)
            ->where('source_id', $order->items()->first()->id)
            ->first();
        $this->assertSame($variant->id, (int) $movement->variant_id);
        $this->assertSame($warehouse->id, (int) $movement->inventory_id);
        $this->assertSame(-1, (int) DB::table('shop_inventory_movements')->where('id', $movement->inventory_movement_id)->value('quantity_changed'));

        app(OrderServiceInterface::class)->cancel($order, 'Variant cancellation');
        $this->assertSame(10, (int) DB::table('shop_product_inventory_stocks')->where('warehouse_id', $warehouse->id)->value('stock_quantity'));
    }

    public function test_combo_deducts_and_restores_component_movement_at_original_source(): void
    {
        $component = Product::create([
            'name' => 'Warehouse combo component', 'slug' => 'warehouse-component-' . uniqid(), 'sku' => 'WCOMP-' . uniqid(),
            'type' => ProductType::Simple, 'price' => 50000, 'qty' => 10, 'total_stock' => 10, 'is_visible' => true, 'apply_tax' => false,
        ]);
        $combo = Product::create([
            'name' => 'Warehouse combo', 'slug' => 'warehouse-combo-' . uniqid(), 'sku' => 'WCOMBO-' . uniqid(),
            'type' => ProductType::Combo, 'price' => 100000, 'qty' => 5, 'total_stock' => 5, 'is_visible' => true, 'apply_tax' => false,
        ]);
        ComboProductItem::create(['parent_product_id' => $combo->id, 'shop_product_id' => $component->id, 'quantity' => 2, 'sort_order' => 0]);
        $warehouse = Inventory::create(['name' => 'Combo Warehouse', 'slug' => 'combo-wh-' . uniqid(), 'location' => 'HCM', 'is_active' => true]);
        DB::table('shop_product_inventory_stocks')->insert([
            'shop_product_id' => $component->id, 'variant_id' => null, 'warehouse_id' => $warehouse->id,
            'stock_quantity' => 10, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $order = $this->placeOrder($combo->id, null, 100000, 'combo@example.com');
        $movement = DB::table('shop_inventory_movement_details')
            ->where('source_type', \App\Models\OrderItem::class)
            ->where('source_id', $order->items()->first()->id)
            ->first();
        $this->assertSame($component->id, (int) $movement->shop_product_id);
        $this->assertSame(-2, (int) DB::table('shop_inventory_movements')->where('id', $movement->inventory_movement_id)->value('quantity_changed'));
        $this->assertSame($warehouse->id, (int) $movement->warehouse_id);

        app(OrderServiceInterface::class)->cancel($order, 'Combo cancellation');
        $this->assertSame(10, (int) DB::table('shop_product_inventory_stocks')->where('warehouse_id', $warehouse->id)->value('stock_quantity'));
    }

    private function placeOrder(int $productId, ?int $variantId, int $price, string $email): Order
    {
        return app(PlaceOrderAction::class)->execute(new OrderDataDTO(
            customerId: null,
            email: $email,
            phone: '0900000001',
            shippingAddress: ['first_name' => 'Warehouse', 'last_name' => 'Test', 'phone' => '0900000001', 'country' => 'VN', 'street' => 'Test', 'city' => 'HCMC'],
            billingAddress: ['first_name' => 'Warehouse', 'last_name' => 'Test', 'phone' => '0900000001', 'country' => 'VN', 'street' => 'Test', 'city' => 'HCMC'],
            items: [['product_id' => $productId, 'variant_id' => $variantId, 'qty' => 1, 'price' => $price]],
            shippingMethod: 'flat_rate',
            grandTotal: $price + 35000,
        ));
    }
}
