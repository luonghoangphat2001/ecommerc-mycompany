<?php

namespace Database\Seeders;

use App\Ecommerce\Department\Enums\RiskLevelThreshold;
use App\Models\Department;
use App\Ecommerce\Department\Services\DepartmentAgentService;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(DepartmentAgentService $agentService): void
    {
        $departments = [
            [
                'code' => 'rnd',
                'name' => 'Phòng R&D',
                'description' => 'Cấu hình sản phẩm, menu F&B mới, định lượng',
                'risk_level_threshold' => RiskLevelThreshold::LOW,
            ],
            [
                'code' => 'logistics',
                'name' => 'Phòng Logistics',
                'description' => 'Cảnh báo nguyên liệu, tồn kho, điều phối kho',
                'risk_level_threshold' => RiskLevelThreshold::MEDIUM,
            ],
            [
                'code' => 'cfo',
                'name' => 'Phòng Tài chính CFO',
                'description' => 'Chi thu, đổi giá sản phẩm, duyệt hoàn tiền',
                'risk_level_threshold' => RiskLevelThreshold::HIGH,
            ],
            [
                'code' => 'ops',
                'name' => 'Phòng Vận hành (Ops)',
                'description' => 'Vận hành đơn hàng, hủy đơn, chuyển trạng thái đơn',
                'risk_level_threshold' => RiskLevelThreshold::MEDIUM,
            ],
            [
                'code' => 'cskh',
                'name' => 'Phòng CSKH',
                'description' => 'Phản hồi khách hàng, ghi nhận khiếu nại',
                'risk_level_threshold' => RiskLevelThreshold::LOW,
            ],
            [
                'code' => 'hr',
                'name' => 'Phòng Nhân sự',
                'description' => 'Quản lý hợp đồng lao động, nhân sự',
                'risk_level_threshold' => RiskLevelThreshold::MEDIUM,
            ],
        ];

        foreach ($departments as $data) {
            $department = Department::updateOrCreate(
                ['code' => $data['code']],
                $data
            );

            // Create a default agent
            $agent = $department->agents()->firstOrCreate(
                ['agent_code' => 'agent-' . $data['code'] . '-01'],
                [
                    'name' => 'Default Agent ' . strtoupper($data['code']),
                    'status' => 'active',
                ]
            );

            // Generate tokens
            $agentService->revokeAndRegenerateTokens($agent);
        }
    }
}
