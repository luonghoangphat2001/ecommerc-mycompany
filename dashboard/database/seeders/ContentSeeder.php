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

        // 6. Create storefront navigation menus (main-menu & footer-menu)
        $this->seedMenus();

        // 7. Upgrade & backfill bilingual translations on existing records
        $this->backfillTranslations();
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

        $enTranslations = [
            'Tin tức công nghệ' => 'Technology News',
            'Đánh giá sản phẩm' => 'Product Reviews',
            'Hướng dẫn sử dụng' => 'User Guides',
            'Khuyến mãi & Sự kiện' => 'Promotions & Events',
            'Xu hướng mua sắm' => 'Shopping Trends',
            'Mẹo vặt cuộc sống' => 'Lifestyle Tips',
        ];

        foreach ($categoryData as $parentName => $children) {
            $enParent = $enTranslations[$parentName] ?? Str::headline(Str::ascii($parentName));
            $parent = PostCategory::updateOrCreate(
                ['slug' => Str::slug($parentName)],
                [
                    'name' => ['vi' => $parentName, 'en' => $enParent],
                    'description' => [
                        'vi' => "Chuyên mục {$parentName} cập nhật tin tức và bài viết mới nhất.",
                        'en' => "Latest articles and insights in {$enParent}.",
                    ],
                    'is_visible' => true,
                ]
            );
            $categories->push($parent);

            foreach ($children as $childName) {
                $enChild = $enTranslations[$childName] ?? Str::headline(Str::ascii($childName));
                $child = PostCategory::updateOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'parent_id' => $parent->id,
                        'name' => ['vi' => $childName, 'en' => $enChild],
                        'description' => [
                            'vi' => "Bài viết thuộc danh mục {$childName}.",
                            'en' => "Articles under {$enChild}.",
                        ],
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
        $pages = [
            [
                'slug' => '/',
                'title' => ['vi' => 'Trang chủ', 'en' => 'Home'],
                'blocks' => [
                    [
                        'type' => 'heading',
                        'value' => ['vi' => 'Chào mừng đến với TechForge Ecommerce', 'en' => 'Welcome to TechForge Ecommerce'],
                    ],
                    [
                        'type' => 'markdown',
                        'value' => [
                            'vi' => '<p>Hệ thống thương mại điện tử chuyên cung cấp thiết bị công nghệ và phần cứng chính hãng hàng đầu.</p>',
                            'en' => '<p>Premier e-commerce platform specializing in authentic enterprise hardware and technology gear.</p>',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'home',
                'title' => ['vi' => 'Trang chủ', 'en' => 'Home'],
                'blocks' => [
                    [
                        'type' => 'heading',
                        'value' => ['vi' => 'Chào mừng đến với TechForge Ecommerce', 'en' => 'Welcome to TechForge Ecommerce'],
                    ],
                    [
                        'type' => 'markdown',
                        'value' => [
                            'vi' => '<p>Hệ thống thương mại điện tử chuyên cung cấp thiết bị công nghệ và phần cứng chính hãng hàng đầu.</p>',
                            'en' => '<p>Premier e-commerce platform specializing in authentic enterprise hardware and technology gear.</p>',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'about',
                'title' => ['vi' => 'Giới thiệu về chúng tôi', 'en' => 'About Us'],
                'blocks' => [
                    [
                        'type' => 'heading',
                        'value' => ['vi' => 'Sứ mệnh công nghệ của chúng tôi', 'en' => 'Our Technology Mission'],
                    ],
                    [
                        'type' => 'markdown',
                        'value' => [
                            'vi' => '<p>Chúng tôi cung cấp giải pháp phần cứng máy trạm, linh kiện công nghệ cao và dịch vụ hậu mãi tiêu chuẩn quốc tế cho khách hàng doanh nghiệp và cá nhân.</p>',
                            'en' => '<p>We deliver enterprise-grade workstation hardware, high-performance components, and world-class after-sales support for businesses and professionals.</p>',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'about-us',
                'title' => ['vi' => 'Giới thiệu về chúng tôi', 'en' => 'About Us'],
                'blocks' => [
                    [
                        'type' => 'heading',
                        'value' => ['vi' => 'Sứ mệnh công nghệ của chúng tôi', 'en' => 'Our Technology Mission'],
                    ],
                    [
                        'type' => 'markdown',
                        'value' => [
                            'vi' => '<p>Chúng tôi cung cấp giải pháp phần cứng máy trạm, linh kiện công nghệ cao và dịch vụ hậu mãi tiêu chuẩn quốc tế cho khách hàng doanh nghiệp và cá nhân.</p>',
                            'en' => '<p>We deliver enterprise-grade workstation hardware, high-performance components, and world-class after-sales support for businesses and professionals.</p>',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'contact',
                'title' => ['vi' => 'Liên hệ với chúng tôi', 'en' => 'Contact Us'],
                'blocks' => [
                    [
                        'type' => 'markdown',
                        'value' => [
                            'vi' => '<p>Email hỗ trợ: support@techforge.vn | Hotline: 1900 6868 (08:00 - 21:00 hàng ngày).</p>',
                            'en' => '<p>Support Email: support@techforge.vn | Hotline: 1900 6868 (08:00 - 21:00 daily).</p>',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'shipping',
                'title' => ['vi' => 'Chính sách vận chuyển', 'en' => 'Shipping Policy'],
                'blocks' => [
                    [
                        'type' => 'markdown',
                        'value' => [
                            'vi' => '<p>Giao hàng toàn quốc trong 24-48 giờ. Miễn phí vận chuyển cho đơn hàng đủ điều kiện.</p>',
                            'en' => '<p>Nationwide delivery within 24-48 hours. Complimentary shipping on eligible orders.</p>',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'returns',
                'title' => ['vi' => 'Chính sách đổi trả & Bảo hành', 'en' => 'Returns & Warranty Policy'],
                'blocks' => [
                    [
                        'type' => 'markdown',
                        'value' => [
                            'vi' => '<p>Đổi mới trong 30 ngày đầu nếu phát sinh lỗi phần cứng từ nhà sản xuất. Bảo hành chính hãng 24-36 tháng.</p>',
                            'en' => '<p>30-day direct replacement for manufacturer hardware defects. 24-36 months official warranty coverage.</p>',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'faq',
                'title' => ['vi' => 'Câu hỏi thường gặp (FAQ)', 'en' => 'Frequently Asked Questions (FAQ)'],
                'blocks' => [
                    [
                        'type' => 'markdown',
                        'value' => [
                            'vi' => '<p>Giải đáp nhanh các thắc mắc về thanh toán, giao nhận, xuất hóa đơn VAT và bảo hành sản phẩm.</p>',
                            'en' => '<p>Quick answers regarding payments, fulfillment, VAT invoicing, and hardware warranty support.</p>',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'privacy',
                'title' => ['vi' => 'Chính sách bảo mật', 'en' => 'Privacy Policy'],
                'blocks' => [
                    [
                        'type' => 'markdown',
                        'value' => [
                            'vi' => '<p>Thông tin cá nhân và dữ liệu giao dịch của quý khách được mã hóa và bảo vệ nghiêm ngặt.</p>',
                            'en' => '<p>Your personal information and transaction data are strictly encrypted and protected.</p>',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'terms',
                'title' => ['vi' => 'Điều khoản dịch vụ', 'en' => 'Terms of Service'],
                'blocks' => [
                    [
                        'type' => 'markdown',
                        'value' => [
                            'vi' => '<p>Các điều khoản và điều kiện áp dụng khi mua sắm và sử dụng dịch vụ trên hệ thống.</p>',
                            'en' => '<p>Terms and conditions governing purchases and services on our platform.</p>',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($pages as $pageData) {
            Page::updateOrCreate(
                ['slug' => $pageData['slug']],
                [
                    'title' => $pageData['title'],
                    'layout' => 'default',
                    'blocks' => $pageData['blocks'],
                ]
            );
        }
    }

    protected function seedMenus(): void
    {
        $mainMenu = \App\Models\Menu::updateOrCreate(
            ['handle' => 'main-menu'],
            ['name' => 'Main Navigation']
        );

        $mainItems = [
            ['order' => 1, 'url' => '/', 'title' => ['vi' => 'Trang chủ', 'en' => 'Home']],
            ['order' => 2, 'url' => '/shop', 'title' => ['vi' => 'Cửa hàng', 'en' => 'Shop']],
            ['order' => 3, 'url' => '/blog', 'title' => ['vi' => 'Bài viết', 'en' => 'Blog']],
            ['order' => 4, 'url' => '/pages/about', 'title' => ['vi' => 'Giới thiệu', 'en' => 'About Us']],
            ['order' => 5, 'url' => '/pages/contact', 'title' => ['vi' => 'Liên hệ', 'en' => 'Contact']],
        ];

        foreach ($mainItems as $item) {
            \App\Models\MenuItem::updateOrCreate(
                ['menu_id' => $mainMenu->id, 'url' => $item['url']],
                [
                    'type' => 'custom',
                    'title' => $item['title'],
                    'target' => '_self',
                    'order' => $item['order'],
                    'model_type' => '',
                    'model_id' => 0,
                ]
            );
        }

        $footerMenu = \App\Models\Menu::updateOrCreate(
            ['handle' => 'footer-menu'],
            ['name' => 'Footer Menu']
        );

        $footerItems = [
            ['order' => 1, 'url' => '/pages/about', 'title' => ['vi' => 'Giới thiệu', 'en' => 'About Us']],
            ['order' => 2, 'url' => '/pages/shipping', 'title' => ['vi' => 'Chính sách vận chuyển', 'en' => 'Shipping Policy']],
            ['order' => 3, 'url' => '/pages/returns', 'title' => ['vi' => 'Chính sách đổi trả', 'en' => 'Returns Policy']],
            ['order' => 4, 'url' => '/pages/faq', 'title' => ['vi' => 'Câu hỏi thường gặp', 'en' => 'FAQ']],
            ['order' => 5, 'url' => '/pages/privacy', 'title' => ['vi' => 'Chính sách bảo mật', 'en' => 'Privacy Policy']],
            ['order' => 6, 'url' => '/pages/terms', 'title' => ['vi' => 'Điều khoản dịch vụ', 'en' => 'Terms of Service']],
        ];

        foreach ($footerItems as $item) {
            \App\Models\MenuItem::updateOrCreate(
                ['menu_id' => $footerMenu->id, 'url' => $item['url']],
                [
                    'type' => 'custom',
                    'title' => $item['title'],
                    'target' => '_self',
                    'order' => $item['order'],
                    'model_type' => '',
                    'model_id' => 0,
                ]
            );
        }
    }

    protected function backfillTranslations(): void
    {
        // Enable marketing settings in DB so loyalty/coupons/upsells/cross-sells/combos work out-of-the-box
        try {
            DB::table('settings')
                ->where('group', 'marketing')
                ->whereIn('name', ['upsell_enabled', 'cross_sell_enabled', 'combo_enabled', 'loyalty_enabled', 'enable_coupons'])
                ->update(['payload' => json_encode(true)]);
        } catch (\Throwable $e) {
            // Ignore if settings table is not migrated yet
        }

        // Clean up any "EN: " prefixes or missing English translations on Posts
        Post::query()->each(function (Post $post) {
            $titles = $post->getTranslations('title');
            $contents = $post->getTranslations('content');
            $viTitle = $titles['vi'] ?? (is_string($post->getRawOriginal('title')) ? $post->getRawOriginal('title') : 'Bài viết công nghệ');
            $enTitle = $titles['en'] ?? '';

            if ($enTitle === '' || str_starts_with($enTitle, 'EN:')) {
                $enTitle = 'Tech Insight: ' . Str::headline(str_replace('-', ' ', $post->slug ?: 'hardware-review'));
            }

            $viContent = $contents['vi'] ?? '<p>Bài viết phân tích chuyên sâu về xu hướng công nghệ và thiết bị phần cứng mới nhất.</p>';
            $enContent = $contents['en'] ?? '';
            if ($enContent === '' || str_starts_with(strip_tags($enContent), 'EN:')) {
                $enContent = '<p>In-depth analysis and hands-on review covering modern enterprise hardware architecture, performance benchmarks, and practical deployment tips.</p>';
            }

            $post->setTranslations('title', ['vi' => $viTitle, 'en' => $enTitle]);
            $post->setTranslations('content', ['vi' => $viContent, 'en' => $enContent]);
            $post->saveQuietly();
        });

        // Clean up any "EN: " prefixes on ProductCategories, Brands, and Products
        \App\Models\ProductCategory::query()->each(function (\App\Models\ProductCategory $cat) {
            $names = $cat->getTranslations('name');
            $viName = $names['vi'] ?? $cat->slug;
            $enName = $names['en'] ?? '';
            if ($enName === '' || str_starts_with($enName, 'EN:')) {
                $enName = Str::headline(str_replace('-', ' ', $cat->slug ?: $viName));
            }
            $descs = $cat->getTranslations('description');
            $viDesc = $descs['vi'] ?? "Danh mục sản phẩm {$viName} chính hãng.";
            $enDesc = $descs['en'] ?? '';
            if ($enDesc === '' || str_starts_with(strip_tags($enDesc), 'EN:')) {
                $enDesc = "Explore our curated selection of {$enName} products.";
            }
            $cat->setTranslations('name', ['vi' => $viName, 'en' => $enName]);
            $cat->setTranslations('description', ['vi' => $viDesc, 'en' => $enDesc]);
            $cat->saveQuietly();
        });

        \App\Models\Brand::query()->each(function (\App\Models\Brand $brand) {
            $names = $brand->getTranslations('name');
            $viName = $names['vi'] ?? $brand->slug;
            $enName = $names['en'] ?? '';
            if ($enName === '' || str_starts_with($enName, 'EN:')) {
                $enName = $viName;
            }
            $descs = $brand->getTranslations('description');
            $viDesc = $descs['vi'] ?? "Thương hiệu {$viName} chính hãng.";
            $enDesc = $descs['en'] ?? '';
            if ($enDesc === '' || str_starts_with(strip_tags($enDesc), 'EN:')) {
                $enDesc = "Official products from {$enName}.";
            }
            $brand->setTranslations('name', ['vi' => $viName, 'en' => $enName]);
            $brand->setTranslations('description', ['vi' => $viDesc, 'en' => $enDesc]);
            $brand->saveQuietly();
        });

        \App\Models\Product::query()->each(function (\App\Models\Product $product) {
            $names = $product->getTranslations('name');
            $viName = $names['vi'] ?? $product->slug;
            $enName = $names['en'] ?? '';
            if ($enName === '' || str_starts_with($enName, 'EN:')) {
                $enName = Str::headline(str_replace('-', ' ', $product->slug ?: $viName));
            }
            $descs = $product->getTranslations('description');
            $viDesc = $descs['vi'] ?? "Sản phẩm {$viName} chất lượng cao, bảo hành chính hãng.";
            $enDesc = $descs['en'] ?? '';
            if ($enDesc === '' || str_starts_with(strip_tags($enDesc), 'EN:')) {
                $enDesc = "High-performance {$enName} backed by official warranty and technical support.";
            }
            $product->setTranslations('name', ['vi' => $viName, 'en' => $enName]);
            $product->setTranslations('description', ['vi' => $viDesc, 'en' => $enDesc]);
            $product->saveQuietly();
        });
    }
}
