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
        return 'Media';
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
            'name' => ['label' => 'Tên media', 'rules' => ['required', 'string', 'max:255']],
            'file_name' => ['label' => 'File name', 'rules' => ['nullable', 'string', 'max:255']],
            'mime_type' => ['label' => 'Mime type', 'rules' => ['nullable', 'string', 'max:255']],
            'disk' => ['label' => 'Disk', 'rules' => ['required', 'string', 'max:255']],
            'size' => ['label' => 'Size', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0']],
            'path' => ['label' => 'Path', 'rules' => ['nullable', 'string', 'max:255'], 'hideOnIndex' => true],
            'visibility' => ['label' => 'Visibility', 'type' => 'select', 'rules' => ['nullable', 'string', 'max:30'], 'options' => ['public' => 'public', 'private' => 'private']],
            'width' => ['label' => 'Width', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
            'height' => ['label' => 'Height', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
            'type' => ['label' => 'Type', 'rules' => ['nullable', 'string', 'max:255']],
            'ext' => ['label' => 'Ext', 'rules' => ['nullable', 'string', 'max:20']],
            'alt' => ['label' => 'Alt', 'rules' => ['nullable', 'string', 'max:255']],
            'title' => ['label' => 'Title', 'rules' => ['nullable', 'string', 'max:255']],
            'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
            'caption' => ['label' => 'Caption', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        ];
    }
}
