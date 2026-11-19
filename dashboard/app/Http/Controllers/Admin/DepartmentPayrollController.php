<?php

namespace App\Http\Controllers\Admin;

use App\Ecommerce\Workspace\Services\CfoService;
use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\Traits\HasAdminSelectOptions;
use App\Http\Controllers\Admin\Traits\HasPermissionedServiceCrudActions;
use App\Models\DepartmentPayroll;

class DepartmentPayrollController extends BaseCrudController
{
    use HasAdminSelectOptions;
    use HasPermissionedServiceCrudActions;

    public function __construct(private readonly CfoService $cfoService)
    {
        parent::__construct();
    }

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
                'options' => $this->departmentOptions(),
            ],
            'user_id' => [
                'label' => 'Nhân sự',
                'type' => 'select',
                'rules' => ['required', 'exists:users,id'],
                'options' => $this->userOptions(),
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

    protected function crudService(): object
    {
        return $this->cfoService;
    }

    protected function createServiceMethod(): string
    {
        return 'createPayroll';
    }

    protected function updateServiceMethod(): string
    {
        return 'updatePayroll';
    }

    protected function deleteServiceMethod(): string
    {
        return 'deletePayroll';
    }
}
