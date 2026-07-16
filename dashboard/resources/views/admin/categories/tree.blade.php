@extends('admin.layouts.app', ['title' => $title])

@php
    $renderNode = function ($item, $depth = 0) use (&$renderNode, $routePrefix) {
        $children = $item->children ?? collect();
        echo '<li class="dd-item" data-id="' . $item->id . '">';
        echo '<div class="dd-handle">';
        echo '<div style="display:flex; justify-content:space-between; align-items:center;">';
        echo '<div>';
        echo '<strong>' . e($item->name) . '</strong>';
        echo '<span class="cell-muted" style="margin-left: 10px;">#' . e($item->id) . '</span>';
        if (isset($item->type)) {
            echo '<span class="settings-chip" style="margin-left: 10px;">' . e($item->type) . '</span>';
        }
        echo '<span class="settings-chip" style="margin-left: 10px;">' . ($item->is_visible ? __('admin.categories.visible') : __('admin.categories.hidden')) . '</span>';
        echo '</div>';
        echo '<div class="item-actions">';
        echo '<a href="' . e(route($routePrefix . '.edit', $item->id)) . '" onclick="event.stopPropagation();">' . __('admin.actions.edit') . '</a>';
        echo '<a href="' . e(route($routePrefix . '.create', ['parent_id' => $item->id])) . '" onclick="event.stopPropagation();">+ ' . __('admin.categories.add_child') . '</a>';
        echo '<form method="post" action="' . e(route($routePrefix . '.destroy', $item->id)) . '" style="display:inline;" onclick="event.stopPropagation();">';
        echo csrf_field();
        echo method_field('delete');
        echo '<button type="submit" onclick="return confirm(\'' . __('admin.messages.confirm_delete') . '\')">' . __('admin.actions.delete') . '</button>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        if ($children->isNotEmpty()) {
            // Must be sorted by order_column if applicable, but controller does it or we can do it here:
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
            <a class="btn" href="{{ route($routePrefix . '.create') }}">{{ $createLabel }}</a>
        </div>
    </div>

    @if (session('status'))
        <div class="status-message">{{ session('status') }}</div>
    @endif

    <div class="card p-4" style="padding: 20px;">
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
            
            <div style="margin-top: 20px;">
                <button id="save-tree-btn" class="btn btn-primary" style="display: none;">{{ __('admin.categories.save_order') }}</button>
            </div>
        @endif
    </div>

    <!-- Nestable2 CSS -->
    <style>
        .dd { position: relative; display: block; margin: 0; padding: 0; max-width: 100%; list-style: none; font-size: 14px; line-height: 20px; }
        .dd-list { display: block; position: relative; margin: 0; padding: 0; list-style: none; }
        .dd-list .dd-list { padding-left: 30px; }
        .dd-item, .dd-empty, .dd-placeholder { display: block; position: relative; margin: 0; padding: 0; min-height: 20px; font-size: 14px; line-height: 20px; }
        .dd-handle { display: block; height: auto; min-height: 42px; margin: 5px 0; padding: 10px 14px; color: #333; text-decoration: none; font-weight: 600; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 6px; cursor: move; }
        .dd-handle:hover { background: #fff; border-color: #cbd5e1; }
        .dd-item > button { position: relative; cursor: pointer; float: left; width: 25px; height: 30px; margin: 5px 0; padding: 0; text-indent: 100%; white-space: nowrap; overflow: hidden; border: 0; background: transparent; font-size: 16px; line-height: 1; text-align: center; font-weight: bold; }
        .dd-item > button:before { content: '+'; display: block; position: absolute; width: 100%; text-align: center; text-indent: 0; color: #64748b; margin-top: 8px;}
        .dd-item > button[data-action="collapse"]:before { content: '-'; }
        .dd-placeholder, .dd-empty { margin: 5px 0; padding: 0; min-height: 42px; background: #f1f5f9; border: 1px dashed #cbd5e1; box-sizing: border-box; border-radius: 6px; }
        .dd-dragel { position: absolute; pointer-events: none; z-index: 9999; }
        .dd-dragel > .dd-item .dd-handle { margin-top: 0; opacity: 0.8; }
        
        .item-actions { float: right; pointer-events: auto; }
        .item-actions a { margin-left: 10px; font-weight: 400; font-size: 13px; color: #3b82f6; text-decoration: none; }
        .item-actions a:hover { text-decoration: underline; }
        .item-actions button { background: transparent; border: none; font-weight: 400; font-size: 13px; color: #ef4444; margin-left: 10px; cursor: pointer; }
        .item-actions button:hover { text-decoration: underline; }
    </style>

    <!-- jQuery & Nestable2 JS -->
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
                        btn.text('{{ __('admin.categories.saved_success') }}').css('background-color', '#10b981').css('border-color', '#10b981');
                        setTimeout(() => {
                            btn.text('{{ __('admin.categories.save_order') }}').css('background-color', '').css('border-color', '').hide();
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
