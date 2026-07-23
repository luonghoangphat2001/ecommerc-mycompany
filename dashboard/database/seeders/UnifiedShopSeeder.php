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
use Illuminate\Support\Facades\Schema;

class UnifiedShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Product::truncate();
        ProductCategory::truncate();
        Brand::truncate();
        Order::truncate();
        OrderItem::truncate();
        OrderAddress::truncate();
        Payment::truncate();
        DB::table('shop_category_product')->truncate();
        DB::table('shop_brand_product')->truncate();
        Schema::enableForeignKeyConstraints();

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
        $fbBrands = [
            [
                'name' => ['vi' => 'Nhà máy Bia Heineken Việt Nam', 'en' => 'Heineken Vietnam Brewery'],
                'description' => ['vi' => 'Nhà cung cấp chính thức các dòng sản phẩm bia Heineken, Tiger và Larue.', 'en' => 'Official supplier of Heineken, Tiger, and Larue beer products.'],
            ],
            [
                'name' => ['vi' => 'Nhà cung cấp Nguyên liệu F&B Sài Gòn', 'en' => 'Saigon F&B Ingredients Supply'],
                'description' => ['vi' => 'Cung cấp nguyên liệu tươi ngon, chất lượng cao cho ngành dịch vụ ăn uống.', 'en' => 'Providing fresh, high-quality ingredients for the food and beverage industry.'],
            ],
            [
                'name' => ['vi' => 'Công ty Thực phẩm Cao cấp Classic Fine Foods', 'en' => 'Classic Fine Foods Vietnam'],
                'description' => ['vi' => 'Nhập khẩu và phân phối thực phẩm cao cấp từ Châu Âu.', 'en' => 'Importer and distributor of premium fine food products from Europe.'],
            ],
            [
                'name' => ['vi' => 'Tổng kho Rượu vang & Rượu mạnh Cellar', 'en' => 'Wine & Spirits Cellar Group'],
                'description' => ['vi' => 'Nhà phân phối độc quyền các dòng rượu vang và rượu mạnh nhập khẩu chính ngạch.', 'en' => 'Exclusive distributor of officially imported wines and spirits.'],
            ],
            [
                'name' => ['vi' => 'Rau củ hữu cơ Đà Lạt Fresh', 'en' => 'Dalat Fresh Organic Vegetables'],
                'description' => ['vi' => 'Cung ứng rau củ quả hữu cơ, tươi sạch tiêu chuẩn VietGAP từ Đà Lạt.', 'en' => 'Supplying fresh, organic vegetables and fruits with VietGAP standards from Dalat.'],
            ],
            [
                'name' => ['vi' => 'Nhà phân phối Thực phẩm Hảo hạng Gourmet', 'en' => 'Gourmet Food Distributors'],
                'description' => ['vi' => 'Đối tác cung ứng nguyên liệu thực phẩm cao cấp cho nhà hàng và bar/club.', 'en' => 'Supply partner of premium food ingredients for restaurants and bars/clubs.'],
            ],
        ];

        foreach ($fbBrands as $brandData) {
            $brand = Brand::create([
                'name' => $brandData['name'],
                'description' => $brandData['description'],
                'slug' => Str::slug($brandData['name']['en']) . '-' . Str::random(4),
                'website' => 'https://' . Str::slug($brandData['name']['en']) . '.com.vn',
                'is_visible' => true,
            ]);

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
        }
        return Brand::all();
    }

    protected function seedCategories(): \Illuminate\Support\Collection
    {
        if (ProductCategory::count() === 0) {
            $categoryTranslations = [
                'Món ăn' => [
                    'name' => ['vi' => 'Món ăn', 'en' => 'Food & Dishes'],
                    'children' => [
                        'Khai vị' => ['vi' => 'Khai vị', 'en' => 'Appetizers'],
                        'Món chính F&B' => ['vi' => 'Món chính F&B', 'en' => 'Main Courses'],
                        'Ăn nhẹ / Snack' => ['vi' => 'Ăn nhẹ / Snack', 'en' => 'Snacks & Pub Food'],
                        'Tráng miệng' => ['vi' => 'Tráng miệng', 'en' => 'Desserts'],
                    ]
                ],
                'Đồ uống có cồn' => [
                    'name' => ['vi' => 'Đồ uống có cồn', 'en' => 'Alcoholic Beverages'],
                    'children' => [
                        'Cocktail đặc trưng' => ['vi' => 'Cocktail đặc trưng', 'en' => 'Signature Cocktails'],
                        'Rượu mạnh / Spirits' => ['vi' => 'Rượu mạnh / Spirits', 'en' => 'Spirits & Liqueurs'],
                        'Rượu vang / Wine' => ['vi' => 'Rượu vang / Wine', 'en' => 'Wines'],
                        'Bia / Beer' => ['vi' => 'Bia / Beer', 'en' => 'Beers'],
                    ]
                ],
                'Nước giải khát' => [
                    'name' => ['vi' => 'Nước giải khát', 'en' => 'Non-Alcoholic Drinks'],
                    'children' => [
                        'Nước ngọt / Soft Drink' => ['vi' => 'Nước ngọt / Soft Drink', 'en' => 'Soft Drinks'],
                        'Nước ép trái cây' => ['vi' => 'Nước ép trái cây', 'en' => 'Fresh Fruit Juices'],
                        'Trà / Cà phê' => ['vi' => 'Trà / Cà phê', 'en' => 'Tea & Coffee'],
                    ]
                ],
            ];

            foreach ($categoryTranslations as $parentKey => $data) {
                $parent = ProductCategory::factory()->create([
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']['en']),
                ]);

                foreach ($data['children'] as $childKey => $childData) {
                    ProductCategory::factory()->create([
                        'parent_id' => $parent->id,
                        'name' => $childData,
                        'slug' => Str::slug($childData['en']),
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
