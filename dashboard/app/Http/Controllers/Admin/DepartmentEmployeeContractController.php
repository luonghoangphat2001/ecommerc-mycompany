<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\DepartmentEmployeeContract;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentEmployeeContractController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return DepartmentEmployeeContract::class;
    }

    protected function title(): string
    {
        return 'Hợp đồng & Nhân sự';
    }

    protected function routePrefix(): string
    {
        return 'admin.employee-contracts';
    }

    protected function searchable(): array
    {
        return ['contract_code', 'position'];
    }

    protected function fields(): array
    {
        return [
            'department_id' => [
                'label' => 'Phòng ban',
                'type' => 'select',
                'rules' => ['required', 'exists:departments,id'],
                'options' => \App\Models\Department::pluck('name', 'id')->toArray(),
            ],
            'user_id' => [
                'label' => 'Nhân sự',
                'type' => 'select',
                'rules' => ['required', 'exists:users,id'],
                'options' => User::orderBy('name')->pluck('name', 'id')->toArray(),
            ],
            'contract_code' => [
                'label' => 'Mã hợp đồng',
                'rules' => ['required', 'string', 'max:255', 'unique:department_employee_contracts,contract_code,' . request()->route('id')],
            ],
            'start_date' => [
                'label' => 'Ngày bắt đầu',
                'type' => 'date',
                'rules' => ['required', 'date'],
            ],
            'end_date' => [
                'label' => 'Ngày kết thúc (Tùy chọn)',
                'type' => 'date',
                'rules' => ['nullable', 'date', 'after_or_equal:start_date'],
            ],
            'position' => [
                'label' => 'Vị trí / Chức vụ',
                'rules' => ['required', 'string', 'max:255'],
            ],
            'performance_score' => [
                'label' => 'Điểm KPIs (0-100)',
                'type' => 'number',
                'rules' => ['nullable', 'integer', 'min:0', 'max:100'],
            ],
        ];
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        if (!$this->hasPermission('create')) {
            abort(403);
        }

        $validated = $this->validateRequest($request);
        
        $hrService = app(\App\Ecommerce\Workspace\Services\HrService::class);
        $hrService->createContract($validated);

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Tạo mới thành công.');
    }

    public function update(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        if (!$this->hasPermission('update')) {
            abort(403);
        }

        $validated = $this->validateRequest($request);
        
        $hrService = app(\App\Ecommerce\Workspace\Services\HrService::class);
        $hrService->updateContract($id, $validated);

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Cập nhật thành công.');
    }

    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        if (!$this->hasPermission('delete')) {
            abort(403);
        }

        $hrService = app(\App\Ecommerce\Workspace\Services\HrService::class);
        $hrService->deleteContract($id);

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Xóa thành công.');
    }
}
