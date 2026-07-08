<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Database\Seeder;

class EngagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = $this->getUsersPool();
        $products = Product::all();
        $posts = Post::all();

        // 1. Comments for Products (Using Factory)
        if ($products->isNotEmpty()) {
            $this->seedProductComments($products, $users);
        }

        // 2. Comments for Posts (Using Factory)
        if ($posts->isNotEmpty()) {
            $this->seedPostComments($posts, $users);
        }
    }

    protected function getUsersPool(): \Illuminate\Database\Eloquent\Collection
    {
        $users = User::role('Thành viên đăng ký')->get();
        if ($users->isEmpty()) {
            $users = User::factory(5)->create();
            $users->each(fn($u) => $u->assignRole('Thành viên đăng ký'));
        }
        return $users;
    }

    protected function seedProductComments($products, $users): void
    {
        $products->random(min(10, $products->count()))->each(function ($product) use ($users) {
            Comment::factory()->count(rand(1, 2))->create([
                'user_id' => $users->random()->id,
                'commentable_type' => Product::class,
                'commentable_id' => $product->id,
            ]);
        });
    }

    protected function seedPostComments($posts, $users): void
    {
        $posts->random(min(10, $posts->count()))->each(function ($post) use ($users) {
            Comment::factory()->count(rand(1, 2))->create([
                'user_id' => $users->random()->id,
                'commentable_type' => Post::class,
                'commentable_id' => $post->id,
            ]);
        });
    }
}
