<?php

namespace Database\Seeders\Demo;

use App\Models\Brand;
use App\Models\ComboProduct;
use App\Models\Coupon;
use App\Models\CrossSellProduct;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\TaxClass;
use App\Models\TaxRate;
use App\Models\UpsellProduct;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /**
     * Run the catalog demo seeder.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first() ?? User::first();

        // 1. Tax Classes and Rates
        $this->seedTaxData();

        // 2. Shipping Zones and Methods
        $this->seedShippingData();

        // 3. Brands
        $brands = $this->seedBrands();

        // 4. Categories & Subcategories
        $categories = $this->seedCategories();

        // 5. Products
        $products = $this->seedProducts($brands, $categories, $admin);

        // 6. Combos
        $this->seedCombos($products);

        // 7. Upsells and Cross-sells
        $this->seedUpsellsAndCrossSells($products);

        // 8. Coupons
        $this->seedCoupons();

        $this->command?->info("CatalogSeeder completed successfully: Products, Categories, Combos, and Coupons ready.");
    }

    protected function seedTaxData(): void
    {
        $standardTax = TaxClass::firstOrCreate(['slug' => 'standard'], ['name' => 'Standard VAT']);
        TaxRate::updateOrCreate(
            ['tax_class_id' => $standardTax->id, 'country' => 'VN', 'name' => 'VAT 10%'],
            ['rate' => 10, 'priority' => 1]
        );

        $reducedTax = TaxClass::firstOrCreate(['slug' => 'reduced'], ['name' => 'Reduced VAT']);
        TaxRate::updateOrCreate(
            ['tax_class_id' => $reducedTax->id, 'country' => 'VN', 'name' => 'VAT 8%'],
            ['rate' => 8, 'priority' => 1]
        );
    }

    protected function seedShippingData(): void
    {
        $vnZone = ShippingZone::firstOrCreate(
            ['name' => 'Vietnam Nationwide'],
            ['locations' => [['country' => 'VN', 'state' => '*']], 'sort' => 1]
        );

        ShippingMethod::updateOrCreate(
            ['shipping_zone_id' => $vnZone->id, 'name' => 'Giao hàng Tiêu chuẩn'],
            ['type' => 'flat_rate', 'settings' => ['cost' => 35000], 'is_enabled' => true]
        );

        ShippingMethod::updateOrCreate(
            ['shipping_zone_id' => $vnZone->id, 'name' => 'Giao hàng Hỏa tốc 60 phút (Chườm đá lạnh)'],
            ['type' => 'flat_rate', 'settings' => ['cost' => 55000], 'is_enabled' => true]
        );
    }

    protected function seedBrands(): \Illuminate\Support\Collection
    {
        $brandsData = [
            [
                'slug' => 'heineken-vietnam',
                'name' => ['vi' => 'Nhà máy Bia Heineken Việt Nam', 'en' => 'Heineken Vietnam Brewery'],
                'description' => ['vi' => 'Nhà sản xuất và phân phối chính thức các dòng bia Heineken Silver, Tiger Crystal và Edelweiss.', 'en' => 'Official brewer and distributor of Heineken Silver, Tiger Crystal, and Edelweiss.'],
                'website' => 'https://heineken-vietnam.com.vn',
            ],
            [
                'slug' => 'moet-hennessy',
                'name' => ['vi' => 'Tập đoàn Rượu Moët Hennessy', 'en' => 'Moët Hennessy Group'],
                'description' => ['vi' => 'Thương hiệu rượu vang và cognac sang trọng bậc nhất thế giới: Hennessy, Dom Pérignon, Moët & Chandon.', 'en' => 'The world luxury leader in fine wines and spirits: Hennessy, Dom Pérignon, Moët & Chandon.'],
                'website' => 'https://moet-hennessy.com',
            ],
            [
                'slug' => 'pernod-ricard',
                'name' => ['vi' => 'Pernod Ricard Việt Nam', 'en' => 'Pernod Ricard Vietnam'],
                'description' => ['vi' => 'Nhà phân phối độc quyền các dòng Whisky Scotland Chivas Regal, The Glenlivet và Ballantine’s.', 'en' => 'Exclusive distributor of Scotch Whiskies Chivas Regal, The Glenlivet, and Ballantine’s.'],
                'website' => 'https://pernod-ricard.com',
            ],
            [
                'slug' => 'east-west-brewing',
                'name' => ['vi' => 'Xưởng Bia Thủ Công East West Brewing', 'en' => 'East West Craft Brewery'],
                'description' => ['vi' => 'Tiên phong phong trào nấu bia thủ công kết hợp hoa bia nhập khẩu Mỹ và nguyên liệu nhiệt đới Sài Gòn.', 'en' => 'Pioneering craft brewery blending imported US hops with tropical Saigon botanicals.'],
                'website' => 'https://eastwestbrewing.vn',
            ],
            [
                'slug' => 'cellar-spirits-group',
                'name' => ['vi' => 'Tổng kho Rượu vang & Rượu mạnh Cellar', 'en' => 'Cellar Fine Wine & Spirits'],
                'description' => ['vi' => 'Nhập khẩu chính ngạch các dòng vang Bordeaux Pháp, Chianti Ý và whisky đơn cất Scotland.', 'en' => 'Officially imported French Bordeaux, Italian Chianti, and Scotch single malts.'],
                'website' => 'https://cellarwine.vn',
            ],
            [
                'slug' => 'saigon-fb-gourmet',
                'name' => ['vi' => 'Nhà cung cấp Nguyên liệu F&B Sài Gòn', 'en' => 'Saigon F&B Gourmet Supply'],
                'description' => ['vi' => 'Cung ứng thịt bò Wagyu, phô mai châu Âu và syrup thảo mộc tươi cho các quầy bar club.', 'en' => 'Supplying Wagyu beef, European cheeses, and fresh botanical syrups for club bars.'],
                'website' => 'https://saigongourmet.vn',
            ],
            [
                'slug' => 'nova-club-signature',
                'name' => ['vi' => 'Bộ Sưu Tập Độc Quyền Nova Club', 'en' => 'Nova Club Signature Collection'],
                'description' => ['vi' => 'Sản phẩm áo thi đấu, phụ kiện thể thao và quà tặng VIP mang dấu ấn Nova Club.', 'en' => 'Official jerseys, sports accessories, and VIP merchandise from Nova Club.'],
                'website' => 'https://novaclub.vn',
            ],
        ];

        $brands = collect();
        foreach ($brandsData as $b) {
            $brand = Brand::updateOrCreate(
                ['slug' => $b['slug']],
                [
                    'name' => $b['name'],
                    'description' => $b['description'],
                    'website' => $b['website'],
                    'is_visible' => true,
                ]
            );
            $brands->push($brand);
        }

        return $brands;
    }

    protected function seedCategories(): \Illuminate\Support\Collection
    {
        $categoriesTree = [
            'cocktails' => [
                'slug' => 'cocktail-va-mocktail',
                'name' => ['vi' => 'Cocktail & Mocktail', 'en' => 'Cocktails & Mocktails'],
                'description' => ['vi' => 'Các loại cocktail pha chế tươi mát từ các bartender chuyên nghiệp.', 'en' => 'Handcrafted signature and classic cocktails prepared fresh.'],
                'children' => [
                    [
                        'slug' => 'signature-cocktails',
                        'name' => ['vi' => 'Đồ Uống Đặc Quyền Nova', 'en' => 'Nova Signature Drinks'],
                        'description' => ['vi' => 'Hương vị thức uống sáng tạo độc quyền.', 'en' => 'Exclusive creative beverage creations.'],
                    ],
                    [
                        'slug' => 'classic-cocktails',
                        'name' => ['vi' => 'Cocktail Cổ Điển', 'en' => 'Classic Cocktails'],
                        'description' => ['vi' => 'Những ly cocktail kinh điển sống mãi với thời gian.', 'en' => 'Timeless classic cocktail recipes.'],
                    ],
                    [
                        'slug' => 'mocktails',
                        'name' => ['vi' => 'Mocktail Không Cồn', 'en' => 'Non-Alcoholic Mocktails'],
                        'description' => ['vi' => 'Nước trái cây và thảo mộc thanh mát.', 'en' => 'Refreshing fruit and botanical mocktails.'],
                    ],
                ],
            ],
            'spirits' => [
                'slug' => 'ruou-manh-spirits',
                'name' => ['vi' => 'Rượu Mạnh (Spirits)', 'en' => 'Fine Spirits & Liquors'],
                'description' => ['vi' => 'Bộ sưu tập Whisky, Cognac, Gin và Vodka nhập khẩu chính ngạch.', 'en' => 'Curated collection of imported Whiskies, Cognacs, Gins, and Vodkas.'],
                'children' => [
                    [
                        'slug' => 'whisky-single-malt',
                        'name' => ['vi' => 'Whisky & Single Malt', 'en' => 'Whisky & Single Malt'],
                        'description' => ['vi' => 'Whisky mạch nha đơn cất ủ thùng sồi lâu năm.', 'en' => 'Aged single malt and blended Scotch whiskies.'],
                    ],
                    [
                        'slug' => 'cognac-brandy',
                        'name' => ['vi' => 'Cognac & Brandy', 'en' => 'Cognac & Brandy'],
                        'description' => ['vi' => 'Cognac Pháp thượng hạng.', 'en' => 'Prestigious French Cognacs.'],
                    ],
                    [
                        'slug' => 'gin-tequila',
                        'name' => ['vi' => 'Gin, Vodka & Tequila', 'en' => 'Gin, Vodka & Tequila'],
                        'description' => ['vi' => 'Rượu nền pha chế và thưởng thức nguyên chất.', 'en' => 'Base spirits for mixing and neat tasting.'],
                    ],
                ],
            ],
            'wines' => [
                'slug' => 'ruou-vang-champagne',
                'name' => ['vi' => 'Rượu Vang & Champagne', 'en' => 'Wines & Champagnes'],
                'description' => ['vi' => 'Vang đỏ, vang trắng và champagne phục vụ tiệc đêm.', 'en' => 'Premium red wines, white wines, and celebratory champagnes.'],
                'children' => [
                    [
                        'slug' => 'red-wines',
                        'name' => ['vi' => 'Rượu Vang Đỏ (Red Wine)', 'en' => 'Red Wines'],
                        'description' => ['vi' => 'Vang đỏ đậm đà từ Pháp, Ý và Chile.', 'en' => 'Full-bodied red wines from France, Italy, and Chile.'],
                    ],
                    [
                        'slug' => 'champagne-sparkling',
                        'name' => ['vi' => 'Champagne & Vang Nổ', 'en' => 'Champagnes & Sparkling'],
                        'description' => ['vi' => 'Sủi tăm mịn màng cho những khoảnh khắc ăn mừng.', 'en' => 'Fine bubbles for celebratory moments.'],
                    ],
                ],
            ],
            'beers' => [
                'slug' => 'bia-va-cider',
                'name' => ['vi' => 'Bia & Cider Thượng Hạng', 'en' => 'Beers & Ciders'],
                'description' => ['vi' => 'Bia thủ công IPA, bia ủ lạnh mát lạnh cho nhóm bạn.', 'en' => 'Craft IPAs and chilled lagers for parties.'],
                'children' => [
                    [
                        'slug' => 'craft-beer',
                        'name' => ['vi' => 'Bia Thủ Công (Craft Beer)', 'en' => 'Craft Beers'],
                        'description' => ['vi' => 'Bia ủ tươi phong cách mới.', 'en' => 'Artisanal microbrews.'],
                    ],
                    [
                        'slug' => 'imported-lagers',
                        'name' => ['vi' => 'Bia Nhập Khẩu & Lager', 'en' => 'Imported Lagers'],
                        'description' => ['vi' => 'Bia lon, bia chai ướp lạnh sẵn.', 'en' => 'Pre-chilled imported bottles and cans.'],
                    ],
                ],
            ],
            'culinary' => [
                'slug' => 'am-thuc-club-lounge',
                'name' => ['vi' => 'Ẩm Thực Club & Lounge', 'en' => 'Club Dining & Tapas'],
                'description' => ['vi' => 'Bò bít tết Wagyu, mỳ Ý, snack và món tráng miệng thượng hạng.', 'en' => 'Wagyu ribeye steaks, pasta, gourmet tapas, and desserts.'],
                'children' => [
                    [
                        'slug' => 'steaks-mains',
                        'name' => ['vi' => 'Món Chính & Bò Bít Tết', 'en' => 'Steaks & Main Courses'],
                        'description' => ['vi' => 'Bò Wagyu áp chảo, sườn nướng BBQ.', 'en' => 'Wagyu steaks, BBQ ribs, and pan-seared salmon.'],
                    ],
                    [
                        'slug' => 'tapas-snacks',
                        'name' => ['vi' => 'Finger Food & Ăn Nhẹ', 'en' => 'Tapas & Bar Bites'],
                        'description' => ['vi' => 'Khoai tây Cajun, cánh gà giòn mắm tỏi.', 'en' => 'Cajun fries, crispy wings, and artisanal finger food.'],
                    ],
                    [
                        'slug' => 'desserts',
                        'name' => ['vi' => 'Tráng Miệng Ý & Kem Lạnh', 'en' => 'Desserts & Ice Cream'],
                        'description' => ['vi' => 'Tiramisu, panna cotta và kem tươi.', 'en' => 'Classic Tiramisu, Panna Cotta, and ice cream.'],
                    ],
                ],
            ],
            'merchandise' => [
                'slug' => 'phu-kien-club-vip',
                'name' => ['vi' => 'Phụ Kiện & Merchandise', 'en' => 'Accessories & Merchandise'],
                'description' => ['vi' => 'Áo thun VIP, bộ ly pha lê và dụng cụ quầy bar.', 'en' => 'VIP club apparel, crystal glassware, and bartending tools.'],
                'children' => [
                    [
                        'slug' => 'glassware-tools',
                        'name' => ['vi' => 'Bộ Ly Pha Lê & Dụng Cụ Bar', 'en' => 'Glassware & Bar Tools'],
                        'description' => ['vi' => 'Bộ ly pha lê thưởng thức vang và cocktail chuẩn quốc tế.', 'en' => 'Crystal wine glasses and professional shaker sets.'],
                    ],
                    [
                        'slug' => 'apparel',
                        'name' => ['vi' => 'Thời Trang Nova Club VIP', 'en' => 'Nova VIP Club Apparel'],
                        'description' => ['vi' => 'Áo thun cotton cao cấp và nón lưỡi trai Nova Club.', 'en' => 'Premium cotton tees and caps.'],
                    ],
                ],
            ],
        ];

        $allCategories = collect();
        foreach ($categoriesTree as $pKey => $pData) {
            $parent = ProductCategory::updateOrCreate(
                ['slug' => $pData['slug']],
                [
                    'name' => $pData['name'],
                    'description' => $pData['description'],
                    'is_visible' => true,
                    'sort' => 1,
                ]
            );
            $allCategories->push($parent);

            foreach ($pData['children'] as $cIdx => $cData) {
                $child = ProductCategory::updateOrCreate(
                    ['slug' => $cData['slug']],
                    [
                        'parent_id' => $parent->id,
                        'name' => $cData['name'],
                        'description' => $cData['description'],
                        'is_visible' => true,
                        'sort' => $cIdx + 1,
                    ]
                );
                $allCategories->push($child);
            }
        }

        return $allCategories;
    }

    protected function seedProducts($brands, $categories, $admin): \Illuminate\Support\Collection
    {
        $tax = TaxClass::where('slug', 'standard')->first() ?? TaxClass::first();
        $mediaCount = Media::count();
        $authorId = $admin ? $admin->id : 1;

        $catalogItems = [
            // Cocktails
            [
                'sku' => 'CLUB-COCK-001',
                'name' => ['vi' => 'Cocktail Mojito Bạc Hà Truyền Thống', 'en' => 'Classic Fresh Mint Mojito'],
                'description' => ['vi' => 'Sự kết hợp sảng khoái tuyệt hảo giữa rượu Rum trắng, chanh tươi, lá bạc hà đập dập và soda mát lạnh.', 'en' => 'A crisp and invigorating mix of white rum, fresh limes, muddled mint, and chilled club soda.'],
                'price' => 135000, 'old_price' => 155000, 'cost' => 35000, 'sale_price' => null,
                'brand' => 'nova-club-signature', 'category' => 'classic-cocktails', 'featured' => true,
            ],
            [
                'sku' => 'CLUB-COCK-002',
                'name' => ['vi' => 'Cocktail Margarita Cổ Điển Viền Muối', 'en' => 'Classic Salt-Rimmed Margarita'],
                'description' => ['vi' => 'Rượu Tequila vàng hảo hạng hòa quyện rượu mùi cam Triple Sec và nước cốt chanh tươi viền muối tinh.', 'en' => 'Premium Gold Tequila blended with Triple Sec orange liqueur and tangy lime juice with a delicate salt rim.'],
                'price' => 155000, 'old_price' => 175000, 'cost' => 40000, 'sale_price' => 140000,
                'brand' => 'nova-club-signature', 'category' => 'classic-cocktails', 'featured' => true,
            ],
            [
                'sku' => 'CLUB-COCK-003',
                'name' => ['vi' => 'Cocktail Dry Martini Kèm Quả Ô Liu Xanh', 'en' => 'Dry Martini with Green Olive'],
                'description' => ['vi' => 'Biểu tượng cocktail kinh điển từ rượu Gin Bombay Sapphire và Dry Vermouth ướp lạnh hoàn hảo.', 'en' => 'Iconic classic cocktail prepared with Bombay Sapphire Gin and extra-dry Vermouth, served ice-cold.'],
                'price' => 180000, 'old_price' => 200000, 'cost' => 45000, 'sale_price' => null,
                'brand' => 'nova-club-signature', 'category' => 'classic-cocktails', 'featured' => false,
            ],
            [
                'sku' => 'CLUB-COCK-004',
                'name' => ['vi' => 'Cocktail Tequila Sunrise Hoàng Hôn Rực Rỡ', 'en' => 'Tequila Sunrise Cocktail'],
                'description' => ['vi' => 'Sắc màu hoàng hôn ấm áp từ Tequila nguyên chất, nước cam tươi vắt và siro lựu grenadine ngọt ngào.', 'en' => 'A picturesque gradient of premium Tequila, freshly squeezed orange juice, and sweet grenadine syrup.'],
                'price' => 145000, 'old_price' => 165000, 'cost' => 38000, 'sale_price' => null,
                'brand' => 'nova-club-signature', 'category' => 'signature-cocktails', 'featured' => false,
            ],
            [
                'sku' => 'CLUB-COCK-005',
                'name' => ['vi' => 'Đồ Uống Nova Velvet Gold Độc Quyền', 'en' => 'Nova Velvet Gold Signature Drink'],
                'description' => ['vi' => 'Công thức đặc quyền của Nova Club: Trà thảo mộc cao cấp, hương cam bergamot và mật ong rừng nguyên chất.', 'en' => 'Exclusive Nova Club creation with premium herbal blend, bergamot citrus, and pure wild honey.'],
                'price' => 250000, 'old_price' => 290000, 'cost' => 70000, 'sale_price' => 220000,
                'brand' => 'nova-club-signature', 'category' => 'signature-cocktails', 'featured' => true,
            ],
            [
                'sku' => 'CLUB-MOCK-001',
                'name' => ['vi' => 'Thức Uống Điện Giải Trái Cây Nhiệt Đới', 'en' => 'Tropical Breeze Hydration Drink'],
                'description' => ['vi' => 'Nước ép chanh dây, dứa tươi, khoáng chất điện giải bù năng lượng cho vận động viên sau tập luyện.', 'en' => 'Passionfruit, fresh pineapple, electrolyte minerals for post-workout athletic hydration.'],
                'price' => 95000, 'old_price' => 110000, 'cost' => 25000, 'sale_price' => null,
                'brand' => 'nova-club-signature', 'category' => 'mocktails', 'featured' => false,
            ],

            // Fine Spirits
            [
                'sku' => 'CLUB-SPIR-001',
                'name' => ['vi' => 'Rượu Chivas Regal 12 Năm Tuổi (Chai 700ml)', 'en' => 'Chivas Regal 12 Year Old Scotch Whisky (700ml)'],
                'description' => ['vi' => 'Dòng Whisky Scotland trứ danh êm mượt với hương thạch nam, mật ong rừng và táo chín mọng.', 'en' => 'World-renowned blended Scotch whisky offering smooth honey, wild heather, and ripe orchard fruit notes.'],
                'price' => 1450000, 'old_price' => 1600000, 'cost' => 950000, 'sale_price' => 1350000,
                'brand' => 'pernod-ricard', 'category' => 'whisky-single-malt', 'featured' => true,
            ],
            [
                'sku' => 'CLUB-SPIR-002',
                'name' => ['vi' => 'Rượu Macallan 12 Năm Sherry Oak Thượng Hạng', 'en' => 'Macallan 12 Year Old Sherry Oak Whisky'],
                'description' => ['vi' => 'Mạch nha đơn cất Scotland trứ danh ủ hoàn toàn trong thùng gỗ sồi Sherry Oloroso từ Jerez Tây Ban Nha.', 'en' => 'Iconic single malt Scotch fully matured in seasoned Oloroso sherry oak casks from Jerez, Spain.'],
                'price' => 2850000, 'old_price' => 3100000, 'cost' => 1850000, 'sale_price' => null,
                'brand' => 'cellar-spirits-group', 'category' => 'whisky-single-malt', 'featured' => true,
            ],
            [
                'sku' => 'CLUB-SPIR-003',
                'name' => ['vi' => 'Rượu Cognac Hennessy VSOP Chính Hãng (700ml)', 'en' => 'Hennessy VSOP Privilège Cognac (700ml)'],
                'description' => ['vi' => 'Biểu tượng Cognac Pháp với sự cân bằng tuyệt đối giữa hương gỗ sồi vani nồng nàn và gia vị tinh tế.', 'en' => 'Benchmark French Cognac harmonizing toasted oak notes, vanilla sweetness, and delicate spices.'],
                'price' => 2100000, 'old_price' => 2300000, 'cost' => 1350000, 'sale_price' => null,
                'brand' => 'moet-hennessy', 'category' => 'cognac-brandy', 'featured' => true,
            ],
            [
                'sku' => 'CLUB-SPIR-004',
                'name' => ['vi' => 'Rượu Gin Bombay Sapphire London Dry (750ml)', 'en' => 'Bombay Sapphire London Dry Gin (750ml)'],
                'description' => ['vi' => 'Chưng cất hơi nước độc đáo cùng 10 loại thảo mộc quý hiếm trên thế giới.', 'en' => 'Distinctive vapor-infused London Dry Gin crafted with 10 hand-selected exotic botanicals.'],
                'price' => 880000, 'old_price' => 980000, 'cost' => 520000, 'sale_price' => null,
                'brand' => 'cellar-spirits-group', 'category' => 'gin-tequila', 'featured' => false,
            ],

            // Wines & Champagnes
            [
                'sku' => 'CLUB-WINE-001',
                'name' => ['vi' => 'Vang Đỏ Pháp Château Bordeaux Grand Vin', 'en' => 'Château Bordeaux Grand Vin Red Wine (750ml)'],
                'description' => ['vi' => 'Vang đỏ danh tiếng từ vùng Bordeaux, đậm đà vị mận chín, quả mọng rừng và hậu vị tannin mượt mà.', 'en' => 'Prestigious Bordeaux red wine with rich blackcurrant, plum aromas, and silky persistent tannins.'],
                'price' => 920000, 'old_price' => 1050000, 'cost' => 550000, 'sale_price' => 850000,
                'brand' => 'cellar-spirits-group', 'category' => 'red-wines', 'featured' => false,
            ],
            [
                'sku' => 'CLUB-WINE-002',
                'name' => ['vi' => 'Champagne Dom Pérignon Vintage Hào Nhoáng', 'en' => 'Dom Pérignon Vintage Champagne (750ml)'],
                'description' => ['vi' => 'Đỉnh cao Champagne Pháp với bọt sủi mịn màng, hương bánh mì nướng bơ ngậy và độ sâu lắng tuyệt vời.', 'en' => 'The ultimate expression of vintage Champagne with luminous bubbles, brioche notes, and immense depth.'],
                'price' => 5800000, 'old_price' => 6400000, 'cost' => 3900000, 'sale_price' => null,
                'brand' => 'moet-hennessy', 'category' => 'champagne-sparkling', 'featured' => true,
            ],

            // Beers
            [
                'sku' => 'CLUB-BEER-001',
                'name' => ['vi' => 'Bia Heineken Silver Mát Lạnh (Thùng 24 Lon)', 'en' => 'Heineken Silver Crisp Lager (Case of 24 Cans)'],
                'description' => ['vi' => 'Vị bia nhẹ êm, sảng khoái với độ cồn 4% được ủ lạnh độc quyền ở nhiệt độ -1°C.', 'en' => 'Smooth and refreshing lager brewed at -1°C for an effortless drinking experience.'],
                'price' => 450000, 'old_price' => 495000, 'cost' => 340000, 'sale_price' => 430000,
                'brand' => 'heineken-vietnam', 'category' => 'imported-lagers', 'featured' => false,
            ],
            [
                'sku' => 'CLUB-BEER-002',
                'name' => ['vi' => 'Bia Thủ Công East West IPA Đậm Vị (Lốc 6 Chai)', 'en' => 'East West Craft IPA Beer (Pack of 6 Bottles)'],
                'description' => ['vi' => 'Hương thơm nồng nàn từ hoa bia Simcoe & Citra kết hợp mạch nha chất lượng cao, hậu vị đắng thanh tao.', 'en' => 'Bursting with Simcoe & Citra hop aromas and solid malt backbone with a clean pine finish.'],
                'price' => 420000, 'old_price' => 480000, 'cost' => 260000, 'sale_price' => null,
                'brand' => 'east-west-brewing', 'category' => 'craft-beer', 'featured' => false,
            ],
            [
                'sku' => 'CLUB-BEER-003',
                'name' => ['vi' => 'Bia Corona Extra Mexico (Thùng 24 Chai 355ml)', 'en' => 'Corona Extra Mexican Lager (Case of 24 Bottles)'],
                'description' => ['vi' => 'Dòng bia giải khát hoàn hảo khi thưởng thức lạnh cùng một lát chanh tươi và muối hạt.', 'en' => 'World-famous refreshing lager best enjoyed ice-cold with a fresh lime wedge.'],
                'price' => 890000, 'old_price' => 960000, 'cost' => 680000, 'sale_price' => null,
                'brand' => 'heineken-vietnam', 'category' => 'imported-lagers', 'featured' => false,
            ],

            // Culinary & Food
            [
                'sku' => 'CLUB-FOOD-001',
                'name' => ['vi' => 'Bò Bít Tết Wagyu A5 Áp Chảo Kèm Sốt Tiêu Đen', 'en' => 'Pan-Seared Wagyu A5 Ribeye with Peppercorn Sauce'],
                'description' => ['vi' => 'Thịt bò Wagyu mềm mọng như bơ tan, phục vụ cùng khoai tây nghiền mịn và măng tây nướng tỏi.', 'en' => 'Incredible marbled Wagyu A5 ribeye seared to perfection, served with truffle mash and garlic asparagus.'],
                'price' => 720000, 'old_price' => 820000, 'cost' => 350000, 'sale_price' => 680000,
                'brand' => 'saigon-fb-gourmet', 'category' => 'steaks-mains', 'featured' => true,
            ],
            [
                'sku' => 'CLUB-FOOD-002',
                'name' => ['vi' => 'Sườn Heo Non Nướng Sốt BBQ Mật Ong Thảo Mộc', 'en' => 'Honey Glazed Slow-Roasted BBQ Pork Ribs'],
                'description' => ['vi' => 'Sườn heo ướp chậm 12 giờ, nướng than hoa vàng ươm ngập sốt BBQ mật ong đậm đà khó cưỡng.', 'en' => 'Tender 12-hour marinated ribs glazed with rich honey BBQ sauce and served with coleslaw.'],
                'price' => 340000, 'old_price' => 380000, 'cost' => 150000, 'sale_price' => null,
                'brand' => 'saigon-fb-gourmet', 'category' => 'steaks-mains', 'featured' => false,
            ],
            [
                'sku' => 'CLUB-FOOD-003',
                'name' => ['vi' => 'Cá Hồi Na-uy Áp Chảo Sốt Chanh Dây Chua Ngọt', 'en' => 'Pan-Seared Norwegian Salmon with Passionfruit Reduction'],
                'description' => ['vi' => 'Phi-lê cá hồi da giòn rụm, thịt cá giữ trọn độ ẩm ngọt, chấm cùng sốt chanh dây thanh mát.', 'en' => 'Crispy skin salmon fillet drizzled with zesty passionfruit coulis and sautéed baby potatoes.'],
                'price' => 310000, 'old_price' => 350000, 'cost' => 140000, 'sale_price' => null,
                'brand' => 'saigon-fb-gourmet', 'category' => 'steaks-mains', 'featured' => false,
            ],
            [
                'sku' => 'CLUB-FOOD-004',
                'name' => ['vi' => 'Khoai Tây Chiên Lắc Muối Ớt Cajun & Phô Mai', 'en' => 'Crispy Cajun French Fries with Parmesan Cheese'],
                'description' => ['vi' => 'Khoai tây vàng giòn tẩm bột gia vị Cajun cay nồng, rắc phô mai Parmesan bào sợi.', 'en' => 'Golden crispy shoestring fries tossed in bold Cajun spices and shaved Parmesan.'],
                'price' => 85000, 'old_price' => 95000, 'cost' => 25000, 'sale_price' => null,
                'brand' => 'saigon-fb-gourmet', 'category' => 'tapas-snacks', 'featured' => false,
            ],
            [
                'sku' => 'CLUB-FOOD-005',
                'name' => ['vi' => 'Cánh Gà Chiên Nước Mắm Tỏi Ớt Giòn Tan', 'en' => 'Crispy Fish Sauce & Caramelized Garlic Chicken Wings'],
                'description' => ['vi' => 'Cánh gà ta chiên ráo dầu, áo đều lớp sốt nước mắm nhĩ kẹo dẻo thơm ngậy tỏi phi.', 'en' => 'Crispy fried wings tossed in sweet-savory fish sauce glaze and aromatic fried garlic chips.'],
                'price' => 135000, 'old_price' => 155000, 'cost' => 50000, 'sale_price' => null,
                'brand' => 'saigon-fb-gourmet', 'category' => 'tapas-snacks', 'featured' => false,
            ],
            [
                'sku' => 'CLUB-FOOD-006',
                'name' => ['vi' => 'Bánh Tiramisu Ý Cổ Điển Hương Rượu Rum & Cà Phê', 'en' => 'Traditional Italian Espresso & Dark Rum Tiramisu'],
                'description' => ['vi' => 'Lớp bánh ladyfingers ngâm cà phê espresso đậm đà hòa cùng kem phô mai Mascarpone béo mịn.', 'en' => 'Espresso-soaked Savoiardi biscuits layered with creamy Mascarpone and cocoa dust.'],
                'price' => 95000, 'old_price' => 110000, 'cost' => 30000, 'sale_price' => null,
                'brand' => 'saigon-fb-gourmet', 'category' => 'desserts', 'featured' => false,
            ],

            // VIP Accessories & Merch
            [
                'sku' => 'CLUB-MERCH-001',
                'name' => ['vi' => 'Bộ 2 Ly Thủy Tinh Nova Club Thượng Hạng', 'en' => 'Set of 2 Nova Club Premium Glasses'],
                'description' => ['vi' => 'Thủy tinh cao cấp chịu nhiệt, in logo Nova Club mạ vàng tinh xảo.', 'en' => 'Heat-resistant premium glassware featuring subtle golden Nova Club branding.'],
                'price' => 550000, 'old_price' => 650000, 'cost' => 250000, 'sale_price' => 490000,
                'brand' => 'nova-club-signature', 'category' => 'glassware-tools', 'featured' => false,
            ],
            [
                'sku' => 'CLUB-MERCH-002',
                'name' => ['vi' => 'Áo Thun Cotton Nova Club VIP Limited Edition (Size L)', 'en' => 'Nova Club VIP Limited Edition Cotton T-Shirt (Size L)'],
                'description' => ['vi' => 'Chất liệu 100% cotton 280gsm thoáng mát, in logo dạ quang Nova Club tinh tế ở ngực và lưng.', 'en' => 'Heavyweight 280gsm cotton tee featuring luminous Nova Club branding details.'],
                'price' => 350000, 'old_price' => 420000, 'cost' => 140000, 'sale_price' => null,
                'brand' => 'nova-club-signature', 'category' => 'apparel', 'featured' => false,
            ],
        ];

        $products = collect();
        $brandMap = $brands->keyBy('slug');
        $catMap = $categories->keyBy('slug');

        foreach ($catalogItems as $index => $item) {
            $brandObj = $brandMap->get($item['brand']) ?? $brands->first();
            $catObj = $catMap->get($item['category']) ?? $categories->first();

            // Pick valid media ID
            $mediaId = $mediaCount > 0 ? (($index % $mediaCount) + 1) : null;

            $product = Product::updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'shop_brand_id' => $brandObj ? $brandObj->id : null,
                    'author_id' => $authorId,
                    'name' => $item['name'],
                    'slug' => Str::slug($item['name']['en']) . '-' . strtolower(Str::random(4)),
                    'barcode' => '893' . str_pad($index + 1000, 10, '0', STR_PAD_LEFT),
                    'description' => $item['description'],
                    'qty' => 100, // Stock will be updated by InventorySeeder
                    'total_stock' => 100,
                    'security_stock' => 10,
                    'featured' => $item['featured'],
                    'is_visible' => true,
                    'price' => $item['price'],
                    'old_price' => $item['old_price'],
                    'cost' => $item['cost'],
                    'sale_price' => $item['sale_price'],
                    'tax_class_id' => $tax ? $tax->id : null,
                    'type' => 'deliverable',
                    'apply_tax' => true,
                    'requires_shipping' => true,
                    'product_images' => $mediaId,
                    'published_at' => now()->subDays(rand(10, 180)),
                ]
            );

            if ($catObj) {
                $product->categories()->syncWithoutDetaching([$catObj->id]);
            }

            $products->push($product);
        }

        return $products;
    }

    protected function seedCombos($products): void
    {
        if ($products->count() < 4) {
            return;
        }

        $combosData = [
            [
                'name' => 'VIP Lounge Gold Party Pack',
                'slug' => 'vip-lounge-gold-party-pack',
                'description' => 'Trọn gói đêm tiệc VIP đỉnh cao: 1 chai Hennessy VSOP + 1 Đĩa bò Wagyu áp chảo + 1 Đĩa sườn BBQ + 1 Bánh Tiramisu.',
                'combo_price' => 2800000,
                'original_price' => 3255000,
                'discount_percent' => 14,
                'skus' => ['CLUB-SPIR-003', 'CLUB-FOOD-001', 'CLUB-FOOD-002', 'CLUB-FOOD-006'],
            ],
            [
                'name' => 'Craft Beer & BBQ Platter Weekend Pack',
                'slug' => 'craft-beer-bbq-platter-weekend-pack',
                'description' => 'Combo giải nhiệt cuối tuần: 1 Lốc East West IPA + 1 Phần sườn BBQ + 1 Phần cánh gà chiên mắm + 1 Khoai tây Cajun.',
                'combo_price' => 850000,
                'original_price' => 980000,
                'discount_percent' => 13,
                'skus' => ['CLUB-BEER-002', 'CLUB-FOOD-002', 'CLUB-FOOD-005', 'CLUB-FOOD-004'],
            ],
            [
                'name' => 'Cocktail Tasting Flight Trio',
                'slug' => 'cocktail-tasting-flight-trio',
                'description' => 'Thưởng thức bộ 3 ly cocktail đặc sắc: Classic Mojito + Margarita + Tequila Sunrise.',
                'combo_price' => 380000,
                'original_price' => 435000,
                'discount_percent' => 12,
                'skus' => ['CLUB-COCK-001', 'CLUB-COCK-002', 'CLUB-COCK-004'],
            ],
        ];

        $productMap = $products->keyBy('sku');

        foreach ($combosData as $c) {
            $combo = ComboProduct::updateOrCreate(
                ['slug' => $c['slug']],
                [
                    'name' => $c['name'],
                    'description' => $c['description'],
                    'combo_price' => $c['combo_price'],
                    'original_price' => $c['original_price'],
                    'discount_percent' => $c['discount_percent'],
                    'is_active' => true,
                    'start_date' => now()->subDays(30),
                    'end_date' => now()->addMonths(6),
                ]
            );

            // Clear old items and attach
            DB::table('shop_combo_product_items')->where('combo_product_id', $combo->id)->delete();
            foreach ($c['skus'] as $idx => $sku) {
                $p = $productMap->get($sku);
                if ($p) {
                    DB::table('shop_combo_product_items')->insert([
                        'combo_product_id' => $combo->id,
                        'shop_product_id' => $p->id,
                        'quantity' => 1,
                        'sort_order' => $idx + 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    protected function seedUpsellsAndCrossSells($products): void
    {
        $productMap = $products->keyBy('sku');

        // Wine upsells to crystal glasses
        $wine = $productMap->get('CLUB-WINE-001');
        $glasses = $productMap->get('CLUB-MERCH-001');
        $steak = $productMap->get('CLUB-FOOD-001');
        $mojito = $productMap->get('CLUB-COCK-001');
        $fries = $productMap->get('CLUB-FOOD-004');

        if ($wine && $glasses) {
            UpsellProduct::updateOrCreate(
                ['shop_product_id' => $wine->id, 'upsell_product_id' => $glasses->id],
                ['sort_order' => 1, 'is_active' => true]
            );
        }

        if ($steak && $wine) {
            CrossSellProduct::updateOrCreate(
                ['shop_product_id' => $steak->id, 'cross_sell_product_id' => $wine->id],
                ['sort_order' => 1, 'is_active' => true]
            );
        }

        if ($mojito && $fries) {
            CrossSellProduct::updateOrCreate(
                ['shop_product_id' => $mojito->id, 'cross_sell_product_id' => $fries->id],
                ['sort_order' => 1, 'is_active' => true]
            );
        }
    }

    protected function seedCoupons(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'type' => 'percentage',
                'amount' => 10,
                'minimum_order_amount' => 200000,
                'usage_limit' => 500,
                'usage_limit_per_user' => 1,
                'is_active' => true,
                'expiry_date' => now()->addMonths(6),
            ],
            [
                'code' => 'VIPCLUB20',
                'type' => 'percentage',
                'amount' => 20,
                'minimum_order_amount' => 1000000,
                'usage_limit' => 200,
                'usage_limit_per_user' => 2,
                'is_active' => true,
                'expiry_date' => now()->addMonths(6),
            ],
            [
                'code' => 'HAPPYHOUR50',
                'type' => 'fixed_cart',
                'amount' => 50000,
                'minimum_order_amount' => 300000,
                'usage_limit' => 1000,
                'usage_limit_per_user' => 5,
                'is_active' => true,
                'expiry_date' => now()->addMonths(3),
            ],
            [
                'code' => 'FREESHIP',
                'type' => 'fixed_cart',
                'amount' => 35000,
                'minimum_order_amount' => 500000,
                'usage_limit' => null,
                'usage_limit_per_user' => 3,
                'is_active' => true,
                'expiry_date' => now()->addMonths(6),
            ],
        ];

        foreach ($coupons as $c) {
            Coupon::updateOrCreate(
                ['code' => $c['code']],
                $c
            );
        }
    }
}
