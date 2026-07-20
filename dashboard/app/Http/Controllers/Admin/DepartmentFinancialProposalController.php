<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\DepartmentFinancialProposal;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentFinancialProposalController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return DepartmentFinancialProposal::class;
    }

    protected function title(): string
    {
        return 'Đề xuất tài chính';
    }

    protected function routePrefix(): string
    {
        return 'admin.financial-proposals';
    }

    protected function searchable(): array
    {
        return ['title', 'reason'];
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
                'label' => 'Người đề xuất',
                'type' => 'select',
                'rules' => ['required', 'exists:users,id'],
                'options' => User::orderBy('name')->pluck('name', 'id')->toArray(),
            ],
            'title' => [
                'label' => 'Tiêu đề',
                'rules' => ['required', 'string', 'max:255'],
            ],
            'amount' => [
                'label' => 'Số tiền',
                'type' => 'number',
                'rules' => ['required', 'numeric', 'min:0'],
            ],
            'reason' => [
                'label' => 'Lý do',
                'type' => 'textarea',
                'rules' => ['required', 'string'],
            ],
            'status' => [
                'label' => 'Trạng thái',
                'type' => 'select',
                'rules' => ['required', 'in:pending,approved,rejected'],
                'options' => [
                    'pending' => 'Chờ duyệt',
                    'approved' => 'Đã duyệt',
                    'rejected' => 'Từ chối',
                ],
            ],
            'is_urgent' => [
                'label' => 'Đề xuất khẩn',
                'type' => 'select',
                'rules' => ['required', 'boolean'],
                'options' => [
                    '0' => 'Không',
                    '1' => 'Có',
                ],
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
        $cfoService->createProposal($validated);

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Tạo mới thành công.');
    }

    public function update(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        if (!$this->hasPermission('update')) {
            abort(403);
        }

        $validated = $this->validateRequest($request);
        
        $cfoService = app(\App\Ecommerce\Workspace\Services\CfoService::class);
        $cfoService->updateProposal($id, $validated);

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Cập nhật thành công.');
    }

    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        if (!$this->hasPermission('delete')) {
            abort(403);
        }

        $cfoService = app(\App\Ecommerce\Workspace\Services\CfoService::class);
        $cfoService->deleteProposal($id);

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Xóa thành công.');
    }
}
