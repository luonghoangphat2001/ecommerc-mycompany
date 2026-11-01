<?php

namespace Database\Seeders\Demo;

use App\Models\Comment;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CmsSeeder extends Seeder
{
    /**
     * Run the CMS demo seeder.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first() ?? User::first();
        if (! $admin) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
            $admin = User::factory()->create([
                'name' => 'Admin System',
                'email' => 'admin@admin.com',
            ]);
            $admin->assignRole('super_admin');
        }

        // 1. Sync Media
        $this->seedMedia();

        // 2. Seed Pages
        $this->seedPages();

        // 3. Seed Menus
        $this->seedMenus();

        // 4. Seed Blog Categories & Posts
        $this->seedBlogContent($admin);
    }

    protected function seedMedia(): void
    {
        $mediaPath = storage_path('app/public/media');
        if (! file_exists($mediaPath)) {
            mkdir($mediaPath, 0755, true);
        }

        $sources = [
            database_path('seeders/local_images/1280x720'),
            database_path('seeders/local_images/200x200'),
            database_path('seeders/local_images/logo'),
        ];

        $imageIndex = 1;
        foreach ($sources as $dir) {
            if (! is_dir($dir)) {
                continue;
            }
            $images = glob($dir . '/*.{jpg,jpeg,png,webp,svg}', GLOB_BRACE);
            foreach ($images as $sourcePath) {
                $fileName = basename($sourcePath);
                $targetPath = $mediaPath . '/' . $fileName;

                if (! file_exists($targetPath)) {
                    copy($sourcePath, $targetPath);
                }

                $info = @getimagesize($targetPath);
                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                $mime = $info['mime'] ?? ('image/' . $ext);

                DB::table('media')->updateOrInsert(
                    ['file_name' => $fileName],
                    [
                        'id' => $imageIndex,
                        'uuid' => (string) Str::uuid(),
                        'disk' => 'public',
                        'directory' => 'media',
                        'visibility' => 'public',
                        'name' => pathinfo($fileName, PATHINFO_FILENAME),
                        'path' => "media/{$fileName}",
                        'mime_type' => $mime,
                        'type' => $mime,
                        'size' => file_exists($targetPath) ? filesize($targetPath) : 0,
                        'width' => $info[0] ?? 800,
                        'height' => $info[1] ?? 600,
                        'ext' => $ext,
                        'alt' => "Nova Club Media " . $imageIndex,
                        'title' => "Nova Club Media " . $imageIndex,
                        'collection_name' => 'default',
                        'manipulations' => '[]',
                        'custom_properties' => '[]',
                        'generated_conversions' => '[]',
                        'responsive_images' => '[]',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                $imageIndex++;
            }
        }

        $this->command?->info("Media sync complete: {$imageIndex} images processed.");
    }

    protected function seedPages(): void
    {
        $pages = [
            [
                'slug' => '/',
                'title' => [
                    'vi' => 'Trang chủ - Nova Club Store',
                    'en' => 'Home - Nova Club Store',
                ],
                'layout' => 'default',
                'blocks' => [
                    [
                        'type' => 'heading',
                        'value' => [
                            'vi' => 'Chào mừng đến với Nova Club Official Store',
                            'en' => 'Welcome to Nova Club Official Store',
                        ],
                    ],
                    [
                        'type' => 'markdown',
                        'value' => [
                            'vi' => '<p>Cửa hàng trực tuyến chính thức của Nova Athletic Club. Cung cấp trang phục thi đấu chính hãng, phụ kiện thể thao, trang thiết bị tập luyện chuyên nghiệp và bộ sưu tập fan merchandise độc quyền.</p>',
                            'en' => '<p>Official online store of Nova Athletic Club. Featuring authentic match jerseys, training gear, professional sports equipment, and exclusive fan merchandise collections.</p>',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'about-us',
                'title' => [
                    'vi' => 'Về chúng tôi - Nova Club Store',
                    'en' => 'About Us - Nova Club Store',
                ],
                'layout' => 'default',
                'blocks' => [
                    [
                        'type' => 'heading',
                        'value' => [
                            'vi' => 'Hành trình thể thao và niềm tự hào câu lạc bộ',
                            'en' => 'Our Sports Heritage & Club Pride',
                        ],
                    ],
                    [
                        'type' => 'markdown',
                        'value' => [
                            'vi' => '<p>Được thành lập từ năm 2018 tại TP.HCM, Nova Athletic Club là biểu tượng của tinh thần thể thao kiên cường và đam mê bất tận. Nova Club Store cam kết 100% trang phục thi đấu và dụng cụ thể thao chính hãng, đáp ứng tiêu chuẩn thi đấu chuyên nghiệp quốc tế.</p>',
                            'en' => '<p>Established in 2018 in Ho Chi Minh City, Nova Athletic Club symbolizes relentless athletic spirit and passion. Nova Club Store is dedicated to 100% authentic jerseys and equipment crafted to international competitive standards.</p>',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'contact',
                'title' => [
                    'vi' => 'Liên hệ & Đặt bàn',
                    'en' => 'Contact & VIP Reservations',
                ],
                'layout' => 'default',
                'blocks' => [
                    [
                        'type' => 'markdown',
                        'value' => [
                            'vi' => '<p><strong>Địa chỉ:</strong> Khu liên hợp Thể thao Nova Arena, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh<br/><strong>Hotline tư vấn & Giao hàng 24/7:</strong> 1900 8888 | (028) 3822 9999<br/><strong>Email:</strong> contact@novaclub.vn<br/><strong>Giờ mở cửa:</strong> 08:00 - 22:00 hàng ngày.</p>',
                            'en' => '<p><strong>Address:</strong> Nova Arena Sports Complex, Ben Nghe Ward, District 1, Ho Chi Minh City<br/><strong>24/7 Hotline & Express Delivery:</strong> 1900 8888 | (028) 3822 9999<br/><strong>Email:</strong> contact@novaclub.vn<br/><strong>Opening Hours:</strong> 08:00 - 22:00 Daily.</p>',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'shipping',
                'title' => [
                    'vi' => 'Chính sách vận chuyển',
                    'en' => 'Delivery Policy',
                ],
                'layout' => 'default',
                'blocks' => [
                    [
                        'type' => 'markdown',
                        'value' => [
                            'vi' => '<p>Dịch vụ giao rượu và đồ ăn tiệc tận nơi siêu tốc trong vòng 30 - 60 phút tại nội thành TP.HCM. Tất cả chai vang và cocktail tươi đều được đóng thùng xốp giữ nhiệt và chườm đá lạnh chuyên dụng, đảm bảo nhiệt độ thưởng thức hoàn hảo.</p>',
                            'en' => '<p>Express party and beverage delivery within 30 - 60 minutes across urban Ho Chi Minh City. All fine wines and fresh cocktails are dispatched in thermal insulated coolers with dedicated ice packs to preserve ideal drinking temperature.</p>',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'returns',
                'title' => [
                    'vi' => 'Chính sách đổi trả & Hoàn tiền',
                    'en' => 'Returns & Refund Policy',
                ],
                'layout' => 'default',
                'blocks' => [
                    [
                        'type' => 'markdown',
                        'value' => [
                            'vi' => '<p>Quý khách được đổi mới ngay lập tức hoặc hoàn tiền 100% nếu phát hiện chai rượu bị lỗi corked (hỏng nút bần), rò rỉ dung dịch hoặc tem nhập khẩu bị rách trong quá trình vận chuyển. Vui lòng kiểm tra hàng khi nhận và thông báo hotline trong vòng 24 giờ.</p>',
                            'en' => '<p>Customers are entitled to immediate replacement or 100% refund in case of corked wine faults, seal breaches, or shipping damage. Please inspect goods upon delivery and notify our hotline within 24 hours.</p>',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'faq',
                'title' => [
                    'vi' => 'Câu hỏi thường gặp (FAQ)',
                    'en' => 'Frequently Asked Questions (FAQ)',
                ],
                'layout' => 'default',
                'blocks' => [
                    [
                        'type' => 'markdown',
                        'value' => [
                            'vi' => '<p><strong>1. Áo đấu câu lạc bộ có chính hãng không?</strong><br/>100% trang phục thi đấu tại Nova Club Store là hàng chính hãng từ các đối tác thương hiệu Nike, Adidas, Puma và Nova Club Official.<br/><br/><strong>2. Tôi có thể in tên và số áo theo yêu cầu không?</strong><br/>Có, chúng tôi hỗ trợ in nhiệt công nghệ cao tên và số áo theo font chuẩn giải đấu cho hội viên và CĐV.<br/><br/><strong>3. Nếu chọn không vừa size có được đổi trả không?</strong><br/>Nova Club hỗ trợ đổi size miễn phí trong vòng 7 ngày kể từ khi nhận hàng (sản phẩm còn nguyên tem mác).</p>',
                            'en' => '<p><strong>1. Are the club jerseys authentic?</strong><br/>100% of jerseys at Nova Club Store are officially licensed products from Nike, Adidas, Puma, and Nova Club Official.<br/><br/><strong>2. Can I customize name and number on the jersey?</strong><br/>Yes, we offer tournament-grade heat press customization for official lettering and numbering.<br/><br/><strong>3. Can I exchange if the size does not fit?</strong><br/>We offer free size exchanges within 7 days of delivery as long as tags remain intact.</p>',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'privacy',
                'title' => [
                    'vi' => 'Chính sách bảo mật',
                    'en' => 'Privacy Policy',
                ],
                'layout' => 'default',
                'blocks' => [
                    [
                        'type' => 'markdown',
                        'value' => [
                            'vi' => '<p>Nova Club Store cam kết bảo mật tuyệt đối thông tin cá nhân và lịch sử mua sắm của hội viên theo tiêu chuẩn an ninh thông tin hiện đại.</p>',
                            'en' => '<p>Nova Club Store is committed to safeguarding member privacy, transaction history, and personal data in strict compliance with data security standards.</p>',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'terms',
                'title' => [
                    'vi' => 'Điều khoản sử dụng',
                    'en' => 'Terms of Service',
                ],
                'layout' => 'default',
                'blocks' => [
                    [
                        'type' => 'markdown',
                        'value' => [
                            'vi' => '<p>Khi đặt hàng hoặc sử dụng dịch vụ tại website, quý khách đồng ý với các quy định về thanh toán, giao nhận và tiêu chuẩn ứng xử văn minh tại Club.</p>',
                            'en' => '<p>By placing orders or reserving services on our platform, you agree to our payment terms, delivery conditions, and club etiquette standards.</p>',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($pages as $p) {
            Page::updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'title' => $p['title'],
                    'layout' => $p['layout'],
                    'blocks' => $p['blocks'],
                ]
            );
        }

        $this->command?->info("CMS Pages seeded: " . count($pages) . " pages.");
    }

    protected function seedMenus(): void
    {
        // 1. Main Navigation Menu
        $mainMenu = Menu::updateOrCreate(
            ['handle' => 'main-menu'],
            ['name' => 'Main Navigation Menu']
        );

        $mainItems = [
            ['title' => ['vi' => 'Trang chủ', 'en' => 'Home'], 'url' => '/', 'order' => 1],
            ['title' => ['vi' => 'Sản phẩm & Menu F&B', 'en' => 'Store & Menu'], 'url' => '/shop', 'order' => 2],
            ['title' => ['vi' => 'Bài viết & Cẩm nang', 'en' => 'Blog & Guides'], 'url' => '/posts', 'order' => 3],
            ['title' => ['vi' => 'Về chúng tôi', 'en' => 'About Us'], 'url' => '/page/about-us', 'order' => 4],
            ['title' => ['vi' => 'Liên hệ & Đặt bàn', 'en' => 'Contact & Booking'], 'url' => '/page/contact', 'order' => 5],
        ];

        foreach ($mainItems as $item) {
            MenuItem::updateOrCreate(
                ['menu_id' => $mainMenu->id, 'url' => $item['url']],
                [
                    'title' => $item['title'],
                    'order' => $item['order'],
                    'type' => 'custom',
                ]
            );
        }

        // 2. Footer Menu
        $footerMenu = Menu::updateOrCreate(
            ['handle' => 'footer-menu'],
            ['name' => 'Footer Links Menu']
        );

        $footerItems = [
            ['title' => ['vi' => 'Giới thiệu', 'en' => 'About Us'], 'url' => '/page/about-us', 'order' => 1],
            ['title' => ['vi' => 'Chính sách vận chuyển', 'en' => 'Shipping Policy'], 'url' => '/page/shipping', 'order' => 2],
            ['title' => ['vi' => 'Chính sách đổi trả', 'en' => 'Returns Policy'], 'url' => '/page/returns', 'order' => 3],
            ['title' => ['vi' => 'Câu hỏi thường gặp', 'en' => 'FAQ'], 'url' => '/page/faq', 'order' => 4],
            ['title' => ['vi' => 'Chính sách bảo mật', 'en' => 'Privacy'], 'url' => '/page/privacy', 'order' => 5],
            ['title' => ['vi' => 'Điều khoản dịch vụ', 'en' => 'Terms'], 'url' => '/page/terms', 'order' => 6],
        ];

        foreach ($footerItems as $item) {
            MenuItem::updateOrCreate(
                ['menu_id' => $footerMenu->id, 'url' => $item['url']],
                [
                    'title' => $item['title'],
                    'order' => $item['order'],
                    'type' => 'custom',
                ]
            );
        }

        $this->command?->info("Navigation menus seeded: main-menu and footer-menu.");
    }

    protected function seedBlogContent(User $admin): void
    {
        $categoriesData = [
            'mixology' => [
                'name' => ['vi' => 'Nghệ thuật Mixology & Pha chế', 'en' => 'Mixology & Cocktail Craft'],
                'slug' => 'mixology-cocktail-craft',
                'description' => ['vi' => 'Khám phá công thức cocktail đỉnh cao từ các Head Bartender hàng đầu.', 'en' => 'Explore world-class cocktail recipes from premier Head Bartenders.'],
            ],
            'wine-spirits' => [
                'name' => ['vi' => 'Cẩm nang Rượu vang & Spirits', 'en' => 'Wine & Fine Spirits Guide'],
                'slug' => 'wine-fine-spirits-guide',
                'description' => ['vi' => 'Kiến thức thưởng thức rượu vang, whisky đơn cất và cách kết hợp món ăn.', 'en' => 'Guide to wine tasting, single malt whiskies, and fine dining pairings.'],
            ],
            'nightlife-events' => [
                'name' => ['vi' => 'Sự kiện CLB & Hoạt động CĐV', 'en' => 'Club Events & Fan Activities'],
                'slug' => 'club-events-fan-activities',
                'description' => ['vi' => 'Cập nhật lịch thi đấu, ngày hội cổ động viên và đặc quyền hội viên Nova Club.', 'en' => 'Latest match schedules, fan festivals, and membership perks at Nova Club.'],
            ],
            'culinary' => [
                'name' => ['vi' => 'Phong cách Thể thao & Sức khỏe', 'en' => 'Sports Lifestyle & Fitness'],
                'slug' => 'sports-lifestyle-fitness',
                'description' => ['vi' => 'Bí quyết dinh dưỡng thể thao, tập luyện và phục hồi thể lực đỉnh cao.', 'en' => 'Sports nutrition, workout regimens, and high-performance recovery.'],
            ],
        ];

        $categories = collect();
        foreach ($categoriesData as $cData) {
            $cat = PostCategory::updateOrCreate(
                ['slug' => $cData['slug']],
                [
                    'name' => $cData['name'],
                    'description' => $cData['description'],
                    'is_visible' => true,
                ]
            );
            $categories->push($cat);
        }

        $postsData = [
            [
                'slug' => 'top-5-signature-cocktails-summer-2026',
                'title' => [
                    'vi' => 'Ra mắt mẫu áo đấu chính thức Nova Club Mùa giải 2026',
                    'en' => 'Official Launch of Nova Club 2026 Home Match Jersey',
                ],
                'content' => [
                    'vi' => '<p>Thiết kế áo đấu mùa giải 2026 của Nova Club tôn vinh hai sắc màu truyền thống Xanh Hải Quân và Vàng Hoàng Gia, ứng dụng công nghệ dệt AeroReady siêu thoáng khí giúp vận động viên luôn duy trì phong độ tốt nhất.</p>',
                    'en' => '<p>The 2026 Nova Club home kit pays tribute to the iconic Navy Blue and Royal Gold palette, equipped with AeroReady ventilation technology for peak performance.</p>',
                ],
                'post_type' => 'blog',
            ],
            [
                'slug' => 'how-to-pair-single-malt-whisky-with-steak',
                'title' => [
                    'vi' => 'Nghệ Thuật Kết Hợp Rượu Whisky Mạch Nha Với Bò Bít Tết Wagyu',
                    'en' => 'The Art of Pairing Single Malt Whisky with Wagyu Ribeye Steak',
                ],
                'content' => [
                    'vi' => '<p>Hương khói sồi nồng nàn của Macallan 12 năm tuổi hòa quyện hoàn hảo cùng độ mềm mọng như bơ tan của thịt bò Wagyu áp chảo sốt tiêu đen, tạo nên trải nghiệm ẩm thực đỉnh cao khó quên.</p>',
                    'en' => '<p>The rich oak smokiness of 12-year-old Macallan harmonizes seamlessly with the melt-in-your-mouth juiciness of pan-seared Wagyu beef, creating an unforgettable culinary summit.</p>',
                ],
                'post_type' => 'blog',
            ],
            [
                'slug' => 'weekend-vip-lounge-experience-guide',
                'title' => [
                    'vi' => 'Bí Quyết Đặt Bàn VIP Lounge Cho Tiệc Sinh Nhật Đẳng Cấp',
                    'en' => 'Guide to Booking a VIP Lounge for an Exclusive Celebration',
                ],
                'content' => [
                    'vi' => '<p>Khám phá gói dịch vụ VIP Gold Set với Dom Perignon ướp lạnh, bàn riêng hướng trọn sân khấu DJ và đội ngũ phục vụ chuyên nghiệp tận tình suốt đêm tiệc.</p>',
                    'en' => '<p>Discover our VIP Gold package featuring chilled Dom Perignon champagne, prime stage-facing lounge seating, and dedicated table concierge throughout your unforgettable night.</p>',
                ],
                'post_type' => 'news',
            ],
            [
                'slug' => 'craft-beer-revolution-saigon-bars',
                'title' => [
                    'vi' => 'Làn Sóng Bia Thủ Công (Craft Beer) Đổ Bộ Quầy Bar Sài Gòn',
                    'en' => 'The Craft Beer Revolution in Saigon Nightlife',
                ],
                'content' => [
                    'vi' => '<p>Bia thủ công IPA với hương hoa bia nhiệt đới ngào ngạt đang trở thành lựa chọn yêu thích của giới trẻ sành điệu khi họp mặt bạn bè cuối tuần.</p>',
                    'en' => '<p>Aromatic craft IPAs packed with tropical hop notes are rapidly becoming the preferred choice for sophisticated night owls gathering with friends over the weekend.</p>',
                ],
                'post_type' => 'blog',
            ],
            [
                'slug' => 'how-to-choose-authentic-champagne-party',
                'title' => [
                    'vi' => 'Cách Nhận Biết Và Thưởng Thức Champagne Pháp Chuẩn Vị',
                    'en' => 'How to Appreciate and Identify Authentic French Champagne',
                ],
                'content' => [
                    'vi' => '<p>Bọt sủi li ti đều đặn, màu vàng rơm óng ả cùng tiếng nổ êm dịu khi khui chai chính là bảo chứng cho một chai Champagne chính gốc từ vùng Reims nước Pháp.</p>',
                    'en' => '<p>Consistent fine perlage, golden straw hues, and the gentle pop of a well-handled cork are the undeniable hallmarks of authentic Champagne crafted in Reims, France.</p>',
                ],
                'post_type' => 'blog',
            ],
        ];

        foreach ($postsData as $index => $p) {
            $post = Post::updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'author_id' => $admin->id,
                    'title' => $p['title'],
                    'content' => $p['content'],
                    'post_type' => $p['post_type'],
                    'image' => ($index % 10) + 1,
                    'is_visible' => true,
                    'published_at' => now()->subDays($index * 5),
                ]
            );

            if ($categories->isNotEmpty()) {
                $post->categories()->sync([$categories->random()->id]);
            }

            // Seed sample comments
            Comment::updateOrCreate(
                [
                    'commentable_type' => Post::class,
                    'commentable_id' => $post->id,
                    'user_id' => $admin->id,
                ],
                [
                    'title' => 'Bài viết rất bổ ích',
                    'content' => 'Thông tin chi tiết và rất hữu ích cho những ai yêu thích ẩm thực và pha chế tại Sài Gòn!',
                    'is_visible' => true,
                ]
            );
        }

        $this->command?->info("Blog categories and posts seeded: " . count($postsData) . " posts.");
    }
}
