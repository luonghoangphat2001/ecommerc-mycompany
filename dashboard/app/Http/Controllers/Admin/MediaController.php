<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return Media::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.media';
    }

    protected function routePrefix(): string
    {
        return 'admin.media';
    }

    protected function searchable(): array
    {
        return ['name', 'file_name', 'mime_type', 'path', 'alt', 'title'];
    }

    public function index(Request $request): View
    {
        $query = Media::query();
        $this->applySearch($query, $request);

        $viewMode = $request->query('view') === 'table' ? 'table' : 'grid';

        return view('admin.media.index', [
            'title' => 'Media',
            'items' => $query->latest('id')->paginate(18)->withQueryString(),
            'fields' => $this->visibleFields('index'),
            'routePrefix' => $this->routePrefix(),
            'viewMode' => $viewMode,
            'canCreate' => $this->canCreate(),
            'canEdit' => $this->canEdit(),
            'canDelete' => $this->canDelete(),
            'canImportExport' => true,
        ]);
    }

    protected function fields(): array
    {
        return [
            'name' => ['label' => __('admin.media.fields.name'), 'rules' => ['required', 'string', 'max:255']],
            'file_name' => ['label' => __('admin.media.fields.file_name'), 'rules' => ['nullable', 'string', 'max:255']],
            'mime_type' => ['label' => __('admin.media.fields.mime_type'), 'rules' => ['nullable', 'string', 'max:255']],
            'disk' => ['label' => __('admin.media.fields.disk'), 'rules' => ['required', 'string', 'max:255']],
            'size' => ['label' => __('admin.media.fields.size'), 'type' => 'number', 'rules' => ['required', 'integer', 'min:0']],
            'path' => ['label' => __('admin.media.fields.path'), 'rules' => ['nullable', 'string', 'max:255'], 'hideOnIndex' => true],
            'visibility' => ['label' => __('admin.media.fields.visibility'), 'type' => 'select', 'rules' => ['nullable', 'string', 'max:30'], 'options' => ['public' => 'public', 'private' => 'private']],
            'width' => ['label' => __('admin.media.fields.width'), 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
            'height' => ['label' => __('admin.media.fields.height'), 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
            'type' => ['label' => __('admin.media.fields.type'), 'rules' => ['nullable', 'string', 'max:255']],
            'ext' => ['label' => __('admin.media.fields.ext'), 'rules' => ['nullable', 'string', 'max:20']],
            'alt' => ['label' => __('admin.media.fields.alt'), 'rules' => ['nullable', 'string', 'max:255']],
            'title' => ['label' => __('admin.media.fields.title'), 'rules' => ['nullable', 'string', 'max:255']],
            'description' => ['label' => __('admin.media.fields.description'), 'type' => 'textarea', 'rules' => ['nullable', 'string']],
            'caption' => ['label' => __('admin.media.fields.caption'), 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        ];
    }
}
