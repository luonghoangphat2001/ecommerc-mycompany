<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = Product::class;

    public function definition(): array
    {
        $productsData = [
            'mojito' => [
                'name' => ['vi' => 'Cocktail Mojito truyền thống', 'en' => 'Classic Mojito Cocktail'],
                'description' => ['vi' => 'Sự kết hợp tươi mát từ lá bạc hà, chanh tươi, đường mía, rượu Rum và soda.', 'en' => 'A refreshing blend of mint leaves, fresh lime, cane sugar, white rum, and club soda.'],
                'price' => 120000, 'cost' => 30000, 'old_price' => 150000
            ],
            'margarita' => [
                'name' => ['vi' => 'Cocktail Margarita cổ điển', 'en' => 'Classic Margarita Cocktail'],
                'description' => ['vi' => 'Hương vị chua thanh từ nước cốt chanh, rượu Tequila và Triple Sec viền muối tinh tế.', 'en' => 'A tangy mix of Tequila, Triple Sec, and fresh lime juice, served with a salted rim.'],
                'price' => 140000, 'cost' => 35000, 'old_price' => 160000
            ],
            'martini' => [
                'name' => ['vi' => 'Cocktail Dry Martini thượng hạng', 'en' => 'Premium Dry Martini Cocktail'],
                'description' => ['vi' => 'Cocktail cổ điển pha chế từ rượu Gin hảo hạng và Dry Vermouth, trang trí quả ô liu.', 'en' => 'Classic cocktail made with premium Gin and Dry Vermouth, garnished with a green olive.'],
                'price' => 160000, 'cost' => 40000, 'old_price' => 180000
            ],
            'old_fashioned' => [
                'name' => ['vi' => 'Cocktail Old Fashioned cổ điển', 'en' => 'Old Fashioned Cocktail'],
                'description' => ['vi' => 'Sự phối trộn truyền thống giữa Bourbon Whiskey, đường và hương đắng thảo mộc.', 'en' => 'A timeless blend of Bourbon Whiskey, sugar, and aromatic bitters, served over ice.'] ,
                'price' => 180000, 'cost' => 50000, 'old_price' => 200000
            ],
            'tequila_sunrise' => [
                'name' => ['vi' => 'Cocktail Tequila Sunrise rực rỡ', 'en' => 'Tequila Sunrise Cocktail'],
                'description' => ['vi' => 'Rượu Tequila, nước cam tươi và si rô lựu tạo nên sắc màu hoàng hôn ấm áp.', 'en' => 'Tequila, fresh orange juice, and grenadine syrup creating a beautiful sunrise gradient.'],
                'price' => 135000, 'cost' => 35000, 'old_price' => 150000
            ],
            'chivas_12' => [
                'name' => ['vi' => 'Rượu Chivas Regal 12 năm tuổi (Chai 750ml)', 'en' => 'Chivas Regal 12 Year Old Scotch Whisky (750ml)'],
                'description' => ['vi' => 'Dòng Whisky Scotland mượt mà với hương mật ong, thạch nam và trái cây chín.', 'en' => 'Smooth Scotch Whisky with notes of honey, heather, and ripe orchard fruits.'],
                'price' => 1450000, 'cost' => 900000, 'old_price' => 1600000
            ],
            'macallan_12' => [
                'name' => ['vi' => 'Rượu Macallan Sherry Oak 12 năm tuổi', 'en' => 'Macallan Sherry Oak 12 Year Old Whisky'],
                'description' => ['vi' => 'Whisky mạch nha đơn cất ủ trong thùng gỗ sồi Sherry nhập từ Tây Ban Nha hảo hạng.', 'en' => 'Single malt whisky matured in seasoned Oloroso sherry oak casks from Jerez.'],
                'price' => 2800000, 'cost' => 1800000, 'old_price' => 3000000
            ],
            'hennessy_vsop' => [
                'name' => ['vi' => 'Rượu Cognac Hennessy VSOP đẳng cấp', 'en' => 'Hennessy VSOP Cognac'],
                'description' => ['vi' => 'Hương vị Cognac Pháp nồng nàn, hậu vị êm mượt khó quên từ những thùng sồi lâu năm.', 'en' => 'Rich French Cognac with a harmonious blend of oaky aromas and a velvety finish.'],
                'price' => 1950000, 'cost' => 1200000, 'old_price' => 2100000
            ],
            'bombay_gin' => [
                'name' => ['vi' => 'Rượu Gin Bombay Sapphire hảo hạng', 'en' => 'Bombay Sapphire London Dry Gin'],
                'description' => ['vi' => 'Hương vị Gin đặc trưng từ 10 loại thảo mộc quý được chưng cất hơi nước độc đáo.', 'en' => 'Distinguished Gin infused with 10 hand-selected botanicals via vapor distillation.'],
                'price' => 850000, 'cost' => 500000, 'old_price' => 950000
            ],
            'heineken_silver' => [
                'name' => ['vi' => 'Bia Heineken Silver (Chai 330ml)', 'en' => 'Heineken Silver Lager Beer (330ml)'],
                'description' => ['vi' => 'Bia nhẹ êm, hương vị sảng khoái dễ uống từ công nghệ ủ lạnh độc quyền.', 'en' => 'Extra-refreshing lager brewed with an ice-cold filtration process.'],
                'price' => 55000, 'cost' => 20000, 'old_price' => 65000
            ],
            'corona_extra' => [
                'name' => ['vi' => 'Bia Corona Extra 355ml nhập khẩu Mexico', 'en' => 'Corona Extra Mexico Imported Beer (355ml)'],
                'description' => ['vi' => 'Thưởng thức bia Corona ướp lạnh tuyệt hảo khi uống kèm một lát chanh tươi.', 'en' => 'Enjoy ice-cold Corona extra lager with a fresh slice of lime.'],
                'price' => 75000, 'cost' => 30000, 'old_price' => 85000
            ],
            'tiger_crystal' => [
                'name' => ['vi' => 'Bia Tiger Crystal chai mát lạnh', 'en' => 'Tiger Crystal Lager Beer (Bottle)'],
                'description' => ['vi' => 'Ủ lọc pha lê giữ hương bia đậm đà nhưng êm ái sảng khoái khó quên.', 'en' => 'Crystal cold filtered lager featuring full flavor and intense refreshment.'],
                'price' => 48000, 'cost' => 18000, 'old_price' => 55000
            ],
            'craft_ipa' => [
                'name' => ['vi' => 'Bia thủ công East West IPA đậm đà', 'en' => 'East West Craft IPA Beer'],
                'description' => ['vi' => 'Bia thủ công đậm hương hoa bia nhiệt đới và hậu vị đắng nhẹ đặc trưng.', 'en' => 'Craft IPA beer infused with tropical hop aromas and a signature bitter finish.'],
                'price' => 95000, 'cost' => 35000, 'old_price' => 110000
            ],
            'chateau_bordeaux' => [
                'name' => ['vi' => 'Vang đỏ Chateau Bordeaux cao cấp', 'en' => 'Chateau Bordeaux Premium Red Wine'],
                'description' => ['vi' => 'Vang đỏ danh tiếng từ vùng Bordeaux Pháp, đậm đà vị mận chín và gỗ sồi.', 'en' => 'Renowned red wine from Bordeaux France, featuring rich plum notes and oak finish.'],
                'price' => 850000, 'cost' => 450000, 'old_price' => 980000
            ],
            'dom_perignon' => [
                'name' => ['vi' => 'Vang nổ Champagne Dom Perignon', 'en' => 'Dom Perignon Vintage Champagne'],
                'description' => ['vi' => 'Biểu tượng Champagne Pháp thượng lưu với bọt sủi mịn màng, hương thơm tinh tế.', 'en' => 'Iconic vintage Champagne with fine bubbles, offering complexity and structure.'],
                'price' => 5500000, 'cost' => 3800000, 'old_price' => 6000000
            ],
            'caesar_salad' => [
                'name' => ['vi' => 'Salad Caesar gà nướng sốt truyền thống', 'en' => 'Grilled Chicken Caesar Salad'],
                'description' => ['vi' => 'Xà lách Romaine tươi giòn trộn phô mai Parmesan, thịt xông khói và sốt Caesar.', 'en' => 'Fresh Romaine lettuce tossed with Parmesan, crispy bacon, and Caesar dressing.'],
                'price' => 135000, 'cost' => 45000, 'old_price' => 155000
            ],
            'pumpkin_soup' => [
                'name' => ['vi' => 'Súp bí đỏ kem tươi béo ngậy kèm bánh mì', 'en' => 'Creamy Pumpkin Soup with Croutons'],
                'description' => ['vi' => 'Súp bí đỏ mịn, hòa quyện kem tươi béo ngậy, ăn kèm bánh mì bơ tỏi nướng giòn.', 'en' => 'Smooth roasted pumpkin soup with fresh cream, served with garlic croutons.'],
                'price' => 95000, 'cost' => 30000, 'old_price' => 110000
            ],
            'french_fries' => [
                'name' => ['vi' => 'Khoai tây chiên muối ớt Cajun lắc', 'en' => 'Spicy Cajun French Fries'],
                'description' => ['vi' => 'Khoai tây chiên vàng giòn rắc gia vị muối ớt Cajun thơm lừng kích thích vị giác.', 'en' => 'Crispy golden fries tossed in aromatic Cajun spices and sea salt.'],
                'price' => 75000, 'cost' => 20000, 'old_price' => 85000
            ],
            'chicken_wings' => [
                'name' => ['vi' => 'Cánh gà chiên nước mắm tỏi ớt giòn rụm', 'en' => 'Crispy Fish Sauce Chicken Wings with Chili Garlic'],
                'description' => ['vi' => 'Cánh gà ta chiên giòn lắc sốt nước mắm cốt nhĩ đậm đà, tỏi phi thơm lừng.', 'en' => 'Fried chicken wings glazed in a rich fish sauce caramel and crispy garlic.'],
                'price' => 125000, 'cost' => 45000, 'old_price' => 145000
            ],
            'wagyu_steak' => [
                'name' => ['vi' => 'Bò bít tết Wagyu sốt tiêu đen thượng hạng', 'en' => 'Premium Wagyu Beef Ribeye Steak with Black Pepper Sauce'],
                'description' => ['vi' => 'Thịt bò Wagyu áp chảo đạt độ mềm mọng như bơ tan, kèm khoai tây nghiền và sốt tiêu.', 'en' => 'Mouthwatering Wagyu ribeye steak cooked to perfection, with mashed potatoes and pepper sauce.'],
                'price' => 680000, 'cost' => 250000, 'old_price' => 750000
            ],
            'carbonara' => [
                'name' => ['vi' => 'Mỳ Ý Carbonara sốt kem phô mai và ba chỉ xông khói', 'en' => 'Spaghetti Carbonara with Smoked Bacon and Creamy Cheese'],
                'description' => ['vi' => 'Sợi mỳ Ý dai ngon ngập trong sốt kem lòng đỏ trứng, phô mai Pecorino Romano.', 'en' => 'Classic spaghetti tossed with egg yolk cream sauce, crispy bacon, and Pecorino Romano.'],
                'price' => 185000, 'cost' => 60000, 'old_price' => 210000
            ],
            'bbq_ribs' => [
                'name' => ['vi' => 'Sườn heo nướng sốt BBQ mật ong đậm đà', 'en' => 'Honey Glazed BBQ Pork Ribs'],
                'description' => ['vi' => 'Sườn heo non nướng chậm thơm phức, tẩm sốt BBQ mật ong đậm vị.', 'en' => 'Slow-roasted tender pork ribs glazed in our signature sweet honey BBQ sauce.'],
                'price' => 320000, 'cost' => 120000, 'old_price' => 360000
            ],
            'salmon' => [
                'name' => ['vi' => 'Cá hồi Na-uy áp chảo sốt chanh leo chua ngọt', 'en' => 'Pan-Seared Norwegian Salmon with Passion Fruit Sauce'],
                'description' => ['vi' => 'Fillet cá hồi áp chảo giòn da ngọt thịt, rưới sốt chanh leo thanh mát.', 'en' => 'Crispy skin pan-seared salmon fillet drizzled with zesty and sweet passion fruit reduction.'],
                'price' => 295000, 'cost' => 110000, 'old_price' => 330000
            ],
            'tiramisu' => [
                'name' => ['vi' => 'Bánh ngọt Tiramisu truyền thống Ý', 'en' => 'Traditional Italian Tiramisu Cake'],
                'description' => ['vi' => 'Bánh tiramisu béo ngậy vị phô mai Mascarpone, nồng nàn hương cà phê và rượu Rum.', 'en' => 'Rich Mascarpone cheese cake infused with coffee, cocoa powder, and dark Rum.'],
                'price' => 85000, 'cost' => 25000, 'old_price' => 95000
            ],
            'coconut_icecream' => [
                'name' => ['vi' => 'Kem dừa lạnh trong trái dừa tươi', 'en' => 'Fresh Coconut Ice Cream served in Coconut Shell'],
                'description' => ['vi' => 'Kem dừa thơm bùi đựng trong gáo dừa xiêm, rắc dừa khô và đậu phộng rang giòn.', 'en' => 'Creamy coconut ice cream garnished with toasted peanuts and coconut flakes.'],
                'price' => 90000, 'cost' => 30000, 'old_price' => 105000
            ],
            'panna_cotta' => [
                'name' => ['vi' => 'Panna Cotta sốt dâu tây chua ngọt dịu', 'en' => 'Strawberry Panna Cotta'],
                'description' => ['vi' => 'Món tráng miệng Ý mềm mịn từ sữa và kem béo, rưới sốt dâu tây tươi mát.', 'en' => 'Smooth and silky Italian pudding topped with a luscious fresh strawberry sauce.'],
                'price' => 65000, 'cost' => 20000, 'old_price' => 75000
            ],
            'coca_cola' => [
                'name' => ['vi' => 'Nước ngọt Coca-Cola lon mát lạnh', 'en' => 'Chilled Coca-Cola Can'],
                'description' => ['vi' => 'Nước ngọt có ga Coca-Cola 330ml uống cùng đá viên.', 'en' => 'Refreshing carbonated soft drink served with ice cubes.'],
                'price' => 35000, 'cost' => 10000, 'old_price' => 40000
            ],
            'orange_juice' => [
                'name' => ['vi' => 'Nước ép cam nguyên chất sảng khoái', 'en' => 'Freshly Squeezed Orange Juice'],
                'description' => ['vi' => 'Nước cam vắt nguyên chất giàu vitamin C tự nhiên, không thêm đường hóa học.', 'en' => '100% natural freshly squeezed orange juice, rich in Vitamin C.'],
                'price' => 65000, 'cost' => 20000, 'old_price' => 75000
            ],
            'peach_tea' => [
                'name' => ['vi' => 'Trà đào cam sả đá mát lạnh ngày hè', 'en' => 'Iced Peach Lemongrass Tea with Orange'],
                'description' => ['vi' => 'Trà đen ủ hương đào, sả tươi đập dập, lát cam vàng thơm nhẹ.', 'en' => 'Brewed black tea infused with sweet peach syrup, fresh lemongrass, and orange slices.'],
                'price' => 60000, 'cost' => 18000, 'old_price' => 70000
            ],
        ];

        $key = $this->faker->randomElement(array_keys($productsData));
        $data = $productsData[$key];

        return [
            'shop_brand_id' => Brand::factory(),
            'name' => [
                'vi' => $data['name']['vi'],
                'en' => $data['name']['en'],
            ],
            'slug' => Str::slug($data['name']['en']) . '-' . Str::random(4),
            'sku' => 'FB-' . strtoupper(Str::random(5)),
            'barcode' => $this->faker->ean13(),
            'description' => [
                'vi' => $data['description']['vi'],
                'en' => $data['description']['en'],
            ],
            'qty' => $this->faker->numberBetween(50, 200),
            'security_stock' => 10,
            'featured' => $this->faker->boolean(20),
            'is_visible' => true,
            'old_price' => $data['old_price'],
            'price' => $data['price'],
            'cost' => $data['cost'],
            'type' => 'deliverable',
            'product_images' => rand(1, 40),
            'published_at' => $this->faker->dateTimeBetween('-1 year', '+1 month'),
        ];
    }
}
