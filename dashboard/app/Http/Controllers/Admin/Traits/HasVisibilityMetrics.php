<?php

namespace App\Http\Controllers\Admin\Traits;

trait HasVisibilityMetrics
{
    protected function visibilityMetrics(array $additional = []): array
    {
        $modelClass = $this->modelClass();

        $metrics = $modelClass::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN is_visible = 1 THEN 1 ELSE 0 END) as visible')
            ->selectRaw('SUM(CASE WHEN is_visible = 0 THEN 1 ELSE 0 END) as hidden')
            ->selectRaw('SUM(CASE WHEN parent_id IS NULL THEN 1 ELSE 0 END) as root')
            ->first();

        return array_merge([
            'total' => (int) ($metrics->total ?? 0),
            'visible' => (int) ($metrics->visible ?? 0),
            'hidden' => (int) ($metrics->hidden ?? 0),
            'root' => (int) ($metrics->root ?? 0),
        ], $additional);
    }
}
