<?php

namespace App\Http\Controllers\Admin\Traits;

use Illuminate\Support\Facades\Schema;

trait HasHierarchicalOptions
{
    private array $hierarchyTreeCache = [];

    protected function hierarchyCollections($records): array
    {
        $grouped = $records->groupBy('parent_id');
        $roots = $grouped->get(null, collect());
        $recordIds = $records->pluck('id')->all();
        $orphans = $records->filter(
            fn ($record) => ! is_null($record->parent_id) && ! in_array($record->parent_id, $recordIds, true)
        );

        return [
            'grouped' => $grouped,
            'roots' => $orphans->isNotEmpty() ? $roots->merge($orphans) : $roots,
        ];
    }

    protected function hierarchyTree(string $modelClass, string $orderBy = 'name')
    {
        $key = $modelClass . ':' . $orderBy;
        if (isset($this->hierarchyTreeCache[$key])) {
            return $this->hierarchyTreeCache[$key];
        }

        return $this->hierarchyTreeCache[$key] = $modelClass::query()
            ->whereNull('parent_id')
            ->with('children.children')
            ->orderBy($orderBy)
            ->get();
    }

    public function getParentCategoryOptions(?int $excludeId = null): array
    {
        $modelClass = $this->modelClass();
        $model = new $modelClass;
        $query = $modelClass::query()
            ->orderBy('order_column');

        if (Schema::hasColumn($model->getTable(), 'sort')) {
            $query->orderBy('sort');
        }

        $records = $query->orderBy('id')->get();
        $rootLabel = method_exists($this, 'hierarchyRootLabel')
            ? $this->hierarchyRootLabel()
            : 'Danh mục gốc';
        $options = ['' => '-- ' . __('admin.common.none') . ' (' . $rootLabel . ') --'];

        $buildTree = function ($parentId, string $prefix = '') use (&$buildTree, &$options, $records, $excludeId): void {
            foreach ($records->where('parent_id', $parentId) as $record) {
                if ($excludeId && (int) $record->id === $excludeId) {
                    continue;
                }

                $name = $record->getTranslation('name', 'vi') ?: $record->slug;
                $options[$record->id] = $prefix . ($prefix ? '↳ ' : '') . $name;
                $buildTree($record->id, $prefix . '    ');
            }
        };

        $buildTree(null);

        return $options;
    }
}
