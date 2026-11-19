<?php

namespace App\Http\Controllers\Admin;

use App\Ecommerce\Workspace\Services\OpsService;
use App\Enums\IncidentStatus;
use App\Enums\IncidentType;
use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\Traits\HasAdminSelectOptions;
use App\Http\Controllers\Admin\Traits\HasPermissionedServiceCrudActions;
use App\Models\DepartmentIncident;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DepartmentIncidentController extends BaseCrudController
{
    use HasAdminSelectOptions;
    use HasPermissionedServiceCrudActions;

    public function __construct(private readonly OpsService $opsService)
    {
        parent::__construct();
    }

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
                'options' => $this->departmentOptions(),
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
                'options' => ['' => '-- Trống --'] + $this->userOptions(),
            ],
            'type' => [
                'label' => 'Loại sự cố',
                'type' => 'select',
                'rules' => ['required', 'string', 'max:255'],
                'options' => IncidentType::options(),
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
                'options' => IncidentStatus::options(),
            ],
        ];
    }

    protected function crudService(): object
    {
        return $this->opsService;
    }

    protected function createServiceMethod(): string
    {
        return 'createIncident';
    }

    protected function updateServiceMethod(): string
    {
        return 'updateIncident';
    }

    protected function deleteServiceMethod(): string
    {
        return 'deleteIncident';
    }

    public function quickStatus(Request $request, int $id): RedirectResponse
    {
        $status = $request->input('status');
        if (! in_array($status, ['resolved', 'closed', 'investigating', 'open'], true)) {
            return back()->with('error', 'Trạng thái không hợp lệ.');
        }

        $incident = $this->opsService->updateIncidentStatus($id, $status);

        return back()->with('status', "Sự cố vận hành #{$incident->id} đã chuyển trạng thái sang [{$status}] thành công.");
    }
}
