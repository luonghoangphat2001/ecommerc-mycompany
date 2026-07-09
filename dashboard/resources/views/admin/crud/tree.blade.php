@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
        </div>
        <div class="actions">
            <a class="btn" href="{{ route($routePrefix . '.create') }}">{{ __('admin.actions.create') }}</a>
        </div>
    </div>

    @if (session('status'))
        <div class="status-message">{{ session('status') }}</div>
    @endif

    <div class="card p-4">
        <p class="text-muted mb-4">Kéo thả để sắp xếp vị trí và thứ bậc.</p>
        
        <div class="dd" id="nestable-tree">
            {!! $treeHtml !!}
        </div>
        
        <div class="mt-4">
            <button id="save-tree-btn" class="btn btn-primary" style="display: none;">Lưu thứ tự</button>
        </div>
    </div>

    <!-- Nestable2 CSS -->
    <style>
        .dd { position: relative; display: block; margin: 0; padding: 0; max-width: 800px; list-style: none; font-size: 14px; line-height: 20px; }
        .dd-list { display: block; position: relative; margin: 0; padding: 0; list-style: none; }
        .dd-list .dd-list { padding-left: 30px; }
        .dd-item, .dd-empty, .dd-placeholder { display: block; position: relative; margin: 0; padding: 0; min-height: 20px; font-size: 14px; line-height: 20px; }
        .dd-handle { display: block; height: 42px; margin: 5px 0; padding: 10px 14px; color: #333; text-decoration: none; font-weight: 600; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 6px; cursor: move; }
        .dd-handle:hover { color: #2ea8e5; background: #fff; }
        .dd-item > button { position: relative; cursor: pointer; float: left; width: 25px; height: 20px; margin: 10px 0; padding: 0; text-indent: 100%; white-space: nowrap; overflow: hidden; border: 0; background: transparent; font-size: 12px; line-height: 1; text-align: center; font-weight: bold; }
        .dd-item > button:before { content: '+'; display: block; position: absolute; width: 100%; text-align: center; text-indent: 0; }
        .dd-item > button[data-action="collapse"]:before { content: '-'; }
        .dd-placeholder, .dd-empty { margin: 5px 0; padding: 0; min-height: 42px; background: #f1f5f9; border: 1px dashed #cbd5e1; box-sizing: border-box; border-radius: 6px; }
        .dd-dragel { position: absolute; pointer-events: none; z-index: 9999; }
        .dd-dragel > .dd-item .dd-handle { margin-top: 0; opacity: 0.8; }
        
        .item-actions { float: right; }
        .item-actions a { margin-left: 10px; font-weight: 400; font-size: 13px; color: #64748b; text-decoration: none; }
        .item-actions a:hover { color: #2ea8e5; }
        .item-actions button { background: transparent; border: none; font-weight: 400; font-size: 13px; color: #ef4444; margin-left: 10px; cursor: pointer; }
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
                maxDepth: {{ $maxDepth ?? 3 }}
            }).on('change', function() {
                $saveBtn.show();
            });

            $saveBtn.on('click', function() {
                var data = $tree.nestable('serialize');
                var btn = $(this);
                btn.text('Đang lưu...').prop('disabled', true);
                
                $.ajax({
                    url: '{{ $reorderUrl }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: { tree: data },
                    success: function() {
                        btn.text('Đã lưu').removeClass('btn-primary').addClass('btn-green');
                        setTimeout(() => {
                            btn.text('Lưu thứ tự').removeClass('btn-green').addClass('btn-primary').hide();
                            btn.prop('disabled', false);
                        }, 2000);
                    },
                    error: function() {
                        alert('Có lỗi xảy ra khi lưu thứ tự.');
                        btn.text('Lưu thứ tự').prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endsection
