<?php

namespace App\Http\Controllers\Admin;

use App\Ecommerce\Workspace\Services\LogisticsService;
use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\Traits\HasAdminSelectOptions;
use App\Http\Controllers\Admin\Traits\HasPermissionedServiceCrudActions;
use App\Models\DepartmentPurchaseOrder;

class DepartmentPurchaseOrderController extends BaseCrudController
{
    use HasAdminSelectOptions;
    use HasPermissionedServiceCrudActions;

    public function __construct(private readonly LogisticsService $logisticsService)
    {
        parent::__construct();
    }

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
                'options' => $this->departmentOptions(),
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
                'rules' => ['required', 'in:pending,shipping,partial,completed,defective_return'],
                'options' => PurchaseOrderStatus::options(),
            ],
        ];
    }

    protected function crudService(): object
    {
        return $this->logisticsService;
    }

    protected function createServiceMethod(): string
    {
        return 'createPO';
    }

    protected function updateServiceMethod(): string
    {
        return 'updatePO';
    }

    protected function deleteServiceMethod(): string
    {
        return 'deletePO';
    }
}
