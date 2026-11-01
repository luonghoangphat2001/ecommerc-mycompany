<?php

namespace Database\Seeders\Demo;

use App\Enums\AddressType;
use App\Models\Address;
use App\Models\LoyaltyLog;
use App\Models\LoyaltyPoint;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class CustomerSeeder extends Seeder
{
    /**
     * Run the customer demo seeder.
     */
    public function run(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'Thành viên đăng ký', 'guard_name' => 'web']);

        // Create Default System Super Admin if not present
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'System Administrator',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'gender' => 'male',
                'phone' => '0901000001',
                'birthday' => '1988-01-01',
                'bio' => 'Nova Club Administrator with full system management privileges.',
            ]
        );
        if (!$admin->hasRole('super_admin')) {
            $admin->assignRole($superAdminRole);
        }

        $customerDefinitions = [
            // 1. VIP Club Customers
            [
                'email' => 'vip.hoanganh@clubstore.vn',
                'name' => 'Nguyễn Hoàng Anh',
                'phone' => '0903123456',
                'gender' => 'male',
                'birthday' => '1990-05-15',
                'bio' => 'Thành viên VIP Diamond của Nova Athletic Club, thường xuyên mua áo đấu và vé trọn mùa giải.',
                'tier' => 'vip',
                'points' => 3500,
                'lifetime_points' => 8500,
                'addresses' => [
                    [
                        'type' => AddressType::Shipping,
                        'first_name' => 'Hoàng Anh',
                        'last_name' => 'Nguyễn',
                        'company' => 'Apex Horizon Capital',
                        'phone' => '0903123456',
                        'email' => 'vip.hoanganh@clubstore.vn',
                        'country_code' => 'VN',
                        'state_id' => 'Thành phố Hồ Chí Minh',
                        'city_id' => 'Quận 1',
                        'ward_id' => 'Phường Bến Nghé',
                        'address_detail' => 'Tầng 25, Bitexco Financial Tower, số 2 Hải Triều',
                        'postal_code' => '70000',
                    ],
                    [
                        'type' => AddressType::Billing,
                        'first_name' => 'Hoàng Anh',
                        'last_name' => 'Nguyễn',
                        'company' => 'Apex Horizon Capital',
                        'phone' => '0903123456',
                        'email' => 'vip.hoanganh@clubstore.vn',
                        'country_code' => 'VN',
                        'state_id' => 'Thành phố Hồ Chí Minh',
                        'city_id' => 'Quận 2 (TP. Thủ Đức)',
                        'ward_id' => 'Phường Thảo Điền',
                        'address_detail' => 'Biệt thự Riviera 18 Giang Văn Minh',
                        'postal_code' => '71100',
                    ],
                ],
            ],
            [
                'email' => 'vip.phuongthao@clubstore.vn',
                'name' => 'Lê Phương Thảo',
                'phone' => '0918889999',
                'gender' => 'female',
                'birthday' => '1993-08-20',
                'bio' => 'Sommelier nghiệp dư, đam mê sưu tập vang đỏ Pháp và champagne cổ điển.',
                'tier' => 'vip',
                'points' => 2800,
                'lifetime_points' => 6200,
                'addresses' => [
                    [
                        'type' => AddressType::Shipping,
                        'first_name' => 'Phương Thảo',
                        'last_name' => 'Lê',
                        'company' => 'Thảo Lê Media Studio',
                        'phone' => '0918889999',
                        'email' => 'vip.phuongthao@clubstore.vn',
                        'country_code' => 'VN',
                        'state_id' => 'Thành phố Hồ Chí Minh',
                        'city_id' => 'Quận 7',
                        'ward_id' => 'Phường Tân Phong',
                        'address_detail' => 'Khu Cảnh Viên 2, Đường số 17, Phú Mỹ Hưng',
                        'postal_code' => '72900',
                    ],
                ],
            ],

            // 2. Returning Loyal Customers
            [
                'email' => 'minhtuan.tran@clubstore.vn',
                'name' => 'Trần Minh Tuấn',
                'phone' => '0988112233',
                'gender' => 'male',
                'birthday' => '1988-12-04',
                'bio' => 'Khách hàng thân thiết, thường mua bia thủ công craft beer và snack cuối tuần.',
                'tier' => 'regular',
                'points' => 950,
                'lifetime_points' => 2400,
                'addresses' => [
                    [
                        'type' => AddressType::Shipping,
                        'first_name' => 'Minh Tuấn',
                        'last_name' => 'Trần',
                        'phone' => '0988112233',
                        'email' => 'minhtuan.tran@clubstore.vn',
                        'country_code' => 'VN',
                        'state_id' => 'Thành phố Hồ Chí Minh',
                        'city_id' => 'Quận 3',
                        'ward_id' => 'Phường Võ Thị Sáu',
                        'address_detail' => '184bis Điện Biên Phủ',
                        'postal_code' => '72000',
                    ],
                ],
            ],
            [
                'email' => 'quocbao.pham@clubstore.vn',
                'name' => 'Phạm Quốc Bảo',
                'phone' => '0937456789',
                'gender' => 'male',
                'birthday' => '1995-03-28',
                'bio' => 'Tín đồ cocktail mixology, thích thử nghiệm các hương vị rượu mới.',
                'tier' => 'regular',
                'points' => 620,
                'lifetime_points' => 1500,
                'addresses' => [
                    [
                        'type' => AddressType::Shipping,
                        'first_name' => 'Quốc Bảo',
                        'last_name' => 'Phạm',
                        'phone' => '0937456789',
                        'email' => 'quocbao.pham@clubstore.vn',
                        'country_code' => 'VN',
                        'state_id' => 'Thành phố Hồ Chí Minh',
                        'city_id' => 'Quận Bình Thạnh',
                        'ward_id' => 'Phường 22',
                        'address_detail' => 'Tòa Landmark 4, Vinhomes Central Park, 208 Nguyễn Hữu Cảnh',
                        'postal_code' => '72300',
                    ],
                ],
            ],
            [
                'email' => 'haidang.vu@clubstore.vn',
                'name' => 'Vũ Hải Đăng',
                'phone' => '0979555666',
                'gender' => 'male',
                'birthday' => '1992-10-10',
                'bio' => 'Khách hàng thường xuyên đặt tiệc công ty và quà tặng đối tác cuối năm.',
                'tier' => 'regular',
                'points' => 1200,
                'lifetime_points' => 3100,
                'addresses' => [
                    [
                        'type' => AddressType::Shipping,
                        'first_name' => 'Hải Đăng',
                        'last_name' => 'Vũ',
                        'company' => 'Đăng Vũ Tech Solutions',
                        'phone' => '0979555666',
                        'email' => 'haidang.vu@clubstore.vn',
                        'country_code' => 'VN',
                        'state_id' => 'Thành phố Hồ Chí Minh',
                        'city_id' => 'Quận 1',
                        'ward_id' => 'Phường Đa Kao',
                        'address_detail' => '68 Nguyễn Thị Minh Khai',
                        'postal_code' => '70000',
                    ],
                ],
            ],
            [
                'email' => 'thutrang.do@clubstore.vn',
                'name' => 'Đỗ Thu Trang',
                'phone' => '0908777888',
                'gender' => 'female',
                'birthday' => '1996-07-07',
                'bio' => 'Yêu thích các loại cocktail thanh nhẹ và món tráng miệng Ý.',
                'tier' => 'regular',
                'points' => 450,
                'lifetime_points' => 1100,
                'addresses' => [
                    [
                        'type' => AddressType::Shipping,
                        'first_name' => 'Thu Trang',
                        'last_name' => 'Đỗ',
                        'phone' => '0908777888',
                        'email' => 'thutrang.do@clubstore.vn',
                        'country_code' => 'VN',
                        'state_id' => 'Thành phố Hồ Chí Minh',
                        'city_id' => 'Quận Phú Nhuận',
                        'ward_id' => 'Phường 7',
                        'address_detail' => '42 Phan Xích Long',
                        'postal_code' => '72200',
                    ],
                ],
            ],

            // 3. Customers with Special Scenarios (Pending, Cancelled, Refunded)
            [
                'email' => 'dinhtrong.bui@clubstore.vn',
                'name' => 'Bùi Đình Trọng',
                'phone' => '0933221100',
                'gender' => 'male',
                'birthday' => '1991-01-18',
                'bio' => 'Khách hàng có đơn đặt tiệc đang chờ vận chuyển đến sự kiện.',
                'tier' => 'regular',
                'points' => 300,
                'lifetime_points' => 900,
                'addresses' => [
                    [
                        'type' => AddressType::Shipping,
                        'first_name' => 'Đình Trọng',
                        'last_name' => 'Bùi',
                        'phone' => '0933221100',
                        'email' => 'dinhtrong.bui@clubstore.vn',
                        'country_code' => 'VN',
                        'state_id' => 'Thành phố Hồ Chí Minh',
                        'city_id' => 'Quận Tân Bình',
                        'ward_id' => 'Phường 2',
                        'address_detail' => '15 Cửu Long',
                        'postal_code' => '72500',
                    ],
                ],
            ],
            [
                'email' => 'yennhi.hoang@clubstore.vn',
                'name' => 'Hoàng Yến Nhi',
                'phone' => '0902345678',
                'gender' => 'female',
                'birthday' => '1998-09-23',
                'bio' => 'Khách hàng từng đổi sản phẩm do lỗi tem nhập khẩu, đã được CSKH xử lý hoàn tiền chu đáo.',
                'tier' => 'regular',
                'points' => 200,
                'lifetime_points' => 500,
                'addresses' => [
                    [
                        'type' => AddressType::Shipping,
                        'first_name' => 'Yến Nhi',
                        'last_name' => 'Hoàng',
                        'phone' => '0902345678',
                        'email' => 'yennhi.hoang@clubstore.vn',
                        'country_code' => 'VN',
                        'state_id' => 'Thành phố Hồ Chí Minh',
                        'city_id' => 'Quận 4',
                        'ward_id' => 'Phường 12',
                        'address_detail' => 'Chung cư Masteri Millennium, 132 Bến Vân Đồn',
                        'postal_code' => '72800',
                    ],
                ],
            ],
            [
                'email' => 'ngocanh.phan@clubstore.vn',
                'name' => 'Phan Ngọc Ánh',
                'phone' => '0912334455',
                'gender' => 'female',
                'birthday' => '1994-11-12',
                'bio' => 'Khách hàng có lịch sử hủy đơn do thay đổi thời gian tổ chức tiệc sinh nhật.',
                'tier' => 'regular',
                'points' => 100,
                'lifetime_points' => 400,
                'addresses' => [
                    [
                        'type' => AddressType::Shipping,
                        'first_name' => 'Ngọc Ánh',
                        'last_name' => 'Phan',
                        'phone' => '0912334455',
                        'email' => 'ngocanh.phan@clubstore.vn',
                        'country_code' => 'VN',
                        'state_id' => 'Thành phố Hồ Chí Minh',
                        'city_id' => 'Quận 10',
                        'ward_id' => 'Phường 12',
                        'address_detail' => 'Khu dân cư Hà Đô Centrosa, 200 Ba Tháng Hai',
                        'postal_code' => '72400',
                    ],
                ],
            ],

            // 4. Newly Registered Customers
            [
                'email' => 'hoanglong.nguyen@clubstore.vn',
                'name' => 'Nguyễn Hoàng Long',
                'phone' => '0966778899',
                'gender' => 'male',
                'birthday' => '2001-04-14',
                'bio' => 'Thành viên mới đăng ký tài khoản trải nghiệm dịch vụ Club Store.',
                'tier' => 'new',
                'points' => 50,
                'lifetime_points' => 50,
                'addresses' => [
                    [
                        'type' => AddressType::Shipping,
                        'first_name' => 'Hoàng Long',
                        'last_name' => 'Nguyễn',
                        'phone' => '0966778899',
                        'email' => 'hoanglong.nguyen@clubstore.vn',
                        'country_code' => 'VN',
                        'state_id' => 'Thành phố Hồ Chí Minh',
                        'city_id' => 'Quận 1',
                        'ward_id' => 'Phường Cầu Kho',
                        'address_detail' => '391 Trần Hưng Đạo',
                        'postal_code' => '70000',
                    ],
                ],
            ],
            [
                'email' => 'thanhmai.le@clubstore.vn',
                'name' => 'Lê Thanh Mai',
                'phone' => '0934112244',
                'gender' => 'female',
                'birthday' => '1999-06-30',
                'bio' => 'Thành viên mới đăng ký quan tâm đến các workshop pha chế cocktail cuối tuần.',
                'tier' => 'new',
                'points' => 50,
                'lifetime_points' => 50,
                'addresses' => [
                    [
                        'type' => AddressType::Shipping,
                        'first_name' => 'Thanh Mai',
                        'last_name' => 'Lê',
                        'phone' => '0934112244',
                        'email' => 'thanhmai.le@clubstore.vn',
                        'country_code' => 'VN',
                        'state_id' => 'Thành phố Hồ Chí Minh',
                        'city_id' => 'Quận 5',
                        'ward_id' => 'Phường 1',
                        'address_detail' => '102 An Dương Vương',
                        'postal_code' => '72700',
                    ],
                ],
            ],
        ];

        // Add more realistic names to reach ~25 customers
        $additionalNames = [
            ['name' => 'Ngô Bảo Châu', 'gender' => 'male', 'email' => 'baochau.ngo@clubstore.vn', 'phone' => '0944111222'],
            ['name' => 'Trịnh Thùy Linh', 'gender' => 'female', 'email' => 'thuylinh.trinh@clubstore.vn', 'phone' => '0944222333'],
            ['name' => 'Lý Quốc Hùng', 'gender' => 'male', 'email' => 'quochung.ly@clubstore.vn', 'phone' => '0944333444'],
            ['name' => 'Dương Thúy Nga', 'gender' => 'female', 'email' => 'thuynga.duong@clubstore.vn', 'phone' => '0944444555'],
            ['name' => 'Đinh Tiến Dũng', 'gender' => 'male', 'email' => 'tiendung.dinh@clubstore.vn', 'phone' => '0944555666'],
            ['name' => 'Mai Phương Lan', 'gender' => 'female', 'email' => 'phuonglan.mai@clubstore.vn', 'phone' => '0944666777'],
            ['name' => 'Võ Minh Khang', 'gender' => 'male', 'email' => 'minhkhang.vo@clubstore.vn', 'phone' => '0944777888'],
            ['name' => 'Hà Thảo My', 'gender' => 'female', 'email' => 'thaomy.ha@clubstore.vn', 'phone' => '0944888999'],
            ['name' => 'Lâm Gia Huy', 'gender' => 'male', 'email' => 'giahuy.lam@clubstore.vn', 'phone' => '0944999000'],
            ['name' => 'Đặng Kim Ngân', 'gender' => 'female', 'email' => 'kimngan.dang@clubstore.vn', 'phone' => '0945111222'],
            ['name' => 'Cao Thành Trung', 'gender' => 'male', 'email' => 'thanhtrung.cao@clubstore.vn', 'phone' => '0945222333'],
            ['name' => 'Châu Ngọc Diệp', 'gender' => 'female', 'email' => 'ngocdiep.chau@clubstore.vn', 'phone' => '0945333444'],
            ['name' => 'Tô Vĩnh Quang', 'gender' => 'male', 'email' => 'vinhquang.to@clubstore.vn', 'phone' => '0945444555'],
            ['name' => 'Nghiêm Quỳnh Như', 'gender' => 'female', 'email' => 'quynhnhu.nghiem@clubstore.vn', 'phone' => '0945555666'],
        ];

        foreach ($additionalNames as $idx => $extra) {
            $customerDefinitions[] = [
                'email' => $extra['email'],
                'name' => $extra['name'],
                'phone' => $extra['phone'],
                'gender' => $extra['gender'],
                'birthday' => '199' . ($idx % 10) . '-0' . (($idx % 9) + 1) . '-15',
                'bio' => 'Khách hàng thân thiết của Nova Club Store tại TP. Hồ Chí Minh.',
                'tier' => 'regular',
                'points' => 150 + ($idx * 50),
                'lifetime_points' => 400 + ($idx * 120),
                'addresses' => [
                    [
                        'type' => AddressType::Shipping,
                        'first_name' => explode(' ', $extra['name'])[count(explode(' ', $extra['name'])) - 1],
                        'last_name' => explode(' ', $extra['name'])[0],
                        'phone' => $extra['phone'],
                        'email' => $extra['email'],
                        'country_code' => 'VN',
                        'state_id' => 'Thành phố Hồ Chí Minh',
                        'city_id' => 'Quận 1',
                        'ward_id' => 'Phường Bến Thành',
                        'address_detail' => (50 + $idx * 10) . ' Lê Thánh Tôn',
                        'postal_code' => '70000',
                    ],
                ],
            ];
        }

        // Shared bcrypt password hash for 'password'
        $defaultPasswordHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

        $totalCustomers = 0;
        foreach ($customerDefinitions as $cData) {
            $user = User::updateOrCreate(
                ['email' => $cData['email']],
                [
                    'name' => $cData['name'],
                    'password' => $defaultPasswordHash,
                    'phone' => $cData['phone'],
                    'gender' => $cData['gender'],
                    'birthday' => $cData['birthday'],
                    'bio' => $cData['bio'],
                    'email_verified_at' => now()->subDays(rand(10, 180)),
                ]
            );

            // Assign registered member role
            $user->assignRole($role);

            // Create Addresses
            $firstAddressId = null;
            $secondAddressId = null;
            foreach ($cData['addresses'] as $addrIdx => $addrData) {
                $address = Address::create(array_merge($addrData, [
                    'is_default' => $addrIdx === 0,
                ]));

                // Attach via morphToMany
                $user->addresses()->syncWithoutDetaching([$address->id]);

                if ($addrIdx === 0) {
                    $firstAddressId = $address->id;
                } else {
                    $secondAddressId = $address->id;
                }
            }

            // Set default addresses on User record
            if ($firstAddressId) {
                $user->update([
                    'default_shipping_address_id' => $firstAddressId,
                    'default_billing_address_id' => $secondAddressId ?? $firstAddressId,
                ]);
            }

            // Loyalty Points
            $points = $cData['points'] ?? 0;
            $lifetime = $cData['lifetime_points'] ?? $points;
            $loyaltyPoint = LoyaltyPoint::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'current_points' => $points,
                    'lifetime_points' => $lifetime,
                ]
            );

            if ($points > 0) {
                LoyaltyLog::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'action_type' => 'earn',
                    ],
                    [
                        'points_changed' => $points,
                        'order_id' => null,
                        'created_at' => now()->subDays(rand(5, 60)),
                    ]
                );
            }

            $totalCustomers++;
        }

        $this->command?->info("CustomerSeeder completed: {$totalCustomers} customers seeded with addresses and loyalty data.");
    }
}
