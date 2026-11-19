<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class DepartmentAgentStoreRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'agent_code' => ['required', 'string', Rule::unique('department_agents', 'agent_code')],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive,suspended'],
        ];
    }
}
