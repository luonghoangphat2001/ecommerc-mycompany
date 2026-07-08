@extends('admin.layouts.app', ['title' => $title])

@php
    $renderNode = function ($item, $depth = 0) use (&$renderNode, $routePrefix) {
        $children = $item->children ?? collect();
        $indent = $depth * 22;
        $parentId = $item->id;
        echo '<div class="card" style="margin-top: 12px; padding: 14px 16px; margin-left:' . $indent . 'px;">';
        echo '<div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start;">';
        echo '<div>';
        echo '<div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">';
        echo '<strong>' . e($item->name) . '</strong>';
        echo '<span class="cell-muted">#' . e($item->id) . '</span>';
        echo '<span class="settings-chip">' . __('admin.categories.level', ['level' => $depth + 1]) . '</span>';
        echo '<span class="settings-chip">' . e($item->slug) . '</span>';
        if (isset($item->type)) {
            echo '<span class="settings-chip">' . e($item->type) . '</span>';
        }
        echo '<span class="settings-chip">' . ($item->is_visible ? __('admin.categories.visible') : __('admin.categories.hidden')) . '</span>';
        echo '</div>';
        if ($item->description) {
            echo '<div class="cell-muted" style="margin-top:6px; white-space:normal;">' . e(\Illuminate\Support\Str::limit(strip_tags((string) $item->description), 180)) . '</div>';
        }
        echo '</div>';
        echo '<div class="actions">';
        echo '<a class="link-action" href="' . e(route($routePrefix . '.edit', $item->id)) . '">' . e(__('admin.actions.edit')) . '</a>';
        echo '<a class="link-action" href="' . e(route($routePrefix . '.create', ['parent_id' => $parentId])) . '">' . e(__('admin.categories.add_child')) . '</a>';
        echo '<form method="post" action="' . e(route($routePrefix . '.destroy', $item->id)) . '">';
        echo csrf_field();
        echo method_field('delete');
        echo '<button class="link-action link-danger" type="submit" onclick="return confirm(\'' . e(__('admin.messages.confirm_delete')) . '\')">' . e(__('admin.actions.delete')) . '</button>';
        echo '</form>';
        echo '</div>';
        echo '</div>';

        if ($children->isNotEmpty() && $depth < 3) {
            echo '<div style="margin-top:12px;">';
            foreach ($children as $child) {
                $renderNode($child, $depth + 1);
            }
            echo '</div>';
        }

        echo '</div>';
    };
@endphp

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            <p class="page-description">{{ __('admin.categories.description') }}</p>
        </div>
        <div class="actions">
            <a class="btn btn-secondary" href="{{ route($routePrefix . '.index') }}">{{ __('admin.categories.refresh') }}</a>
            <a class="btn" href="{{ route($routePrefix . '.create') }}">{{ $createLabel }}</a>
        </div>
    </div>

    <div class="table-panel card">
        @if (($items ?? collect())->isEmpty())
            <div class="empty-state">{{ __('admin.messages.empty_state') }}</div>
        @else
            <div style="display:grid; gap:12px;">
                @foreach ($items as $item)
                    @if (blank($item->parent_id))
                        {!! $renderNode($item, 0) !!}
                    @endif
                @endforeach
            </div>
        @endif
    </div>
@endsection
