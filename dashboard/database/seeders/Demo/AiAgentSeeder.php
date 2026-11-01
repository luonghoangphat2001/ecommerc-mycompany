<?php

namespace Database\Seeders\Demo;

use App\Ecommerce\Department\Enums\AgentStatus;
use App\Ecommerce\Department\Enums\RiskLevelThreshold;
use App\Ecommerce\Department\Services\DepartmentAgentService;
use App\Enums\WebhookLogStatus;
use App\Models\Coupon;
use App\Models\Department;
use App\Models\DepartmentAgent;
use App\Models\DepartmentAuditLog;
use App\Models\DepartmentCustomerReview;
use App\Models\DepartmentEmployeeContract;
use App\Models\DepartmentFinancialProposal;
use App\Models\DepartmentIncident;
use App\Models\DepartmentMaterialPrice;
use App\Models\DepartmentPayroll;
use App\Models\DepartmentPurchaseOrder;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AiAgentSeeder extends Seeder
{
    /**
     * Run the AI Agent and department ecosystem demo seeder.
     */
    public function run(DepartmentAgentService $agentService): void
    {
        $users = User::all();
        $admin = User::where('email', 'admin@admin.com')->first() ?? $users->first();
        $orders = Order::latest('id')->take(20)->get();
        $coupons = Coupon::all();

        // 1. Seed Departments & AI Agents
        $departments = $this->seedDepartments($agentService);

        // 2. CFO Department Data
        $this->seedCfoData($departments['cfo'], $users);

        // 3. Logistics Department Data
        $this->seedLogisticsData($departments['logistics']);

        // 4. Ops Department Data
        $this->seedOpsData($departments['ops'], $orders, $users);

        // 5. CSKH Department Data
        $this->seedCskhData($departments['cskh'], $users, $coupons);

        // 6. HR Department Data
        $this->seedHrData($departments['hr'], $users);

        // 7. Seed AI Agent Audit Logs (Interaction History)
        $this->seedAuditLogs($departments);

        $this->command?->info("AiAgentSeeder completed: 6 AI Agent Departments, active agents, operational workflows, and audit logs synchronized.");
    }

    protected function seedDepartments(DepartmentAgentService $agentService): array
    {
        $deptConfigs = [
            'rnd' => [
                'name' => 'Phòng R&D và Pha chế',
                'description' => 'Nghiên cứu công thức cocktail mới, tiêu chuẩn định lượng quầy bar và tối ưu thực đơn F&B.',
                'risk_level_threshold' => RiskLevelThreshold::LOW,
                'agent_code' => 'agent-rnd-01',
                'agent_name' => 'AI Mixologist & Menu Architect',
            ],
            'logistics' => [
                'name' => 'Phòng Logistics & Điều phối Kho',
                'description' => 'Giám sát mức tồn kho 3 kho hàng, cảnh báo nguyên liệu sắp hết và quản lý đơn đặt hàng nhà cung cấp.',
                'risk_level_threshold' => RiskLevelThreshold::MEDIUM,
                'agent_code' => 'agent-logistics-01',
                'agent_name' => 'AI Logistics & Inventory Dispatcher',
            ],
            'cfo' => [
                'name' => 'Phòng Tài chính CFO',
                'description' => 'Quản lý doanh thu, đề xuất chi ngân sách, kiểm soát bảng lương nhân viên và phê duyệt hoàn tiền.',
                'risk_level_threshold' => RiskLevelThreshold::HIGH,
                'agent_code' => 'agent-cfo-01',
                'agent_name' => 'AI Financial Analyst & Risk Controller',
            ],
            'ops' => [
                'name' => 'Phòng Vận hành & Sự kiện (Ops)',
                'description' => 'Điều phối đơn hàng giao nhanh, xử lý sự cố vận chuyển và điều hành bàn tiệc VIP.',
                'risk_level_threshold' => RiskLevelThreshold::MEDIUM,
                'agent_code' => 'agent-ops-01',
                'agent_name' => 'AI Operations Coordinator',
            ],
            'cskh' => [
                'name' => 'Phòng Chăm sóc Khách hàng (CSKH)',
                'description' => 'Phân tích phản hồi khách hàng, phân loại cảm xúc (sentiment), giải đáp thắc mắc và gửi coupon tri ân.',
                'risk_level_threshold' => RiskLevelThreshold::LOW,
                'agent_code' => 'agent-cskh-01',
                'agent_name' => 'AI Customer Sentiment Concierge',
            ],
            'hr' => [
                'name' => 'Phòng Nhân sự & Đào tạo',
                'description' => 'Quản lý hợp đồng lao động nhân viên pha chế, đánh giá KPI nhân sự và nhắc nhở gia hạn hợp đồng.',
                'risk_level_threshold' => RiskLevelThreshold::MEDIUM,
                'agent_code' => 'agent-hr-01',
                'agent_name' => 'AI HR & Talent Specialist',
            ],
        ];

        $deptModels = [];
        foreach ($deptConfigs as $code => $cfg) {
            $department = Department::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $cfg['name'],
                    'description' => $cfg['description'],
                    'is_active' => true,
                    'risk_level_threshold' => $cfg['risk_level_threshold'],
                ]
            );

            $agent = DepartmentAgent::updateOrCreate(
                ['agent_code' => $cfg['agent_code']],
                [
                    'department_id' => $department->id,
                    'name' => $cfg['agent_name'],
                    'status' => AgentStatus::ACTIVE,
                    'last_active_at' => now()->subMinutes(rand(1, 45)),
                ]
            );

            // Generate clean API and webhook tokens
            $agentService->revokeAndRegenerateTokens($agent);

            $deptModels[$code] = $department;
        }

        return $deptModels;
    }

    protected function seedCfoData(Department $cfo, $users): void
    {
        $userId = $users->first()?->id ?? 1;

        // 1. Financial Proposals
        $proposals = [
            [
                'title' => 'Đầu tư tủ trữ rượu chuyên dụng Vinocave 168 chai',
                'reason' => 'Đảm bảo điều kiện nhiệt độ 14°C và độ ẩm 70% lý tưởng cho các dòng vang Grand Cru đắt tiền.',
                'amount' => 48000000,
                'status' => 'pending',
                'is_urgent' => true,
            ],
            [
                'title' => 'Nâng cấp máy pha cà phê La Marzocco quầy bar',
                'reason' => 'Phục vụ các dòng cocktail Espresso Martini và thức uống tráng miệng cao cấp cho khách VIP.',
                'amount' => 125000000,
                'status' => 'approved',
                'is_urgent' => false,
            ],
            [
                'title' => 'Kinh phí chiến dịch Marketing Countdown 2027',
                'reason' => 'Tổ chức đêm nhạc hội EDM DJ quốc tế và tiệc buffet champagne đón giao thừa.',
                'amount' => 85000000,
                'status' => 'pending',
                'is_urgent' => false,
            ],
            [
                'title' => 'Bảo trì khẩn cấp hệ thống máy lạnh trung tâm tầng Lounge',
                'reason' => 'Nhiệt độ phòng tăng cao làm ảnh hưởng trải nghiệm khách hàng VIP tối cuối tuần.',
                'amount' => 18000000,
                'status' => 'pending',
                'is_urgent' => true,
            ],
            [
                'title' => 'Thay thế bộ ly pha lê bị vỡ hao hụt quý vừa qua',
                'reason' => 'Bổ sung 60 ly Burgundy và 60 ly Champagne coupe nhập khẩu từ Đức.',
                'amount' => 24000000,
                'status' => 'approved',
                'is_urgent' => false,
            ],
        ];

        foreach ($proposals as $p) {
            DepartmentFinancialProposal::updateOrCreate(
                ['title' => $p['title']],
                array_merge($p, [
                    'department_id' => $cfo->id,
                    'user_id' => $userId,
                ])
            );
        }

        // 2. Monthly Payrolls (Past 2 months)
        $months = ['2026-09', '2026-10'];
        foreach ($months as $month) {
            foreach ($users->take(6) as $idx => $u) {
                $base = 16000000 + ($idx * 2000000);
                $allowance = 1500000 + ($idx * 300000);
                $tax = (int) ($base * 0.05);
                $insurance = (int) ($base * 0.105);
                $net = $base + $allowance - $tax - $insurance;

                DepartmentPayroll::updateOrCreate(
                    ['department_id' => $cfo->id, 'user_id' => $u->id, 'month' => $month],
                    [
                        'base_salary' => $base,
                        'allowance' => $allowance,
                        'tax' => $tax,
                        'insurance' => $insurance,
                        'net_salary' => $net,
                    ]
                );
            }
        }

        // 3. Material Prices
        $materials = [
            ['material_name' => 'Rượu nền Rum trắng Bacardi (Chai)', 'price' => 280000],
            ['material_name' => 'Syrup Monin bạc hà & dâu tây (Chai 700ml)', 'price' => 220000],
            ['material_name' => 'Thịt bò Wagyu Ribeye A5 (Kg)', 'price' => 2400000],
            ['material_name' => 'Đá bi tròn pha lê quầy bar (Bao 20kg)', 'price' => 85000],
            ['material_name' => 'Hộp xốp giữ nhiệt + Túi gel đá khô F&B', 'price' => 35000],
        ];

        foreach ($materials as $m) {
            DepartmentMaterialPrice::updateOrCreate(
                ['material_name' => $m['material_name'], 'department_id' => $cfo->id],
                [
                    'price' => $m['price'],
                    'recorded_at' => Carbon::now()->subDays(rand(1, 10)),
                ]
            );
        }
    }

    protected function seedLogisticsData(Department $logistics): void
    {
        $pos = [
            [
                'po_number' => 'PO-CLUB-2026-01',
                'supplier_name' => 'Nhà máy Bia Heineken Việt Nam',
                'status' => 'shipping',
                'total_amount' => 145000000,
                'expected_delivery_date' => Carbon::now()->addDays(2),
            ],
            [
                'po_number' => 'PO-CLUB-2026-02',
                'supplier_name' => 'Tập đoàn Rượu Moët Hennessy',
                'status' => 'shipping',
                'total_amount' => 320000000,
                'expected_delivery_date' => Carbon::now()->addDays(5),
            ],
            [
                'po_number' => 'PO-CLUB-2026-03',
                'supplier_name' => 'Công ty Thực phẩm Classic Fine Foods',
                'status' => 'completed',
                'total_amount' => 86000000,
                'expected_delivery_date' => Carbon::now()->subDays(3),
            ],
            [
                'po_number' => 'PO-CLUB-2026-04',
                'supplier_name' => 'Xưởng Bia Thủ Công East West Brewing',
                'status' => 'partial',
                'total_amount' => 54000000,
                'expected_delivery_date' => Carbon::now()->addDays(1),
            ],
            [
                'po_number' => 'PO-CLUB-2026-05',
                'supplier_name' => 'Tổng kho Rượu vang Cellar Spirits',
                'status' => 'completed',
                'total_amount' => 210000000,
                'expected_delivery_date' => Carbon::now()->subDays(8),
            ],
        ];

        foreach ($pos as $po) {
            DepartmentPurchaseOrder::updateOrCreate(
                ['po_number' => $po['po_number']],
                array_merge($po, ['department_id' => $logistics->id])
            );
        }
    }

    protected function seedOpsData(Department $ops, $orders, $users): void
    {
        $userId = $users->first()?->id ?? 1;

        $incidents = [
            [
                'type' => 'late_delivery',
                'description' => 'Shipper gặp mưa ngập đường Nguyễn Hữu Cảnh, đơn giao rượu tiệc VIP #DEMO-ORD-00010 bị trễ 25 phút.',
                'status' => 'open',
            ],
            [
                'type' => 'damaged_goods',
                'description' => 'Rung lắc trên xe giao hàng làm rách tem niêm phong chai vang Bordeaux, đã kích hoạt quy trình đổi mới.',
                'status' => 'in_progress',
            ],
            [
                'type' => 'cold_storage_alarm',
                'description' => 'Cảm biến nhiệt độ kho lạnh Thủ Đức báo động tăng lên 8°C (ngưỡng chuẩn 2-4°C) do đóng cửa không chặt.',
                'status' => 'resolved',
            ],
            [
                'type' => 'system_error',
                'description' => 'Cổng thanh toán MoMo timeout trong 10 phút lúc 21:00 tối thứ Bảy, đơn hàng chuyển sang thanh toán COD thành công.',
                'status' => 'resolved',
            ],
        ];

        foreach ($incidents as $idx => $inc) {
            $linkedOrder = $orders->get($idx);
            DepartmentIncident::updateOrCreate(
                ['description' => $inc['description']],
                [
                    'department_id' => $ops->id,
                    'order_id' => $linkedOrder ? $linkedOrder->id : null,
                    'assignee_id' => $userId,
                    'type' => $inc['type'],
                    'status' => $inc['status'],
                ]
            );
        }
    }

    protected function seedCskhData(Department $cskh, $users, $coupons): void
    {
        $coupon = $coupons->first();

        $reviews = [
            [
                'customer_name' => 'Lê Phương Thảo (VIP Diamond)',
                'content' => 'Áo đấu chính thức Nova Club chất vải AeroReady cực kỳ thoáng mát, form chuẩn châu Á, đường may rất sắc sảo. Tuyệt vời!',
                'rating' => 5,
                'sentiment' => 'positive',
                'reply_content' => 'Nova Club chân thành cảm ơn chị Thảo đã luôn tin tưởng và đồng hành cùng câu lạc bộ của chúng tôi!',
            ],
            [
                'customer_name' => 'Trần Minh Tuấn',
                'content' => 'Bộ combo áo đấu và khăn choàng cổ CĐV Nova Club rất đẹp, đóng hộp sang trọng. Sẽ ủng hộ dài dài.',
                'rating' => 5,
                'sentiment' => 'positive',
                'reply_content' => 'Rất vui khi anh Tuấn có những phút giây cổ vũ cuồng nhiệt cùng đội bóng!',
            ],
            [
                'customer_name' => 'Vũ Hải Đăng',
                'content' => 'Sản phẩm đẹp nhưng shipper giao hàng hơi trễ 20 phút do trời mưa to giờ tan tầm.',
                'rating' => 3,
                'sentiment' => 'neutral',
                'reply_content' => 'Nova Club xin ghi nhận góp ý của anh Đăng và sẽ tối ưu lộ trình giao hàng giờ cao điểm chu đáo hơn.',
            ],
            [
                'customer_name' => 'Nguyễn Hoàng Long',
                'content' => 'Hộp áo bị móp góc bên ngoài thùng carton khi nhận, cần chú ý khâu bọc xốp bóng khí hơn.',
                'rating' => 2,
                'sentiment' => 'negative',
                'reply_content' => 'Nova Club vô cùng xin lỗi anh Long về trải nghiệm chưa trọn vẹn. Chúng tôi xin gửi tặng mã NOVACLUB20 giảm 20% cho lần đặt tới.',
            ],
            [
                'customer_name' => 'Hoàng Yến Nhi',
                'content' => 'Nhân viên hotline phản hồi rất nhanh và nhiệt tình khi tôi cần tư vấn vang trắng cho tiệc hải sản.',
                'rating' => 5,
                'sentiment' => 'positive',
                'reply_content' => 'Cảm ơn chị Nhi, chúc chị và gia đình có một bữa tiệc ấm cúng!',
            ],
        ];

        foreach ($reviews as $idx => $r) {
            $user = $users->get($idx);
            DepartmentCustomerReview::updateOrCreate(
                ['customer_name' => $r['customer_name']],
                [
                    'department_id' => $cskh->id,
                    'user_id' => $user ? $user->id : null,
                    'content' => $r['content'],
                    'rating' => $r['rating'],
                    'sentiment' => $r['sentiment'],
                    'coupon_id' => $r['rating'] <= 2 ? $coupon?->id : null,
                    'reply_content' => $r['reply_content'],
                ]
            );
        }
    }

    protected function seedHrData(Department $hr, $users): void
    {
        $contracts = [
            [
                'position' => 'Trưởng ban Huấn luyện & Dinh dưỡng Thể thao',
                'contract_code' => 'HDLD-NOVA-001',
                'performance_score' => 96,
                'start_date' => Carbon::now()->subMonths(18),
                'end_date' => Carbon::now()->addDays(20), // Expiring soon (<30 days) to trigger alert!
            ],
            [
                'position' => 'Chuyên gia Thiết kế Trang phục Thi đấu',
                'contract_code' => 'HDLD-NOVA-002',
                'performance_score' => 92,
                'start_date' => Carbon::now()->subMonths(12),
                'end_date' => Carbon::now()->addDays(25), // Expiring soon (<30 days) to trigger alert!
            ],
            [
                'position' => 'Chuyên viên Điều phối Thiết bị Thể thao',
                'contract_code' => 'HDLD-NOVA-003',
                'performance_score' => 94,
                'start_date' => Carbon::now()->subMonths(24),
                'end_date' => Carbon::now()->addMonths(12),
            ],
            [
                'position' => 'Giám sát Vận hành Sân Vận Động Nova Arena',
                'contract_code' => 'HDLD-NOVA-004',
                'performance_score' => 88,
                'start_date' => Carbon::now()->subMonths(6),
                'end_date' => Carbon::now()->addMonths(6),
            ],
            [
                'position' => 'Trưởng nhóm Quản lý Kho Dụng Cụ Thể Thao',
                'contract_code' => 'HDLD-NOVA-005',
                'performance_score' => 90,
                'start_date' => Carbon::now()->subMonths(10),
                'end_date' => Carbon::now()->addMonths(14),
            ],
        ];

        foreach ($contracts as $idx => $c) {
            $user = $users->get($idx);
            DepartmentEmployeeContract::updateOrCreate(
                ['contract_code' => $c['contract_code']],
                [
                    'department_id' => $hr->id,
                    'user_id' => $user ? $user->id : 1,
                    'position' => $c['position'],
                    'performance_score' => $c['performance_score'],
                    'start_date' => $c['start_date'],
                    'end_date' => $c['end_date'],
                ]
            );
        }
    }

    protected function seedAuditLogs(array $departments): void
    {
        $logs = [
            [
                'dept' => 'cskh',
                'action' => 'review_sentiment_analysis',
                'event' => 'cskh.review.analyzed',
                'payload' => ['customer' => 'Nguyễn Hoàng Long', 'rating' => 2, 'sentiment' => 'negative', 'action' => 'issue_apology_coupon'],
                'response' => ['status' => 'ok', 'coupon_issued' => 'VIPCLUB20', 'sms_dispatched' => true],
            ],
            [
                'dept' => 'logistics',
                'action' => 'inventory_low_stock_alert',
                'event' => 'logistics.stock.threshold_reached',
                'payload' => ['sku' => 'CLUB-SPIR-003', 'product' => 'Rượu Cognac Hennessy VSOP', 'stock_remaining' => 8, 'threshold' => 10],
                'response' => ['status' => 'notified', 'po_drafted' => 'PO-CLUB-2026-02'],
            ],
            [
                'dept' => 'ops',
                'action' => 'dispatch_delivery_incident',
                'event' => 'ops.order.incident_logged',
                'payload' => ['order' => 'DEMO-ORD-00010', 'type' => 'late_delivery', 'delay_minutes' => 25],
                'response' => ['status' => 'escalated', 'shipper_contacted' => true],
            ],
            [
                'dept' => 'cfo',
                'action' => 'financial_risk_audit',
                'event' => 'cfo.proposal.risk_evaluated',
                'payload' => ['proposal_id' => 1, 'amount' => 48000000, 'risk_score' => 'low'],
                'response' => ['status' => 'ready_for_approval'],
            ],
            [
                'dept' => 'rnd',
                'action' => 'seasonal_menu_spec_generation',
                'event' => 'rnd.menu.spec_updated',
                'payload' => ['jersey_model' => 'Nova Pro Home 2026', 'variants_count' => 5, 'margin_percent' => 68],
                'response' => ['status' => 'published'],
            ],
            [
                'dept' => 'hr',
                'action' => 'contract_renewal_notification',
                'event' => 'hr.contract.expiring_soon',
                'payload' => ['contract' => 'HDLD-NOVA-001', 'position' => 'Head Coach', 'days_left' => 20],
                'response' => ['status' => 'manager_alerted'],
            ],
        ];

        foreach ($logs as $log) {
            $dept = $departments[$log['dept']];
            $agent = $dept->agents()->first();

            DepartmentAuditLog::create([
                'department_id' => $dept->id,
                'department_agent_id' => $agent?->id,
                'event_id' => (string) Str::uuid(),
                'action' => $log['action'],
                'event' => $log['event'],
                'payload' => $log['payload'],
                'response' => $log['response'],
                'status' => WebhookLogStatus::Success,
                'duration' => rand(65, 320),
                'created_at' => now()->subHours(rand(1, 36)),
            ]);
        }
    }
}
