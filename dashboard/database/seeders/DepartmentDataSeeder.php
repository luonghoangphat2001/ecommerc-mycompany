<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\User;
use App\Models\DepartmentFinancialProposal;
use App\Models\DepartmentPayroll;
use App\Models\DepartmentMaterialPrice;
use App\Models\DepartmentPurchaseOrder;
use App\Models\DepartmentIncident;
use App\Models\DepartmentCustomerReview;
use App\Models\DepartmentEmployeeContract;
use Carbon\Carbon;

class DepartmentDataSeeder extends Seeder
{
    public function run(): void
    {
        $cfo = Department::where('code', 'cfo')->first();
        $logistics = Department::where('code', 'logistics')->first();
        $ops = Department::where('code', 'ops')->first();
        $cskh = Department::where('code', 'cskh')->first();
        $hr = Department::where('code', 'hr')->first();
        
        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }
        $admin = $users->first();

        DepartmentFinancialProposal::truncate();
        DepartmentPayroll::truncate();
        DepartmentMaterialPrice::truncate();
        DepartmentPurchaseOrder::truncate();
        DepartmentIncident::truncate();
        DepartmentCustomerReview::truncate();
        DepartmentEmployeeContract::truncate();

        // --- CFO Data ---
        if ($cfo) {
            $proposals = [
                [
                    'title' => 'Mua phần mềm kế toán mới',
                    'reason' => 'Phần mềm cũ đã lỗi thời, không đáp ứng chuẩn mực mới',
                    'amount' => 50000000,
                    'status' => 'pending',
                    'is_urgent' => true,
                ],
                [
                    'title' => 'Chi phí teambuilding Q3',
                    'reason' => 'Hoạt động gắn kết nội bộ cho toàn bộ nhân sự công ty',
                    'amount' => 20000000,
                    'status' => 'approved',
                    'is_urgent' => false,
                ],
                [
                    'title' => 'Thay thế 3 laptop hỏng cho phòng R&D',
                    'reason' => 'Các máy hiện tại đã quá cũ, không compile được code',
                    'amount' => 45000000,
                    'status' => 'pending',
                    'is_urgent' => true,
                ],
                [
                    'title' => 'Chi phí chiến dịch tuyển dụng quý tới',
                    'reason' => 'Thuê ngoài đơn vị săn đầu người cho vị trí Trưởng phòng R&D',
                    'amount' => 15000000,
                    'status' => 'approved',
                    'is_urgent' => false,
                ],
                [
                    'title' => 'Mua máy chủ backup dữ liệu cục bộ',
                    'reason' => 'Đề xuất tăng cường bảo mật lưu trữ dự phòng vật lý',
                    'amount' => 120000000,
                    'status' => 'rejected',
                    'is_urgent' => false,
                ],
                [
                    'title' => 'Nâng cấp đường truyền Internet văn phòng',
                    'reason' => 'Mạng chập chờn gây ảnh hưởng đến tiến độ của các nhóm làm việc',
                    'amount' => 8000000,
                    'status' => 'approved',
                    'is_urgent' => false,
                ],
                [
                    'title' => 'Mua tủ lạnh mới cho khu vực pantry',
                    'reason' => 'Tủ lạnh cũ bị rò nước và không đủ làm mát',
                    'amount' => 1200000,
                    'status' => 'pending',
                    'is_urgent' => false,
                ],
                [
                    'title' => 'Khẩn cấp: Sửa chữa hệ thống điều hòa phòng Server',
                    'reason' => 'Nhiệt độ phòng máy chủ tăng cao, có nguy cơ gây hỏng thiết bị phần cứng',
                    'amount' => 25000000,
                    'status' => 'pending',
                    'is_urgent' => true,
                ],
            ];

            foreach ($proposals as $p) {
                // Distribute proposals across users
                $proposalUser = $users->random();
                DepartmentFinancialProposal::create(array_merge($p, [
                    'department_id' => $cfo->id,
                    'user_id' => $proposalUser->id,
                ]));
            }

            // Payrolls: Create payroll entries for all users for 2 months (June & July 2026)
            $months = ['2026-06', '2026-07'];
            foreach ($months as $month) {
                foreach ($users as $idx => $user) {
                    $base = 15000000 + ($idx * 1500000);
                    $allowance = 1000000 + ($idx * 200000);
                    $tax = (int) ($base * 0.05);
                    $insurance = (int) ($base * 0.105);
                    $net = $base + $allowance - $tax - $insurance;

                    DepartmentPayroll::create([
                        'department_id' => $cfo->id,
                        'user_id' => $user->id,
                        'month' => $month,
                        'base_salary' => $base,
                        'allowance' => $allowance,
                        'tax' => $tax,
                        'insurance' => $insurance,
                        'net_salary' => $net,
                    ]);
                }
            }

            // Material Prices
            $materials = [
                ['material_name' => 'Thép HRC', 'price' => 15000, 'recorded_at' => Carbon::now()->subDays(5)],
                ['material_name' => 'Nhựa PET', 'price' => 25000, 'recorded_at' => Carbon::now()->subDays(2)],
                ['material_name' => 'Cát xây dựng', 'price' => 350000, 'recorded_at' => Carbon::now()->subDays(4)],
                ['material_name' => 'Xi măng PCB40', 'price' => 85000, 'recorded_at' => Carbon::now()->subDays(1)],
                ['material_name' => 'Kính cường lực 10mm', 'price' => 450000, 'recorded_at' => Carbon::now()->subDays(3)],
                ['material_name' => 'Sơn tường Dulux', 'price' => 1200000, 'recorded_at' => Carbon::now()->subDays(7)],
                ['material_name' => 'Gạch men Prime', 'price' => 180000, 'recorded_at' => Carbon::now()->subDays(6)],
                ['material_name' => 'Thép tròn phi 10', 'price' => 16500, 'recorded_at' => Carbon::now()->subDays(10)],
                ['material_name' => 'Nhôm Profile Xingfa', 'price' => 95000, 'recorded_at' => Carbon::now()->subDays(8)],
                ['material_name' => 'Dây cáp điện Cadivi', 'price' => 32000, 'recorded_at' => Carbon::now()->subDays(12)],
            ];

            foreach ($materials as $m) {
                DepartmentMaterialPrice::create(array_merge($m, ['department_id' => $cfo->id]));
            }
        }

        // --- Logistics Data ---
        if ($logistics) {
            $pos = [
                [
                    'po_number' => 'PO-2026-001',
                    'supplier_name' => 'Nhà cung cấp Thép Việt Đức',
                    'status' => 'shipping',
                    'total_amount' => 120000000,
                    'expected_delivery_date' => Carbon::now()->addDays(3),
                ],
                [
                    'po_number' => 'PO-2026-002',
                    'supplier_name' => 'Công ty Cổ phần Bao Bì Hà Nội',
                    'status' => 'partial',
                    'total_amount' => 45000000,
                    'expected_delivery_date' => Carbon::now()->subDays(1),
                ],
                [
                    'po_number' => 'PO-2026-003',
                    'supplier_name' => 'Tổng công ty Hóa chất Đức Giang',
                    'status' => 'completed',
                    'total_amount' => 310000000,
                    'expected_delivery_date' => Carbon::now()->subDays(5),
                ],
                [
                    'po_number' => 'PO-2026-004',
                    'supplier_name' => 'Nhà máy Gang thép Thái Nguyên',
                    'status' => 'shipping',
                    'total_amount' => 150000000,
                    'expected_delivery_date' => Carbon::now()->addDays(10),
                ],
                [
                    'po_number' => 'PO-2026-005',
                    'supplier_name' => 'Công ty Cơ khí Hoàng Minh',
                    'status' => 'completed',
                    'total_amount' => 85000000,
                    'expected_delivery_date' => Carbon::now()->subDays(12),
                ],
                [
                    'po_number' => 'PO-2026-006',
                    'supplier_name' => 'Công ty Nhựa Tiền Phong',
                    'status' => 'shipping',
                    'total_amount' => 60000000,
                    'expected_delivery_date' => Carbon::now()->addDays(2),
                ],
                [
                    'po_number' => 'PO-2026-007',
                    'supplier_name' => 'Vật liệu xây dựng An Phát',
                    'status' => 'defective_return',
                    'total_amount' => 22000000,
                    'expected_delivery_date' => Carbon::now()->subDays(2),
                ],
                [
                    'po_number' => 'PO-2026-008',
                    'supplier_name' => 'Thiết bị Điện Đông Á',
                    'status' => 'completed',
                    'total_amount' => 115000000,
                    'expected_delivery_date' => Carbon::now()->subDays(15),
                ],
                [
                    'po_number' => 'PO-2026-009',
                    'supplier_name' => 'Công ty Cung ứng Nguyên liệu Toàn Cầu',
                    'status' => 'shipping',
                    'total_amount' => 74000000,
                    'expected_delivery_date' => Carbon::now()->addDays(5),
                ],
                [
                    'po_number' => 'PO-2026-010',
                    'supplier_name' => 'Tổng kho Gỗ Thành Lâm',
                    'status' => 'completed',
                    'total_amount' => 195000000,
                    'expected_delivery_date' => Carbon::now()->subDays(20),
                ],
            ];

            foreach ($pos as $po) {
                DepartmentPurchaseOrder::create(array_merge($po, ['department_id' => $logistics->id]));
            }
        }

        // --- Ops Data ---
        if ($ops) {
            $incidents = [
                [
                    'type' => 'late_delivery',
                    'description' => 'Tài xế báo hỏng xe giữa đường trên Quốc lộ 1A, đơn hàng số #8928 bị trễ 4 tiếng',
                    'status' => 'open',
                ],
                [
                    'type' => 'damaged_goods',
                    'description' => 'Hàng vỡ do đóng gói kém và rung lắc mạnh trong quá trình vận chuyển liên tỉnh',
                    'status' => 'in_progress',
                ],
                [
                    'type' => 'system_error',
                    'description' => 'API kết nối với đơn vị vận chuyển ViettelPost bị gián đoạn, không đồng bộ được mã vận đơn',
                    'status' => 'resolved',
                ],
                [
                    'type' => 'return_request',
                    'description' => 'Khách hàng yêu cầu hoàn trả do giao sai phân loại màu sắc và kích cỡ sản phẩm',
                    'status' => 'open',
                ],
                [
                    'type' => 'late_delivery',
                    'description' => 'Giao hàng trễ hẹn cho đối tác VIP do kẹt xe nghiêm trọng giờ cao điểm tại trung tâm',
                    'status' => 'resolved',
                ],
                [
                    'type' => 'damaged_goods',
                    'description' => 'Bao bì sản phẩm bị rách nát, ẩm ướt nghiêm trọng do trời mưa to tầm tã lúc bốc dỡ',
                    'status' => 'open',
                ],
                [
                    'type' => 'system_error',
                    'description' => 'Lỗi đồng bộ tồn kho thời gian thực trên sàn thương mại điện tử Shopee gây ra tình trạng over-selling',
                    'status' => 'in_progress',
                ],
                [
                    'type' => 'return_request',
                    'description' => 'Khách hàng trả hàng hoàn tiền do sản phẩm bị lỗi kỹ thuật không lên nguồn',
                    'status' => 'resolved',
                ],
                [
                    'type' => 'late_delivery',
                    'description' => 'Bưu tá nghỉ ốm đột xuất, không có người thay thế kịp thời trong khu vực Quận 3',
                    'status' => 'open',
                ],
                [
                    'type' => 'damaged_goods',
                    'description' => 'Hao hụt và thất thoát 2 kiện hàng ký gửi khi kiểm đếm tại kho tổng',
                    'status' => 'in_progress',
                ],
            ];

            foreach ($incidents as $inc) {
                // Distribute assignee among users
                $assignee = $users->random();
                DepartmentIncident::create(array_merge($inc, [
                    'department_id' => $ops->id,
                    'assignee_id' => $assignee->id,
                ]));
            }
        }

        // --- CSKH Data ---
        if ($cskh) {
            $reviews = [
                [
                    'customer_name' => 'Nguyễn Văn Khách',
                    'content' => 'Giao hàng quá chậm, nhân viên tư vấn thái độ lồi lõm không chịu hỗ trợ!',
                    'rating' => 1,
                    'sentiment' => 'negative',
                ],
                [
                    'customer_name' => 'Trần Thị Mua',
                    'content' => 'Sản phẩm tuyệt vời, đóng gói siêu cẩn thận, sẽ ủng hộ dài dài.',
                    'rating' => 5,
                    'sentiment' => 'positive',
                ],
                [
                    'customer_name' => 'Lê Hoàng Minh',
                    'content' => 'Chất lượng tạm ổn so với tầm giá, tuy nhiên khâu giao hàng cần cải thiện nhanh hơn.',
                    'rating' => 3,
                    'sentiment' => 'neutral',
                ],
                [
                    'customer_name' => 'Phạm Thanh Thủy',
                    'content' => 'Đóng gói cực kỳ đẹp mắt, sản phẩm đúng như hình ảnh quảng cáo. Rất hài lòng.',
                    'rating' => 5,
                    'sentiment' => 'positive',
                ],
                [
                    'customer_name' => 'Nguyễn Thị Bé',
                    'content' => 'Sản phẩm bị trầy xước nhẹ ở góc vỏ hộp, shop cần bọc chống sốc kỹ hơn.',
                    'rating' => 3,
                    'sentiment' => 'neutral',
                ],
                [
                    'customer_name' => 'Đặng Văn Lâm',
                    'content' => 'Nhân viên trực chat hỗ trợ rất nhiệt tình, giải quyết thắc mắc nhanh chóng.',
                    'rating' => 5,
                    'sentiment' => 'positive',
                ],
                [
                    'customer_name' => 'Vũ Anh Tuấn',
                    'content' => 'Giao thiếu phụ kiện cáp sạc đi kèm, nhắn tin từ sáng tới tối chưa thấy ai trả lời.',
                    'rating' => 2,
                    'sentiment' => 'negative',
                ],
                [
                    'customer_name' => 'Hoàng Thu Trang',
                    'content' => 'Quá thất vọng về chất lượng dịch vụ, đổi trả phiền hà và kéo dài cả tuần trời.',
                    'rating' => 1,
                    'sentiment' => 'negative',
                ],
                [
                    'customer_name' => 'Ngô Quốc Anh',
                    'content' => 'Giao hàng siêu tốc chỉ trong 2 tiếng, anh shipper thân thiện vui tính.',
                    'rating' => 5,
                    'sentiment' => 'positive',
                ],
                [
                    'customer_name' => 'Bùi Hồng Hạnh',
                    'content' => 'Sản phẩm dùng bình thường, không quá xuất sắc nhưng chấp nhận được.',
                    'rating' => 3,
                    'sentiment' => 'neutral',
                ],
                [
                    'customer_name' => 'Đỗ Minh Trí',
                    'content' => 'Sản phẩm lỗi kết nối Bluetooth liên tục, liên hệ bảo hành chưa được.',
                    'rating' => 2,
                    'sentiment' => 'negative',
                ],
                [
                    'customer_name' => 'Phan Thị Mai',
                    'content' => 'Mua làm quà tặng cho mẹ, mẹ rất thích. Cảm ơn shop nhiều!',
                    'rating' => 5,
                    'sentiment' => 'positive',
                ],
            ];

            foreach ($reviews as $rev) {
                // Randomly assign reply_content or user_id for handled ones
                $isHandled = rand(0, 1);
                $reviewData = array_merge($rev, [
                    'department_id' => $cskh->id,
                ]);

                if ($isHandled) {
                    $handler = $users->random();
                    $reviewData['user_id'] = $handler->id;
                    $reviewData['reply_content'] = 'Cảm ơn ý kiến đóng góp của bạn. Chúng tôi xin ghi nhận và khắc phục ngay.';
                }

                DepartmentCustomerReview::create($reviewData);
            }
        }

        // --- HR Data ---
        if ($hr) {
            $positions = [
                ['position' => 'Giám đốc Điều hành (CEO)', 'code' => 'HD-CEO-001', 'score' => 95],
                ['position' => 'Trưởng phòng Tài chính (CFO)', 'code' => 'HD-CFO-002', 'score' => 88],
                ['position' => 'Trưởng phòng Logistics', 'code' => 'HD-LOG-003', 'score' => 91],
                ['position' => 'Chuyên viên Vận hành Hệ thống', 'code' => 'HD-OPS-004', 'score' => 85],
                ['position' => 'Trưởng nhóm CSKH', 'code' => 'HD-CSKH-005', 'score' => 82],
                ['position' => 'Kỹ sư Phần mềm R&D', 'code' => 'HD-RND-006', 'score' => 94],
                ['position' => 'Kỹ sư Cầu nối R&D', 'code' => 'HD-RND-007', 'score' => 89],
                ['position' => 'Kế toán trưởng', 'code' => 'HD-ACC-008', 'score' => 90],
                ['position' => 'Nhân viên Giám sát Kho', 'code' => 'HD-LOG-009', 'score' => 79],
                ['position' => 'Nhân viên CSKH Ca Đêm', 'code' => 'HD-CSKH-010', 'score' => 77],
                ['position' => 'Chuyên viên Nhân sự', 'code' => 'HD-HR-011', 'score' => 86],
                ['position' => 'Chuyên viên Pháp chế', 'code' => 'HD-LAW-012', 'score' => 83],
            ];

            foreach ($positions as $idx => $p) {
                // Map to users loop safely
                $user = $users[$idx % $users->count()];
                
                DepartmentEmployeeContract::create([
                    'department_id' => $hr->id,
                    'user_id' => $user->id,
                    'contract_code' => $p['code'],
                    'start_date' => Carbon::now()->subYears(1)->subMonths(rand(1, 5))->toDateString(),
                    'end_date' => Carbon::now()->addYear()->toDateString(),
                    'position' => $p['position'],
                    'performance_score' => $p['score'],
                ]);
            }
        }
    }
}
