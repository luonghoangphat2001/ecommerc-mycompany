<?php

namespace Database\Seeders;

use App\Ecommerce\Shipping\Support\VietnamLocations;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedDomesticZones();
        $this->seedInternationalZones();
    }

    /**
     * Helper to create or update a multilingual zone
     */
    protected function updateOrCreateZone(string $code, array $nameTranslations, array $locations, int $sort, bool $isActive = true): ShippingZone
    {
        $zone = ShippingZone::firstOrNew(['code' => $code]);
        $zone->setTranslations('name', $nameTranslations);
        $zone->locations = $locations;
        $zone->sort = $sort;
        $zone->is_active = $isActive;
        $zone->save();

        return $zone;
    }

    /**
     * Seed Vietnam Domestic Shipping Zones
     */
    protected function seedDomesticZones(): void
    {
        // Clean up legacy unkeyed zones if any
        ShippingZone::whereNull('code')->orWhere('code', '')->delete();

        // 1. Northern Vietnam (Miền Bắc)
        $northProvinces = array_keys(VietnamLocations::NORTH_PROVINCES);
        $northZone = $this->updateOrCreateZone(
            'VN_NORTH',
            [
                'vi' => 'Miền Bắc',
                'en' => 'Northern Vietnam',
            ],
            [
                [
                    'country' => 'VN',
                    'provinces' => $northProvinces,
                ],
            ],
            1,
            true
        );

        $this->seedZoneMethods($northZone, [
            [
                'type' => 'flat_rate',
                'name' => [
                    'vi' => 'Giao hàng tiêu chuẩn Miền Bắc',
                    'en' => 'Northern Standard Delivery',
                ],
                'settings' => [
                    'cost' => 25000,
                    'estimated_days' => 2,
                    'currency' => 'VND',
                    'description' => [
                        'vi' => 'Giao hàng tận nơi 1-2 ngày làm việc.',
                        'en' => 'Door-to-door delivery within 1-2 business days.',
                    ],
                ],
                'is_enabled' => true,
            ],
            [
                'type' => 'flat_rate',
                'name' => [
                    'vi' => 'Giao hàng hỏa tốc Hà Nội & Miền Bắc',
                    'en' => 'Express Delivery Hanoi & North',
                ],
                'settings' => [
                    'cost' => 45000,
                    'estimated_days' => 1,
                    'currency' => 'VND',
                    'description' => [
                        'vi' => 'Nhận hàng trong ngày tại nội thành Hà Nội.',
                        'en' => 'Same-day delivery in inner Hanoi.',
                    ],
                ],
                'is_enabled' => true,
            ],
            [
                'type' => 'free_shipping',
                'name' => [
                    'vi' => 'Miễn phí vận chuyển Miền Bắc (Đơn từ 500k)',
                    'en' => 'Free Shipping North (Orders from 500k)',
                ],
                'settings' => [
                    'min_amount' => 500000,
                    'estimated_days' => 2,
                    'currency' => 'VND',
                    'description' => [
                        'vi' => 'Miễn phí giao hàng cho đơn hàng từ 500.000₫.',
                        'en' => 'Free delivery for orders of 500,000 VND or more.',
                    ],
                ],
                'is_enabled' => true,
            ],
            [
                'type' => 'weight_based',
                'name' => [
                    'vi' => 'Vận chuyển theo khối lượng Miền Bắc',
                    'en' => 'Weight-based Shipping North',
                ],
                'settings' => [
                    'rules' => [
                        ['min_weight' => 0, 'max_weight' => 1000, 'cost' => 22000],
                        ['min_weight' => 1000, 'max_weight' => 3000, 'cost' => 32000],
                        ['min_weight' => 3000, 'max_weight' => 10000, 'cost' => 50000],
                    ],
                    'additional_cost_per_kg' => 5000,
                    'default_cost' => 25000,
                    'free_shipping_threshold' => 500000,
                    'estimated_days' => 2,
                    'currency' => 'VND',
                    'description' => [
                        'vi' => 'Tính phí linh hoạt theo trọng lượng kiện hàng.',
                        'en' => 'Flexible rate calculated by package weight.',
                    ],
                ],
                'is_enabled' => true,
            ],
        ]);

        // 2. Central Vietnam (Miền Trung & Tây Nguyên)
        $centralProvinces = array_keys(VietnamLocations::CENTRAL_PROVINCES);
        $centralZone = $this->updateOrCreateZone(
            'VN_CENTRAL',
            [
                'vi' => 'Miền Trung & Tây Nguyên',
                'en' => 'Central Vietnam & Highlands',
            ],
            [
                [
                    'country' => 'VN',
                    'provinces' => $centralProvinces,
                ],
            ],
            2,
            true
        );

        $this->seedZoneMethods($centralZone, [
            [
                'type' => 'flat_rate',
                'name' => [
                    'vi' => 'Giao hàng tiêu chuẩn Miền Trung',
                    'en' => 'Central Standard Delivery',
                ],
                'settings' => [
                    'cost' => 30000,
                    'estimated_days' => 3,
                    'currency' => 'VND',
                    'description' => [
                        'vi' => 'Giao hàng tận nơi 2-3 ngày làm việc.',
                        'en' => 'Door-to-door delivery within 2-3 business days.',
                    ],
                ],
                'is_enabled' => true,
            ],
            [
                'type' => 'flat_rate',
                'name' => [
                    'vi' => 'Giao hàng nhanh Đà Nẵng & Miền Trung',
                    'en' => 'Fast Delivery Da Nang & Central',
                ],
                'settings' => [
                    'cost' => 50000,
                    'estimated_days' => 1,
                    'currency' => 'VND',
                    'description' => [
                        'vi' => 'Giao nhanh 24h tại Đà Nẵng, Huế, Nha Trang.',
                        'en' => 'Fast 24h delivery in Da Nang, Hue, Nha Trang.',
                    ],
                ],
                'is_enabled' => true,
            ],
            [
                'type' => 'free_shipping',
                'name' => [
                    'vi' => 'Miễn phí vận chuyển Miền Trung (Đơn từ 600k)',
                    'en' => 'Free Shipping Central (Orders from 600k)',
                ],
                'settings' => [
                    'min_amount' => 600000,
                    'estimated_days' => 3,
                    'currency' => 'VND',
                    'description' => [
                        'vi' => 'Miễn phí giao hàng cho đơn hàng từ 600.000₫.',
                        'en' => 'Free delivery for orders of 600,000 VND or more.',
                    ],
                ],
                'is_enabled' => true,
            ],
            [
                'type' => 'weight_based',
                'name' => [
                    'vi' => 'Vận chuyển theo khối lượng Miền Trung',
                    'en' => 'Weight-based Shipping Central',
                ],
                'settings' => [
                    'rules' => [
                        ['min_weight' => 0, 'max_weight' => 1000, 'cost' => 25000],
                        ['min_weight' => 1000, 'max_weight' => 3000, 'cost' => 38000],
                        ['min_weight' => 3000, 'max_weight' => 10000, 'cost' => 55000],
                    ],
                    'additional_cost_per_kg' => 6000,
                    'default_cost' => 30000,
                    'free_shipping_threshold' => 600000,
                    'estimated_days' => 3,
                    'currency' => 'VND',
                    'description' => [
                        'vi' => 'Tính phí linh hoạt theo trọng lượng kiện hàng.',
                        'en' => 'Flexible rate calculated by package weight.',
                    ],
                ],
                'is_enabled' => true,
            ],
        ]);

        // 3. Southern Vietnam (Miền Nam)
        $southProvinces = array_keys(VietnamLocations::SOUTH_PROVINCES);
        $southZone = $this->updateOrCreateZone(
            'VN_SOUTH',
            [
                'vi' => 'Miền Nam',
                'en' => 'Southern Vietnam',
            ],
            [
                [
                    'country' => 'VN',
                    'provinces' => $southProvinces,
                ],
            ],
            3,
            true
        );

        $this->seedZoneMethods($southZone, [
            [
                'type' => 'flat_rate',
                'name' => [
                    'vi' => 'Giao hàng tiêu chuẩn Miền Nam',
                    'en' => 'Southern Standard Delivery',
                ],
                'settings' => [
                    'cost' => 25000,
                    'estimated_days' => 2,
                    'currency' => 'VND',
                    'description' => [
                        'vi' => 'Giao hàng tận nơi 1-2 ngày làm việc.',
                        'en' => 'Door-to-door delivery within 1-2 business days.',
                    ],
                ],
                'is_enabled' => true,
            ],
            [
                'type' => 'flat_rate',
                'name' => [
                    'vi' => 'Giao hàng hỏa tốc TP.HCM 2 giờ',
                    'en' => 'HCM City 2-Hour Express Delivery',
                ],
                'settings' => [
                    'cost' => 45000,
                    'estimated_days' => 1,
                    'currency' => 'VND',
                    'description' => [
                        'vi' => 'Giao hỏa tốc nhận hàng trong 2 giờ tại TP.HCM.',
                        'en' => 'Express delivery within 2 hours in HCM City.',
                    ],
                ],
                'is_enabled' => true,
            ],
            [
                'type' => 'free_shipping',
                'name' => [
                    'vi' => 'Miễn phí vận chuyển Miền Nam (Đơn từ 500k)',
                    'en' => 'Free Shipping South (Orders from 500k)',
                ],
                'settings' => [
                    'min_amount' => 500000,
                    'estimated_days' => 2,
                    'currency' => 'VND',
                    'description' => [
                        'vi' => 'Miễn phí giao hàng cho đơn hàng từ 500.000₫.',
                        'en' => 'Free delivery for orders of 500,000 VND or more.',
                    ],
                ],
                'is_enabled' => true,
            ],
            [
                'type' => 'weight_based',
                'name' => [
                    'vi' => 'Vận chuyển theo khối lượng Miền Nam',
                    'en' => 'Weight-based Shipping South',
                ],
                'settings' => [
                    'rules' => [
                        ['min_weight' => 0, 'max_weight' => 1000, 'cost' => 22000],
                        ['min_weight' => 1000, 'max_weight' => 3000, 'cost' => 32000],
                        ['min_weight' => 3000, 'max_weight' => 10000, 'cost' => 50000],
                    ],
                    'additional_cost_per_kg' => 5000,
                    'default_cost' => 25000,
                    'free_shipping_threshold' => 500000,
                    'estimated_days' => 2,
                    'currency' => 'VND',
                    'description' => [
                        'vi' => 'Tính phí linh hoạt theo trọng lượng kiện hàng.',
                        'en' => 'Flexible rate calculated by package weight.',
                    ],
                ],
                'is_enabled' => true,
            ],
        ]);

        // 4. Nationwide Vietnam Fallback (Toàn quốc)
        $nationwideZone = $this->updateOrCreateZone(
            'VN_NATIONWIDE',
            [
                'vi' => 'Toàn quốc (Việt Nam)',
                'en' => 'Nationwide Vietnam Fallback',
            ],
            [
                [
                    'country' => 'VN',
                    'provinces' => [], // Empty means all provinces fallback
                ],
            ],
            10,
            true
        );

        $this->seedZoneMethods($nationwideZone, [
            [
                'type' => 'flat_rate',
                'name' => [
                    'vi' => 'Giao hàng tiêu chuẩn toàn quốc',
                    'en' => 'Standard Nationwide Delivery',
                ],
                'settings' => [
                    'cost' => 35000,
                    'estimated_days' => 4,
                    'currency' => 'VND',
                    'description' => [
                        'vi' => 'Giao hàng liên tỉnh toàn quốc 3-4 ngày.',
                        'en' => 'Nationwide inter-provincial delivery within 3-4 days.',
                    ],
                ],
                'is_enabled' => true,
            ],
            [
                'type' => 'free_shipping',
                'name' => [
                    'vi' => 'Miễn phí giao hàng toàn quốc (Đơn từ 700k)',
                    'en' => 'Free Nationwide Delivery (Orders from 700k)',
                ],
                'settings' => [
                    'min_amount' => 700000,
                    'estimated_days' => 4,
                    'currency' => 'VND',
                    'description' => [
                        'vi' => 'Miễn phí giao hàng toàn quốc cho đơn hàng đủ điều kiện.',
                        'en' => 'Free nationwide delivery for eligible orders.',
                    ],
                ],
                'is_enabled' => true,
            ],
            [
                'type' => 'weight_based',
                'name' => [
                    'vi' => 'Vận chuyển theo khối lượng toàn quốc',
                    'en' => 'Weight-based Shipping Nationwide',
                ],
                'settings' => [
                    'rules' => [
                        ['min_weight' => 0, 'max_weight' => 1000, 'cost' => 30000],
                        ['min_weight' => 1000, 'max_weight' => 3000, 'cost' => 45000],
                        ['min_weight' => 3000, 'max_weight' => 10000, 'cost' => 70000],
                    ],
                    'additional_cost_per_kg' => 7000,
                    'default_cost' => 35000,
                    'free_shipping_threshold' => 700000,
                    'estimated_days' => 4,
                    'currency' => 'VND',
                    'description' => [
                        'vi' => 'Biểu phí theo khối lượng toàn quốc.',
                        'en' => 'Nationwide weight-tiered shipping rates.',
                    ],
                ],
                'is_enabled' => true,
            ],
        ]);
    }

    /**
     * Seed International Shipping Zones
     */
    protected function seedInternationalZones(): void
    {
        $continents = config('continents', []);

        $internationalConfigs = [
            'AS' => [
                'code' => 'INT_ASIA',
                'name' => [
                    'vi' => 'Châu Á',
                    'en' => 'Asia',
                ],
                'sort' => 20,
                'methods' => [
                    [
                        'type' => 'flat_rate',
                        'name' => [
                            'vi' => 'Chuyển phát tiêu chuẩn Châu Á',
                            'en' => 'Asia Standard Air Courier',
                        ],
                        'settings' => [
                            'cost' => 250000,
                            'estimated_days' => 7,
                            'currency' => 'VND',
                            'description' => [
                                'vi' => 'Chuyển phát hàng không tới các nước Châu Á 5-7 ngày.',
                                'en' => 'Air delivery to Asian countries within 5-7 days.',
                            ],
                        ],
                    ],
                    [
                        'type' => 'flat_rate',
                        'name' => [
                            'vi' => 'Chuyển phát nhanh Châu Á',
                            'en' => 'Asia Express Air Courier',
                        ],
                        'settings' => [
                            'cost' => 450000,
                            'estimated_days' => 4,
                            'currency' => 'VND',
                            'description' => [
                                'vi' => 'Chuyển phát hỏa tốc hàng không Châu Á 3-4 ngày.',
                                'en' => 'Priority air delivery to Asian countries within 3-4 days.',
                            ],
                        ],
                    ],
                ],
            ],
            'EU' => [
                'code' => 'INT_EUROPE',
                'name' => [
                    'vi' => 'Châu Âu',
                    'en' => 'Europe',
                ],
                'sort' => 21,
                'methods' => [
                    [
                        'type' => 'flat_rate',
                        'name' => [
                            'vi' => 'Chuyển phát tiêu chuẩn Châu Âu',
                            'en' => 'Europe Standard Air Courier',
                        ],
                        'settings' => [
                            'cost' => 400000,
                            'estimated_days' => 10,
                            'currency' => 'VND',
                            'description' => [
                                'vi' => 'Chuyển phát hàng không tới các nước Châu Âu 7-10 ngày.',
                                'en' => 'Air delivery to European countries within 7-10 days.',
                            ],
                        ],
                    ],
                    [
                        'type' => 'flat_rate',
                        'name' => [
                            'vi' => 'Chuyển phát nhanh DHL Express Châu Âu',
                            'en' => 'Europe DHL Express Priority',
                        ],
                        'settings' => [
                            'cost' => 750000,
                            'estimated_days' => 5,
                            'currency' => 'VND',
                            'description' => [
                                'vi' => 'Chuyển phát ưu tiên quốc tế Châu Âu 3-5 ngày.',
                                'en' => 'International priority express to Europe within 3-5 days.',
                            ],
                        ],
                    ],
                ],
            ],
            'NA' => [
                'code' => 'INT_NORTH_AMERICA',
                'name' => [
                    'vi' => 'Bắc Mỹ',
                    'en' => 'North America',
                ],
                'sort' => 22,
                'methods' => [
                    [
                        'type' => 'flat_rate',
                        'name' => [
                            'vi' => 'Chuyển phát tiêu chuẩn Bắc Mỹ',
                            'en' => 'North America Standard Air Courier',
                        ],
                        'settings' => [
                            'cost' => 450000,
                            'estimated_days' => 10,
                            'currency' => 'VND',
                            'description' => [
                                'vi' => 'Chuyển phát hàng không tới Hoa Kỳ & Canada 7-10 ngày.',
                                'en' => 'Air delivery to USA & Canada within 7-10 days.',
                            ],
                        ],
                    ],
                    [
                        'type' => 'flat_rate',
                        'name' => [
                            'vi' => 'Chuyển phát nhanh FedEx/DHL Bắc Mỹ',
                            'en' => 'North America FedEx/DHL Express',
                        ],
                        'settings' => [
                            'cost' => 850000,
                            'estimated_days' => 4,
                            'currency' => 'VND',
                            'description' => [
                                'vi' => 'Chuyển phát ưu tiên Bắc Mỹ 3-4 ngày làm việc.',
                                'en' => 'Priority air express to North America within 3-4 business days.',
                            ],
                        ],
                    ],
                ],
            ],
            'SA' => [
                'code' => 'INT_SOUTH_AMERICA',
                'name' => [
                    'vi' => 'Nam Mỹ & Mỹ Latinh',
                    'en' => 'South America & Latin America',
                ],
                'sort' => 23,
                'methods' => [
                    [
                        'type' => 'flat_rate',
                        'name' => [
                            'vi' => 'Chuyển phát quốc tế Nam Mỹ',
                            'en' => 'South America International Air Delivery',
                        ],
                        'settings' => [
                            'cost' => 550000,
                            'estimated_days' => 14,
                            'currency' => 'VND',
                            'description' => [
                                'vi' => 'Vận chuyển quốc tế tới các nước Nam Mỹ.',
                                'en' => 'International courier to South American destinations.',
                            ],
                        ],
                    ],
                ],
            ],
            'AF' => [
                'code' => 'INT_AFRICA',
                'name' => [
                    'vi' => 'Châu Phi',
                    'en' => 'Africa',
                ],
                'sort' => 24,
                'methods' => [
                    [
                        'type' => 'flat_rate',
                        'name' => [
                            'vi' => 'Chuyển phát quốc tế Châu Phi',
                            'en' => 'Africa International Air Delivery',
                        ],
                        'settings' => [
                            'cost' => 600000,
                            'estimated_days' => 14,
                            'currency' => 'VND',
                            'description' => [
                                'vi' => 'Vận chuyển quốc tế tới các nước Châu Phi.',
                                'en' => 'International air delivery to African destinations.',
                            ],
                        ],
                    ],
                ],
            ],
            'OC' => [
                'code' => 'INT_OCEANIA',
                'name' => [
                    'vi' => 'Châu Đại Dương',
                    'en' => 'Oceania (Australia & NZ)',
                ],
                'sort' => 25,
                'methods' => [
                    [
                        'type' => 'flat_rate',
                        'name' => [
                            'vi' => 'Chuyển phát tiêu chuẩn Châu Đại Dương',
                            'en' => 'Oceania Standard Air Courier',
                        ],
                        'settings' => [
                            'cost' => 350000,
                            'estimated_days' => 8,
                            'currency' => 'VND',
                            'description' => [
                                'vi' => 'Chuyển phát tới Úc và New Zealand 6-8 ngày.',
                                'en' => 'Air delivery to Australia & New Zealand within 6-8 days.',
                            ],
                        ],
                    ],
                    [
                        'type' => 'flat_rate',
                        'name' => [
                            'vi' => 'Chuyển phát nhanh Châu Đại Dương',
                            'en' => 'Oceania Express Air Courier',
                        ],
                        'settings' => [
                            'cost' => 650000,
                            'estimated_days' => 4,
                            'currency' => 'VND',
                            'description' => [
                                'vi' => 'Chuyển phát hỏa tốc tới Úc và New Zealand 3-4 ngày.',
                                'en' => 'Priority air express to Australia & New Zealand within 3-4 days.',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($internationalConfigs as $continentCode => $item) {
            $countries = $continents[$continentCode]['countries'] ?? [];
            // Remove VN from Asia if present since Vietnam has domestic zones
            if ($continentCode === 'AS') {
                $countries = array_values(array_diff($countries, ['VN']));
            }

            $zone = $this->updateOrCreateZone(
                $item['code'],
                $item['name'],
                [
                    [
                        'countries' => $countries,
                        'provinces' => [],
                    ],
                ],
                $item['sort'],
                true
            );

            $this->seedZoneMethods($zone, $item['methods']);
        }

        // Worldwide fallback
        $worldwideZone = $this->updateOrCreateZone(
            'INT_REST_OF_WORLD',
            [
                'vi' => 'Quốc tế khác (Toàn cầu)',
                'en' => 'Rest of World (Global Fallback)',
            ],
            [
                [
                    'country' => '*',
                    'provinces' => [],
                ],
            ],
            99,
            true
        );

        $this->seedZoneMethods($worldwideZone, [
            [
                'type' => 'flat_rate',
                'name' => [
                    'vi' => 'Chuyển phát quốc tế tiêu chuẩn',
                    'en' => 'Global Standard International Delivery',
                ],
                'settings' => [
                    'cost' => 650000,
                    'estimated_days' => 15,
                    'currency' => 'VND',
                    'description' => [
                        'vi' => 'Vận chuyển quốc tế tới các quốc gia và vùng lãnh thổ khác.',
                        'en' => 'International delivery to other worldwide destinations.',
                    ],
                ],
                'is_enabled' => true,
            ],
        ]);
    }

    /**
     * Seed methods for a given zone with full multilingual translations
     */
    protected function seedZoneMethods(ShippingZone $zone, array $methods): void
    {
        // Clean up previous methods for this zone to avoid duplicates and ensure clean translations
        ShippingMethod::where('shipping_zone_id', $zone->id)->delete();

        foreach ($methods as $methodData) {
            $method = new ShippingMethod();
            $method->shipping_zone_id = $zone->id;
            $method->type = $methodData['type'];
            
            $nameTranslations = is_array($methodData['name'])
                ? $methodData['name']
                : ['vi' => $methodData['name'], 'en' => $methodData['name']];
            $method->setTranslations('name', $nameTranslations);

            $method->settings = $methodData['settings'];
            $method->is_enabled = $methodData['is_enabled'] ?? true;
            $method->save();
        }
    }
}
