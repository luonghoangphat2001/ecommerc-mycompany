@extends('admin.layouts.app', ['title' => $title])

@php
    $renderNode = function ($item, $depth = 0) use (&$renderNode, $routePrefix) {
        $children = $item->children ?? collect();
        echo '<li class="dd-item" data-id="' . $item->id . '">';
        echo '<div class="dd-handle">';
        echo '<div style="display:flex; justify-content:space-between; align-items:center; gap: 12px;">';
        echo '<div style="display:flex; align-items:center; gap:8px;">';
        echo '<strong style="color: var(--fi-text);">' . e($item->name) . '</strong>';
        echo '<span class="cell-muted mono">#' . e($item->id) . '</span>';
        if (isset($item->type)) {
            echo '<span class="settings-chip">' . e($item->type) . '</span>';
        }
        echo '<span class="badge ' . ($item->is_visible ? 'badge-success' : 'badge-secondary') . '">' . ($item->is_visible ? __('admin.categories.visible') : __('admin.categories.hidden')) . '</span>';
        echo '</div>';
        echo '<div class="item-actions actions">';
        echo '<a class="link-action" href="' . e(route($routePrefix . '.edit', $item->id)) . '" onclick="event.stopPropagation();">' . __('admin.actions.edit') . '</a>';
        echo '<a class="link-action" href="' . e(route($routePrefix . '.create', ['parent_id' => $item->id])) . '" onclick="event.stopPropagation();">+ ' . __('admin.categories.add_child') . '</a>';
        echo '<form method="post" action="' . e(route($routePrefix . '.destroy', $item->id)) . '" style="display:inline; margin:0;" onclick="event.stopPropagation();">';
        echo csrf_field();
        echo method_field('delete');
        echo '<button type="submit" class="link-action link-danger" onclick="return confirm(\'' . __('admin.messages.confirm_delete') . '\')">' . __('admin.actions.delete') . '</button>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        if ($children->isNotEmpty()) {
            $sortedChildren = $children->sortBy('order_column');
            echo '<ol class="dd-list">';
            foreach ($sortedChildren as $child) {
                $renderNode($child, $depth + 1);
            }
            echo '</ol>';
        }
        echo '</li>';
    };
@endphp

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            <p class="page-description">{{ __('admin.categories.drag_drop') }}</p>
        </div>
        <div class="actions">
            <a class="btn btn-secondary" href="{{ route($routePrefix . '.index') }}">{{ __('admin.categories.refresh') }}</a>
            <a class="btn btn-primary" href="{{ route($routePrefix . '.create') }}">+ {{ $createLabel }}</a>
        </div>
    </div>

    @if (session('status'))
        <div class="status-message">{{ session('status') }}</div>
    @endif

    <div class="card">
        @if (($items ?? collect())->isEmpty())
            <div class="empty-state">{{ __('admin.messages.empty_state') }}</div>
        @else
            <div class="dd" id="nestable-tree">
                <ol class="dd-list">
                    @php
                        $rootItems = $items->where('parent_id', null)->sortBy('order_column');
                    @endphp
                    @foreach ($rootItems as $item)
                        {!! $renderNode($item, 0) !!}
                    @endforeach
                </ol>
            </div>

            <div style="margin-top: 16px;">
                <button id="save-tree-btn" class="btn btn-primary" style="display: none;">{{ __('admin.categories.save_order') }}</button>
            </div>
        @endif
    </div>

    <style>
        .dd { position: relative; display: block; margin: 0; padding: 0; max-width: 100%; list-style: none; font-size: 13px; line-height: 20px; }
        .dd-list { display: block; position: relative; margin: 0; padding: 0; list-style: none; }
        .dd-list .dd-list { padding-left: 28px; }
        .dd-item, .dd-empty, .dd-placeholder { display: block; position: relative; margin: 0; padding: 0; min-height: 20px; }
        .dd-handle { display: block; min-height: 40px; margin: 6px 0; padding: 8px 14px; color: var(--fi-text); font-weight: 500; border: 1px solid var(--fi-border); background: #ffffff; border-radius: 8px; cursor: move; transition: border-color 0.14s ease, background 0.14s ease; }
        .dd-handle:hover { background: #f8fafc; border-color: var(--fi-border-strong); }
        .dd-item > button { position: relative; cursor: pointer; float: left; width: 24px; height: 28px; margin: 6px 0; padding: 0; text-indent: 100%; white-space: nowrap; overflow: hidden; border: 0; background: transparent; font-size: 15px; line-height: 1; text-align: center; font-weight: bold; }
        .dd-item > button:before { content: '+'; display: block; position: absolute; width: 100%; text-align: center; text-indent: 0; color: var(--fi-muted); margin-top: 7px; }
        .dd-item > button[data-action="collapse"]:before { content: '−'; }
        .dd-placeholder, .dd-empty { margin: 6px 0; padding: 0; min-height: 40px; background: #f8fafc; border: 1px dashed var(--fi-border-strong); border-radius: 8px; }
        .dd-dragel { position: absolute; pointer-events: none; z-index: 9999; }
        .dd-dragel > .dd-item .dd-handle { margin-top: 0; opacity: 0.9; box-shadow: var(--fi-shadow-md); }
        .item-actions { pointer-events: auto; }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.js"></script>
    <script>
        $(document).ready(function() {
            var $tree = $('#nestable-tree');
            var $saveBtn = $('#save-tree-btn');

            $tree.nestable({
                group: 1,
                maxDepth: 5
            }).on('change', function() {
                $saveBtn.show();
            });

            $saveBtn.on('click', function() {
                var data = $tree.nestable('serialize');
                var btn = $(this);
                btn.text('{{ __('admin.categories.saving') }}').prop('disabled', true);

                $.ajax({
                    url: '{{ route($routePrefix . '.reorder') }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: { tree: data },
                    success: function() {
                        btn.text('{{ __('admin.categories.saved_success') }}').css('background-color', '#059669');
                        setTimeout(() => {
                            btn.text('{{ __('admin.categories.save_order') }}').css('background-color', '').hide();
                            btn.prop('disabled', false);
                        }, 2000);
                    },
                    error: function() {
                        alert('{{ __('admin.categories.save_error') }}');
                        btn.text('{{ __('admin.categories.save_order') }}').prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endsection
