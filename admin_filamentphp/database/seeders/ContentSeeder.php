<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $author = User::first() ?? User::factory()->create();

        // 1. Sync Local Images to Media (Curator compatible)
        $this->seedMedia();

        // 2. Blog Categories (Using Factory Pattern)
        $categories = $this->seedCategories();

        // 3. Load Tags from data file
        $tagNames = include database_path('data/tag.php');

        // 4. Create Bulk Posts (Using Factory Pattern)
        $this->seedPosts($author, $categories, $tagNames);

        // 5. Create sample static pages
        $this->seedPages();
    }

    protected function seedMedia(): void
    {
        $localImagesPath = database_path('seeders/local_images/1280x720');
        $mediaPath = storage_path('app/public/media');

        if (!file_exists($mediaPath)) {
            mkdir($mediaPath, 0755, true);
        }

        $images = glob($localImagesPath . '/*.{jpg,jpeg,png,webp,svg}', GLOB_BRACE);

        foreach ($images as $index => $sourcePath) {
            $id = $index + 1;
            $fileName = basename($sourcePath);
            $targetPath = $mediaPath . '/' . $fileName;

            if (!file_exists($targetPath)) {
                copy($sourcePath, $targetPath);
            }

            $info = getimagesize($targetPath);
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $mime = $info['mime'] ?? ('image/' . $ext);

            DB::table('media')->updateOrInsert(
                ['id' => $id],
                [
                    'uuid' => (string) Str::uuid(),
                    'disk' => 'public',
                    'directory' => 'media',
                    'visibility' => 'public',
                    'name' => pathinfo($fileName, PATHINFO_FILENAME),
                    'path' => "media/$fileName",
                    'file_name' => $fileName,
                    'mime_type' => $mime,
                    'type' => $mime,
                    'size' => filesize($targetPath),
                    'width' => $info[0] ?? 1280,
                    'height' => $info[1] ?? 720,
                    'ext' => $ext,
                    'alt' => "Sample Image $id",
                    'title' => "Sample Image $id",
                    'collection_name' => 'default',
                    'manipulations' => '[]',
                    'custom_properties' => '[]',
                    'generated_conversions' => '[]',
                    'responsive_images' => '[]',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    protected function seedCategories(): \Illuminate\Support\Collection
    {
        $categoryData = include database_path('data/category_blog.php');
        $categories = collect();

        foreach ($categoryData as $parentName => $children) {
            $parent = PostCategory::firstOrCreate(
                ['slug' => Str::slug($parentName)],
                [
                    'name' => ['vi' => $parentName, 'en' => 'EN: ' . $parentName],
                    'is_visible' => true,
                ]
            );
            $categories->push($parent);

            foreach ($children as $childName) {
                $child = PostCategory::firstOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'parent_id' => $parent->id,
                        'name' => ['vi' => $childName, 'en' => 'EN: ' . $childName],
                        'is_visible' => true,
                    ]
                );
                $categories->push($child);
            }
        }

        return $categories;
    }

    protected function seedPosts($author, $categories, $tagNames): void
    {
        if (Post::count() < 10) {
            Post::factory()
                ->count(20)
                ->sequence(
                    ['post_type' => 'blog'],
                    ['post_type' => 'news'],
                )
                ->create([
                    'author_id' => $author->id,
                ])
                ->each(function (Post $post) use ($categories, $tagNames) {
                    // Attach random categories
                    $post->categories()->attach(
                        $categories->random(rand(1, 2))->pluck('id')->toArray()
                    );

                    // Attach random tags
                    if (!empty($tagNames)) {
                        $post->attachTags(\Illuminate\Support\Arr::random($tagNames, rand(2, 3)));
                    }
                });
        }
    }

    protected function seedPages(): void
    {
        if (Page::count() === 0) {
            Page::create([
                'title' => 'Trang chủ',
                'slug' => '/',
                'layout' => 'default',
                'blocks' => [
                    [
                        'type' => 'text',
                        'data' => ['name' => 'Chào mừng đến với Ecommerce'],
                    ],
                    [
                        'type' => 'text_area',
                        'data' => ['text_area' => 'Đây là trang chủ được xây dựng bằng Laravel thuần.'],
                    ],
                ],
            ]);

            Page::create([
                'title' => 'Giới thiệu',
                'slug' => 'about-us',
                'layout' => 'default',
                'blocks' => [
                    [
                        'type' => 'text',
                        'data' => ['name' => 'Về chúng tôi'],
                    ],
                ],
            ]);
        }
    }
}
