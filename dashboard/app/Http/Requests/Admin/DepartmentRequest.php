<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class DepartmentRequest extends AdminFormRequest
{
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'risk_level_threshold' => ['required', 'in:low,medium,high,critical'],
        ];

        if ($this->isMethod('POST')) {
            $rules['code'] = ['required', 'string', Rule::unique('departments', 'code')];
        }

        return $rules;
    }
}
