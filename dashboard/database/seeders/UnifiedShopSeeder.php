<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Brand;
use App\Models\OrderAddress;
use App\Models\TaxClass;
use App\Models\TaxRate;
use App\Models\ShippingZone;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Models\Payment;
use App\Ecommerce\Order\Enums\OrderStatus;
use App\Ecommerce\Order\Contracts\OrderServiceInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UnifiedShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. Ensure a super admin or admin exists
        $user = User::first() ?? User::factory()->create();

        // 1. Tax Data (Unified Factory approach)
        $this->seedTaxData();

        // 2. Shipping Data (Unified Factory approach)
        $this->seedShippingData();

        // 3. Brands & Categories
        $brands = $this->seedBrands();
        $categories = $this->seedCategories();

        // 4. Products
        $products = $this->seedProducts($brands, $categories, $user);

        // 5. Orders (The most complex part)
        $this->seedOrders($products);
    }

    protected function seedTaxData(): void
    {
        $standardTax = TaxClass::firstOrCreate(['slug' => 'standard'], ['name' => 'Standard']);
        TaxRate::updateOrCreate(
            ['tax_class_id' => $standardTax->id, 'country' => 'VN', 'name' => 'VAT 10%'],
            ['rate' => 10, 'priority' => 1]
        );

        $reducedTax = TaxClass::firstOrCreate(['slug' => 'reduced'], ['name' => 'Reduced']);
        TaxRate::updateOrCreate(
            ['tax_class_id' => $reducedTax->id, 'country' => 'VN', 'name' => 'VAT 8%'],
            ['rate' => 8, 'priority' => 1]
        );
    }

    protected function seedShippingData(): void
    {
        $vnZone = ShippingZone::firstOrCreate(
            ['name' => 'Vietnam'],
            ['locations' => [['country' => 'VN', 'state' => '*']], 'sort' => 1]
        );

        ShippingMethod::updateOrCreate(
            ['shipping_zone_id' => $vnZone->id, 'name' => 'Flat Rate Standard'],
            ['type' => 'flat_rate', 'settings' => ['cost' => 35000], 'is_enabled' => true]
        );
    }

    protected function seedBrands(): \Illuminate\Database\Eloquent\Collection
    {
        if (Brand::count() < 5) {
            Brand::factory()->count(10)->create()->each(function ($brand) {
                $brand->addresses()->create([
                    'country_code' => 'VN',
                    'address_detail' => fake()->streetAddress(),
                    'state_id' => 'Thành phố Hồ Chí Minh',
                    'city_id' => 'Quận 1',
                    'ward_id' => 'Phường Bến Nghé',
                    'postal_code' => '70000',
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'phone' => fake()->phoneNumber(),
                    'email' => fake()->safeEmail(),
                ]);
            });
        }
        return Brand::all();
    }

    protected function seedCategories(): \Illuminate\Support\Collection
    {
        if (ProductCategory::count() === 0) {
            $categoryData = include database_path('data/category_shop.php');
            foreach ($categoryData as $parentName => $children) {
                $parent = ProductCategory::factory()->create([
                    'name' => ['vi' => $parentName, 'en' => 'EN: ' . $parentName],
                    'slug' => Str::slug($parentName),
                ]);

                foreach ($children as $childName) {
                    ProductCategory::factory()->create([
                        'parent_id' => $parent->id,
                        'name' => ['vi' => $childName, 'en' => 'EN: ' . $childName],
                        'slug' => Str::slug($childName),
                    ]);
                }
            }
        }
        return ProductCategory::all();
    }

    protected function seedProducts($brands, $categories, $user): \Illuminate\Database\Eloquent\Collection
    {
        if (Product::count() < 10) {
            $taxClasses = TaxClass::all();
            
            Product::factory()
                ->count(30)
                ->create([
                    'author_id' => $user->id,
                ])
                ->each(function (Product $product) use ($brands, $categories, $taxClasses) {
                    $product->update([
                        'shop_brand_id' => $brands->random()->id,
                        'tax_class_id' => $taxClasses->random()->id,
                    ]);
                    $product->categories()->attach($categories->random(rand(1, 2))->pluck('id')->toArray());
                });
        }
        return Product::all();
    }

    protected function seedOrders($products): void
    {
        $customers = User::role('Thành viên đăng ký')->get();
        if ($customers->isEmpty()) {
            $customers = User::factory()->count(5)->create();
            $customers->each(fn($c) => $c->assignRole('Thành viên đăng ký'));
        }

        Order::factory()
            ->count(15)
            ->create()
            ->each(function (Order $order) use ($products, $customers) {
                if ($products->isEmpty()) return;

                // 1. Assign Customer (or guest)
                $isGuest = rand(1, 100) > 80;
                $customer = $isGuest ? null : $customers->random();
                $order->update(['user_id' => $customer?->id]);

                // 2. Add Items
                $selectedProducts = $products->random(rand(1, 3));
                foreach ($selectedProducts as $product) {
                    $qty = rand(1, 2);
                    $price = $product->price ?? 150000;
                    
                    OrderItem::create([
                        'order_id' => $order->id,
                        'shop_product_id' => $product->id,
                        'type' => 'product',
                        'name' => $product->getTranslation('name', 'vi'),
                        'qty' => $qty,
                        'unit_price' => $price,
                        'total' => $price * $qty,
                    ]);
                }

                // 3. Addresses (Using Factory)
                $this->seedOrderAddresses($order);

                // 4. Recalculate via Service (The correct Architectural Way)
                app(OrderServiceInterface::class)->recalculateTotals($order);

                // 5. Payment
                $this->seedOrderPayment($order);
            });
    }

    protected function seedOrderAddresses(Order $order): void
    {
        $fName = fake()->firstName();
        $lName = fake()->lastName();
        $email = fake()->safeEmail();
        $phone = fake()->phoneNumber();

        foreach (['shipping', 'billing'] as $type) {
            OrderAddress::factory()->create([
                'addressable_id' => $order->id,
                'addressable_type' => \App\Models\Order::class,
                'type' => $type,
                'first_name' => $fName,
                'last_name' => $lName,
                'email' => $email,
                'phone' => fake()->phoneNumber(), // Standardized phone
                'country_code' => 'VN',
                'state_id' => 'Thành phố Hồ Chí Minh',
                'city_id' => 'Quận 1',
                'ward_id' => 'Phường Bến Nghé',
            ]);
        }
    }

    protected function seedOrderPayment(Order $order): void
    {
        Payment::create([
            'order_id' => $order->id,
            'reference' => 'PAY-' . strtoupper(Str::random(10)),
            'amount' => $order->total,
            'currency' => 'VND',
            'provider' => 'COD',
            'method' => 'cash',
            'status' => 'success'
        ]);
    }
}
