<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\DepartmentPurchaseOrder;
use Illuminate\Http\Request;

class DepartmentPurchaseOrderController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return DepartmentPurchaseOrder::class;
    }

    protected function title(): string
    {
        return 'Đơn đặt hàng (PO)';
    }

    protected function routePrefix(): string
    {
        return 'admin.purchase-orders';
    }

    protected function searchable(): array
    {
        return ['po_number', 'supplier_name'];
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
            'po_number' => [
                'label' => 'Mã PO',
                'rules' => ['required', 'string', 'max:255', 'unique:department_purchase_orders,po_number,' . request()->route('id')],
            ],
            'supplier_name' => [
                'label' => 'Nhà cung cấp',
                'rules' => ['required', 'string', 'max:255'],
            ],
            'total_amount' => [
                'label' => 'Tổng tiền',
                'type' => 'number',
                'rules' => ['required', 'numeric', 'min:0'],
            ],
            'expected_delivery_date' => [
                'label' => 'Ngày dự kiến giao',
                'type' => 'date',
                'rules' => ['nullable', 'date'],
            ],
            'status' => [
                'label' => 'Trạng thái',
                'type' => 'select',
                'rules' => ['required', 'in:shipping,partial,completed,defective_return'],
                'options' => [
                    'shipping' => 'Đang giao',
                    'partial' => 'Giao 1 phần',
                    'completed' => 'Hoàn thành',
                    'defective_return' => 'Trả hàng lỗi',
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
        
        $logisticsService = app(\App\Ecommerce\Workspace\Services\LogisticsService::class);
        $logisticsService->createPO($validated);

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Tạo mới thành công.');
    }

    public function update(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        if (!$this->hasPermission('update')) {
            abort(403);
        }

        $validated = $this->validateRequest($request);
        
        $logisticsService = app(\App\Ecommerce\Workspace\Services\LogisticsService::class);
        $logisticsService->updatePO($id, $validated);

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Cập nhật thành công.');
    }

    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        if (!$this->hasPermission('delete')) {
            abort(403);
        }

        $logisticsService = app(\App\Ecommerce\Workspace\Services\LogisticsService::class);
        $logisticsService->deletePO($id);

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Xóa thành công.');
    }
}
