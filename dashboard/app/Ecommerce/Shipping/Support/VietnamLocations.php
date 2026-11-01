<?php

namespace App\Ecommerce\Shipping\Support;

class VietnamLocations
{
    /**
     * 25 Northern Provinces (Miền Bắc)
     * Format: 'Name' => 'ISO 3166-2 Code'
     */
    public const NORTH_PROVINCES = [
        'Hà Nội' => 'VN-HN',
        'Hải Phòng' => 'VN-HP',
        'Bắc Ninh' => 'VN-56',
        'Hà Nam' => 'VN-63',
        'Hải Dương' => 'VN-61',
        'Hưng Yên' => 'VN-66',
        'Nam Định' => 'VN-67',
        'Ninh Bình' => 'VN-18',
        'Thái Bình' => 'VN-20',
        'Vĩnh Phúc' => 'VN-70',
        'Hà Giang' => 'VN-03',
        'Cao Bằng' => 'VN-04',
        'Bắc Kạn' => 'VN-53',
        'Lạng Sơn' => 'VN-09',
        'Tuyên Quang' => 'VN-07',
        'Thái Nguyên' => 'VN-69',
        'Phú Thọ' => 'VN-68',
        'Bắc Giang' => 'VN-54',
        'Quảng Ninh' => 'VN-14',
        'Lào Cai' => 'VN-02',
        'Yên Bái' => 'VN-06',
        'Điện Biên' => 'VN-DB',
        'Hoà Bình' => 'VN-17',
        'Lai Châu' => 'VN-LC',
        'Sơn La' => 'VN-05',
    ];

    /**
     * 19 Central Provinces (Miền Trung & Tây Nguyên)
     */
    public const CENTRAL_PROVINCES = [
        'Thanh Hóa' => 'VN-21',
        'Nghệ An' => 'VN-22',
        'Hà Tĩnh' => 'VN-23',
        'Quảng Bình' => 'VN-24',
        'Quảng Trị' => 'VN-25',
        'Thừa Thiên Huế' => 'VN-26',
        'Đà Nẵng' => 'VN-DN',
        'Quảng Nam' => 'VN-27',
        'Quảng Ngãi' => 'VN-29',
        'Bình Định' => 'VN-31',
        'Phú Yên' => 'VN-32',
        'Khánh Hòa' => 'VN-34',
        'Ninh Thuận' => 'VN-36',
        'Bình Thuận' => 'VN-40',
        'Kon Tum' => 'VN-28',
        'Gia Lai' => 'VN-30',
        'Đắk Lắk' => 'VN-33',
        'Đắk Nông' => 'VN-72',
        'Lâm Đồng' => 'VN-35',
    ];

    /**
     * 19 Southern Provinces (Miền Nam)
     */
    public const SOUTH_PROVINCES = [
        'Hồ Chí Minh' => 'VN-SG',
        'Bà Rịa - Vũng Tàu' => 'VN-43',
        'Bình Dương' => 'VN-57',
        'Bình Phước' => 'VN-58',
        'Đồng Nai' => 'VN-39',
        'Tây Ninh' => 'VN-37',
        'An Giang' => 'VN-44',
        'Bến Tre' => 'VN-49',
        'Bạc Liêu' => 'VN-55',
        'Cà Mau' => 'VN-59',
        'Cần Thơ' => 'VN-CT',
        'Đồng Tháp' => 'VN-45',
        'Hậu Giang' => 'VN-73',
        'Kiên Giang' => 'VN-48',
        'Long An' => 'VN-41',
        'Sóc Trăng' => 'VN-52',
        'Tiền Giang' => 'VN-46',
        'Trà Vinh' => 'VN-51',
        'Vĩnh Long' => 'VN-47',
    ];

    /**
     * Get all 63 Vietnam provinces as key-value pairs (Name => Code)
     */
    public static function all(): array
    {
        return array_merge(
            self::NORTH_PROVINCES,
            self::CENTRAL_PROVINCES,
            self::SOUTH_PROVINCES
        );
    }

    /**
     * Normalize province name for resilient matching.
     */
    public static function normalize(string $name): string
    {
        $name = trim($name);
        
        // Remove common administrative prefixes
        $prefixes = [
            '/^(Thành phố|Tỉnh|TP\.|TP\s+)\s+/iu',
        ];
        $cleaned = preg_replace($prefixes, '', $name);

        return trim($cleaned);
    }

    /**
     * Check if a province belongs to a list of provinces (accepts name or ISO code).
     */
    public static function matches(string $needle, array $haystack): bool
    {
        if (empty($haystack)) {
            return false;
        }

        $normalizedNeedle = mb_strtolower(self::normalize($needle), 'UTF-8');
        $needleUpper = mb_strtoupper(trim($needle), 'UTF-8');

        foreach ($haystack as $item) {
            $itemNormalized = mb_strtolower(self::normalize($item), 'UTF-8');
            $itemUpper = mb_strtoupper(trim($item), 'UTF-8');

            if ($itemUpper === $needleUpper || $itemNormalized === $normalizedNeedle) {
                return true;
            }

            // Check if needle matches ISO code of item
            $all = self::all();
            if (isset($all[$item]) && $all[$item] === $needleUpper) {
                return true;
            }
            // Check if item matches ISO code of needle
            $codeToName = array_flip($all);
            if (isset($codeToName[$needleUpper]) && mb_strtolower($codeToName[$needleUpper], 'UTF-8') === $itemNormalized) {
                return true;
            }
        }

        return false;
    }
}
