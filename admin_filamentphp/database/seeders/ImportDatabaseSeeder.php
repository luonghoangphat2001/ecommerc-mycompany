<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Brand;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Comment;
use App\Models\Address;
use App\Models\OrderAddress;
use App\Models\Post;
use App\Models\PostCategory;
use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Query\Expression;
use Symfony\Component\Console\Helper\ProgressBar;
use Closure;
use Illuminate\Database\Eloquent\Collection;

class ImportDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        ini_set('memory_limit', '1024M');

        try {
            $this->cleanupDatabase();

            DB::beginTransaction();

            $admin = $this->ensureAdminExists();
            $customers = $this->seedCustomers();
            
            $brands = $this->seedBrands();
            $categories = $this->seedCategories();
            $products = $this->seedProducts($brands, $categories, $customers);
            
            $this->seedOrders($products, $customers, $admin);
            $this->seedBlog($customers);

            DB::commit();

            Notification::make()
                ->title('Import Database Thành Công!')
                ->success()
                ->send();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ImportDatabaseSeeder Error: ' . $e->getMessage());
            Notification::make()
                ->title('Lỗi Import Database')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }

    protected function cleanupDatabase(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $tables = [
            'shop_brands', 'shop_categories', 'shop_category_product', 
            'shop_products', 'shop_orders', 'shop_order_items', 'shop_payments',
            'blog_categories', 'blog_posts', 'comments', 'addresses', 'shop_order_addresses'
        ];

        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        User::whereDoesntHave('roles', fn($q) => $q->where('name', 'super_admin'))->delete();
    }

    protected function ensureAdminExists(): User
    {
        return User::role('super_admin')->first() ?? User::factory()->create()->assignRole('super_admin');
    }

    protected function seedCustomers(): Collection
    {
        $this->command->warn('Seeding customers...');
        return User::factory(20)->create()->each(fn($u) => $u->assignRole('Thành viên đăng ký'));
    }

    protected function seedBrands(): Collection
    {
        $this->command->warn('Seeding brands...');
        return Brand::factory(10)
            ->has(Address::factory()->count(1))
            ->create();
    }

    protected function seedCategories(): Collection
    {
        $this->command->warn('Seeding categories...');
        return ProductCategory::factory(5)
            ->has(ProductCategory::factory()->count(2), 'children')
            ->create();
    }

    protected function seedProducts($brands, $categories, $customers): Collection
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

    protected function seedOrders($products, $customers, $admin): void
    {
        $this->command->warn('Seeding orders...');
        Order::factory(50)->create()->each(function ($order) use ($products, $customers, $admin) {
            $order->update(['user_id' => $customers->random()->id]);
            
            $items = $products->random(rand(1, 3));
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'shop_product_id' => $item->id,
                    'type' => 'product',
                    'name' => $item->getTranslation('name', 'vi'),
                    'qty' => rand(1, 2),
                    'unit_price' => $item->price ?? 100000,
                    'total' => ($item->price ?? 100000) * rand(1, 2),
                ]);
            }

            OrderAddress::factory()->create(['order_id' => $order->id, 'type' => 'shipping']);
            OrderAddress::factory()->create(['order_id' => $order->id, 'type' => 'billing']);
            
            Payment::factory()->create(['order_id' => $order->id]);
            
            app(\App\Contracts\Services\OrderServiceInterface::class)->recalculateTotals($order);
        });
    }

    protected function seedBlog($customers): void
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
