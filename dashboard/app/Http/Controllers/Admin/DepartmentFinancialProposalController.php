<?php

namespace App\Http\Controllers\Admin;

use App\Ecommerce\Workspace\Services\CfoService;
use App\Enums\FinancialProposalStatus;
use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\Traits\HasAdminSelectOptions;
use App\Http\Controllers\Admin\Traits\HasPermissionedServiceCrudActions;
use App\Models\DepartmentFinancialProposal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DepartmentFinancialProposalController extends BaseCrudController
{
    use HasAdminSelectOptions;
    use HasPermissionedServiceCrudActions;

    public function __construct(private readonly CfoService $cfoService)
    {
        parent::__construct();
    }

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
                'options' => $this->departmentOptions(),
            ],
            'user_id' => [
                'label' => 'Người đề xuất',
                'type' => 'select',
                'rules' => ['required', 'exists:users,id'],
                'options' => $this->userOptions(),
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
                'options' => FinancialProposalStatus::options(),
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

    protected function crudService(): object
    {
        return $this->cfoService;
    }

    protected function createServiceMethod(): string
    {
        return 'createProposal';
    }

    protected function updateServiceMethod(): string
    {
        return 'updateProposal';
    }

    protected function deleteServiceMethod(): string
    {
        return 'deleteProposal';
    }

    public function quickStatus(Request $request, int $id): RedirectResponse
    {
        $status = $request->input('status');
        if (! in_array($status, ['approved', 'rejected'], true)) {
            return back()->with('error', 'Trạng thái không hợp lệ.');
        }

        $proposal = $this->cfoService->updateProposalStatus($id, $status);

        $actionText = $status === 'approved' ? 'được phê duyệt thành công (1-Click CEO Approval)' : 'bị từ chối';

        return back()->with('status', "Đề xuất #{$proposal->id} \"{$proposal->title}\" đã {$actionText}.");
    }
}
