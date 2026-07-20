<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\DepartmentIncident;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentIncidentController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return DepartmentIncident::class;
    }

    protected function title(): string
    {
        return 'Sự cố vận hành';
    }

    protected function routePrefix(): string
    {
        return 'admin.incidents';
    }

    protected function searchable(): array
    {
        return ['type', 'description'];
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
            'order_id' => [
                'label' => 'ID Đơn hàng',
                'type' => 'number',
                'rules' => ['required', 'exists:shop_orders,id'],
            ],
            'assignee_id' => [
                'label' => 'Người xử lý',
                'type' => 'select',
                'rules' => ['nullable', 'exists:users,id'],
                'options' => ['' => '-- Trống --'] + User::orderBy('name')->pluck('name', 'id')->toArray(),
            ],
            'type' => [
                'label' => 'Loại sự cố',
                'type' => 'select',
                'rules' => ['required', 'string', 'max:255'],
                'options' => __('workspace.ops.incident_types'),
            ],
            'description' => [
                'label' => 'Mô tả chi tiết',
                'type' => 'textarea',
                'rules' => ['required', 'string'],
            ],
            'status' => [
                'label' => 'Trạng thái',
                'type' => 'select',
                'rules' => ['required', 'in:open,in_progress,resolved'],
                'options' => [
                    'open' => 'Mới (Open)',
                    'in_progress' => 'Đang xử lý',
                    'resolved' => 'Đã giải quyết',
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
        
        $opsService = app(\App\Ecommerce\Workspace\Services\OpsService::class);
        $opsService->createIncident($validated);

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Tạo mới thành công.');
    }

    public function update(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        if (!$this->hasPermission('update')) {
            abort(403);
        }

        $validated = $this->validateRequest($request);
        
        $opsService = app(\App\Ecommerce\Workspace\Services\OpsService::class);
        $opsService->updateIncident($id, $validated);

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Cập nhật thành công.');
    }

    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        if (!$this->hasPermission('delete')) {
            abort(403);
        }

        $opsService = app(\App\Ecommerce\Workspace\Services\OpsService::class);
        $opsService->deleteIncident($id);

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Xóa thành công.');
    }
}
