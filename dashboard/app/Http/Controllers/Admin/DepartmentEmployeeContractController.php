<?php

namespace App\Http\Controllers\Admin;

use App\Ecommerce\Workspace\Services\HrService;
use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\Traits\HasAdminSelectOptions;
use App\Http\Controllers\Admin\Traits\HasPermissionedServiceCrudActions;
use App\Models\DepartmentEmployeeContract;

class DepartmentEmployeeContractController extends BaseCrudController
{
    use HasAdminSelectOptions;
    use HasPermissionedServiceCrudActions;

    public function __construct(private readonly HrService $hrService)
    {
        parent::__construct();
    }

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
                'options' => $this->departmentOptions(),
            ],
            'user_id' => [
                'label' => 'Nhân sự',
                'type' => 'select',
                'rules' => ['required', 'exists:users,id'],
                'options' => $this->userOptions(),
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

    protected function crudService(): object
    {
        return $this->hrService;
    }

    protected function createServiceMethod(): string
    {
        return 'createContract';
    }

    protected function updateServiceMethod(): string
    {
        return 'updateContract';
    }

    protected function deleteServiceMethod(): string
    {
        return 'deleteContract';
    }
}
