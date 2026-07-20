<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DepartmentAgent;
use App\Ecommerce\Department\Services\DepartmentAgentService;
use Illuminate\Http\Request;

class DepartmentAgentController extends Controller
{
    public function store(Request $request, Department $department)
    {
        $validated = $request->validate([
            'agent_code' => 'required|string|unique:department_agents,agent_code',
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $department->agents()->create($validated);

        return redirect()->route('admin.departments.show', $department)->with('success', 'Agent created successfully.');
    }

    public function destroy(Department $department, DepartmentAgent $agent)
    {
        $agent->delete();
        return redirect()->route('admin.departments.show', $department)->with('success', 'Agent deleted successfully.');
    }

    public function regenerateTokens(Department $department, DepartmentAgent $agent, DepartmentAgentService $service)
    {
        $tokens = $service->revokeAndRegenerateTokens($agent);

        return redirect()->route('admin.departments.show', $department)
            ->with('success', 'Tokens regenerated successfully.')
            ->with('new_api_token', $tokens['api_token'])
            ->with('new_webhook_secret', $tokens['webhook_secret']);
    }
}
