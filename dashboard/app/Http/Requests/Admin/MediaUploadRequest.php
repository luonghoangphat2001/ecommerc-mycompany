<?php

namespace App\Http\Requests\Admin;

class MediaUploadRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return ['file' => ['required', 'file', 'image', 'max:10240']];
    }
}
