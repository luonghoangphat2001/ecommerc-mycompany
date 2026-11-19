<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\Traits\HasAdminSelectOptions;
use App\Http\Controllers\Admin\Traits\HasInventoryTabs;
use App\Models\Inventory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InventoryController extends BaseCrudController
{
    use HasAdminSelectOptions;
    use HasInventoryTabs;

    protected function activeTab(): ?string
    {
        return 'inventories';
    }

    protected function modelClass(): string
    {
        return Inventory::class;
    }

    protected function title(): string
    {
        return 'admin.settings.manage_inventories';
    }

    protected function routePrefix(): string
    {
        return 'admin.inventories';
    }

    protected function searchable(): array
    {
        return ['name', 'slug', 'location'];
    }

    protected function fields(): array
    {
        return [
            'name' => ['label' => 'Tên kho', 'rules' => ['required', 'string', 'max:255']],
            'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255']],
            'location' => ['label' => 'Location', 'rules' => ['nullable', 'string', 'max:255']],
            'is_active' => ['label' => 'Kích hoạt', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => $this->activeField()['options']],
        ];
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        if (blank($data['slug'] ?? null) && filled($data['name'] ?? null)) {
            $data['slug'] = Str::slug((string) $data['name']);
        }

        return $data;
    }
}
