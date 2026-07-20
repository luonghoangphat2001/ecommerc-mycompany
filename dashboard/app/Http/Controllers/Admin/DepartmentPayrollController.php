<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\DepartmentPayroll;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentPayrollController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return DepartmentPayroll::class;
    }

    protected function title(): string
    {
        return 'Bảng lương';
    }

    protected function routePrefix(): string
    {
        return 'admin.payrolls';
    }

    protected function searchable(): array
    {
        return ['month'];
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
            'month' => [
                'label' => 'Tháng',
                'rules' => ['required', 'string', 'max:255'],
            ],
            'base_salary' => [
                'label' => 'Lương cơ bản',
                'type' => 'number',
                'rules' => ['required', 'numeric', 'min:0'],
            ],
            'allowance' => [
                'label' => 'Phụ cấp',
                'type' => 'number',
                'rules' => ['required', 'numeric', 'min:0'],
            ],
            'tax' => [
                'label' => 'Thuế',
                'type' => 'number',
                'rules' => ['required', 'numeric', 'min:0'],
            ],
            'insurance' => [
                'label' => 'Bảo hiểm',
                'type' => 'number',
                'rules' => ['required', 'numeric', 'min:0'],
            ],
            'net_salary' => [
                'label' => 'Thực lãnh',
                'type' => 'number',
                'rules' => ['required', 'numeric', 'min:0'],
            ],
        ];
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        if (!$this->hasPermission('create')) {
            abort(403);
        }

        $validated = $this->validateRequest($request);
        
        $cfoService = app(\App\Ecommerce\Workspace\Services\CfoService::class);
        $cfoService->createPayroll($validated);

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Tạo mới thành công.');
    }

    public function update(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        if (!$this->hasPermission('update')) {
            abort(403);
        }

        $validated = $this->validateRequest($request);
        
        $cfoService = app(\App\Ecommerce\Workspace\Services\CfoService::class);
        $cfoService->updatePayroll($id, $validated);

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Cập nhật thành công.');
    }

    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        if (!$this->hasPermission('delete')) {
            abort(403);
        }

        $cfoService = app(\App\Ecommerce\Workspace\Services\CfoService::class);
        $cfoService->deletePayroll($id);

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Xóa thành công.');
    }
}
