<?php

namespace App\Http\Controllers\Admin;

use App\Ecommerce\Department\Services\DepartmentAgentService;
use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Department;
use App\Models\DepartmentAgent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DepartmentAgentController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return DepartmentAgent::class;
    }

    protected function title(): string
    {
        return 'admin.resources.department-agents';
    }

    protected function routePrefix(): string
    {
        return 'admin.department-agents';
    }

    protected function searchable(): array
    {
        return ['agent_code', 'name'];
    }

    protected function indexQuery(Builder $query, Request $request): Builder
    {
        return $query->with('department');
    }

    protected function fields(): array
    {
        return [
            'department_id' => [
                'label' => 'Phòng ban',
                'type' => 'select',
                'rules' => ['required', 'exists:departments,id'],
                'options' => Department::orderBy('name')->pluck('name', 'id')->toArray(),
            ],
            'agent_code' => [
                'label' => 'Mã Agent',
                'rules' => ['required', 'string', 'max:255'],
            ],
            'name' => [
                'label' => 'Tên Agent',
                'rules' => ['required', 'string', 'max:255'],
            ],
            'status' => [
                'label' => 'Trạng thái',
                'type' => 'select',
                'rules' => ['required', 'in:active,inactive,suspended'],
                'options' => [
                    'active' => 'Hoạt động',
                    'inactive' => 'Ngừng hoạt động',
                    'suspended' => 'Tạm đình chỉ',
                ],
            ],
            'last_active_at' => [
                'label' => 'Hoạt động cuối',
                'hideOnForm' => true,
            ],
            'created_at' => [
                'label' => 'Ngày tạo',
                'hideOnForm' => true,
                'hideOnIndex' => true,
            ],
        ];
    }

    protected function rules(?int $id = null): array
    {
        return [
            'department_id' => ['required', 'exists:departments,id'],
            'agent_code' => ['required', 'string', 'max:255', Rule::unique('department_agents', 'agent_code')->ignore($id)],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive,suspended'],
        ];
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        $data = parent::mutateData($data, $record);

        if ($record === null && empty($data['api_token_hash'])) {
            $data['api_token_hash'] = Str::random(40);
            $data['webhook_secret'] = Str::random(60);
        }

        return $data;
    }

    public function storeForDepartment(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([
            'agent_code' => 'required|string|unique:department_agents,agent_code',
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $department->agents()->create($validated);

        return redirect()->route('admin.departments.show', $department)->with('success', 'Agent created successfully.');
    }

    public function destroyForDepartment(Department $department, DepartmentAgent $agent): RedirectResponse
    {
        $agent->delete();
        return redirect()->route('admin.departments.show', $department)->with('success', 'Agent deleted successfully.');
    }

    public function regenerateTokens(Department $department, DepartmentAgent $agent, DepartmentAgentService $service): RedirectResponse
    {
        $tokens = $service->revokeAndRegenerateTokens($agent);

        return redirect()->route('admin.departments.show', $department)
            ->with('success', 'Tokens regenerated successfully.')
            ->with('new_api_token', $tokens['api_token'])
            ->with('new_webhook_secret', $tokens['webhook_secret']);
    }
}
