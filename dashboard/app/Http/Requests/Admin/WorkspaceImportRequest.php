<?php

namespace App\Http\Requests\Admin;

class WorkspaceImportRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'dataset' => ['required', 'string'],
            'file' => ['required', 'file', 'mimes:csv,txt,xls,xlsx', 'max:5120'],
        ];
    }
}
