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
        
        $admin = User::first();
        if (!$admin) return;

        DepartmentFinancialProposal::truncate();
        DepartmentPayroll::truncate();
        DepartmentMaterialPrice::truncate();
        DepartmentPurchaseOrder::truncate();
        DepartmentIncident::truncate();
        DepartmentCustomerReview::truncate();
        DepartmentEmployeeContract::truncate();

        // --- CFO Data ---
        if ($cfo) {
            DepartmentFinancialProposal::create([
                'department_id' => $cfo->id,
                'user_id' => $admin->id,
                'title' => 'Mua phần mềm kế toán mới',
                'reason' => 'Phần mềm cũ đã lỗi thời',
                'amount' => 50000000,
                'status' => 'pending',
                'is_urgent' => true,
            ]);
            DepartmentFinancialProposal::create([
                'department_id' => $cfo->id,
                'user_id' => $admin->id,
                'title' => 'Chi phí teambuilding Q3',
                'reason' => 'Hoạt động nội bộ',
                'amount' => 20000000,
                'status' => 'approved',
                'is_urgent' => false,
            ]);

            DepartmentPayroll::create([
                'department_id' => $cfo->id,
                'user_id' => $admin->id,
                'month' => '2026-07',
                'base_salary' => 20000000,
                'allowance' => 2000000,
                'tax' => 1500000,
                'insurance' => 2100000,
                'net_salary' => 18400000,
            ]);

            DepartmentMaterialPrice::create(['department_id' => $cfo->id, 'material_name' => 'Thép HRC', 'price' => 15000, 'recorded_at' => Carbon::now()->subDays(5)]);
            DepartmentMaterialPrice::create(['department_id' => $cfo->id, 'material_name' => 'Nhựa PET', 'price' => 25000, 'recorded_at' => Carbon::now()->subDays(2)]);
        }

        // --- Logistics Data ---
        if ($logistics) {
            DepartmentPurchaseOrder::create([
                'department_id' => $logistics->id,
                'po_number' => 'PO-2026-001',
                'supplier_name' => 'Nhà cung cấp A',
                'status' => 'shipping',
                'total_amount' => 120000000,
                'expected_delivery_date' => Carbon::now()->addDays(3),
            ]);
            DepartmentPurchaseOrder::create([
                'department_id' => $logistics->id,
                'po_number' => 'PO-2026-002',
                'supplier_name' => 'Công ty Bao Bì B',
                'status' => 'partial',
                'total_amount' => 45000000,
                'expected_delivery_date' => Carbon::now()->subDays(1),
            ]);
        }

        // --- Ops Data ---
        if ($ops) {
            DepartmentIncident::create([
                'department_id' => $ops->id,
                'assignee_id' => $admin->id,
                'type' => 'late_delivery',
                'description' => 'Tài xế báo hỏng xe giữa đường',
                'status' => 'open',
            ]);
            DepartmentIncident::create([
                'department_id' => $ops->id,
                'assignee_id' => $admin->id,
                'type' => 'damaged_goods',
                'description' => 'Hàng vỡ do đóng gói kém',
                'status' => 'in_progress',
            ]);
        }

        // --- CSKH Data ---
        if ($cskh) {
            DepartmentCustomerReview::create([
                'department_id' => $cskh->id,
                'user_id' => $admin->id,
                'customer_name' => 'Nguyễn Văn Khách',
                'content' => 'Giao hàng quá chậm, nhân viên thái độ lồi lõm!',
                'rating' => 1,
                'sentiment' => 'negative',
            ]);
            DepartmentCustomerReview::create([
                'department_id' => $cskh->id,
                'customer_name' => 'Trần Thị Mua',
                'content' => 'Sản phẩm tuyệt vời, sẽ ủng hộ dài dài.',
                'rating' => 5,
                'sentiment' => 'positive',
            ]);
        }

        // --- HR Data ---
        if ($hr) {
            DepartmentEmployeeContract::create([
                'department_id' => $hr->id,
                'user_id' => $admin->id,
                'contract_code' => 'HD-001-2025',
                'start_date' => '2025-01-01',
                'end_date' => '2026-12-31',
                'position' => 'Giám đốc',
                'performance_score' => 95,
            ]);
        }
    }
}
