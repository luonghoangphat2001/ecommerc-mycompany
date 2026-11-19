<?php

namespace App\Http\Requests\Admin;

class InlineAttributeRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:select,button,color'],
            'values' => ['nullable'],
        ];
    }
}
