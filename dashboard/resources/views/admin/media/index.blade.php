@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('admin.sidebar.media') }}</h1>
            <p class="page-description">{{ __('admin.media.description') }}</p>
        </div>
        <div class="actions">
            <a class="btn btn-secondary" href="{{ route($routePrefix . '.export', request()->query()) }}">{{ __('admin.actions.export_csv') }}</a>
            <a class="btn" href="{{ route($routePrefix . '.create') }}">{{ __('admin.actions.create') }}</a>
        </div>
    </div>

    @if (session('status'))
        <div class="status-message">{{ session('status') }}</div>
    @endif

    <div class="card media-library">
        <div class="toolbar-row media-toolbar">
            <form method="get" class="searchbar">
                <input type="hidden" name="view" value="{{ $viewMode }}">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('admin.media.search_placeholder') }}">
            </form>

            <div class="actions">
                <a class="view-toggle {{ $viewMode === 'grid' ? 'active' : '' }}" href="{{ route($routePrefix . '.index', array_merge(request()->query(), ['view' => 'grid'])) }}">Grid</a>
                <a class="view-toggle {{ $viewMode === 'table' ? 'active' : '' }}" href="{{ route($routePrefix . '.index', array_merge(request()->query(), ['view' => 'table'])) }}">Table</a>
            </div>
        </div>

        <form method="post" action="{{ route($routePrefix . '.import') }}" class="import-form media-import" enctype="multipart/form-data">
            @csrf
            <input type="file" name="file" accept=".csv,text/csv">
            <button class="btn btn-secondary" type="submit">{{ __('admin.actions.import_csv') }}</button>
        </form>

        @error('file')
            <div class="error import-error">{{ $message }}</div>
        @enderror

        @if ($viewMode === 'grid')
            <div class="media-grid">
                @forelse ($items as $item)
                    <article class="media-card">
                        <a class="media-preview" href="{{ route($routePrefix . '.show', $item->id) }}">
                            @if ($item->is_image && $item->preview_url)
                                <img src="{{ $item->preview_url }}" alt="{{ $item->alt ?: $item->name }}" loading="lazy">
                            @else
                                <div class="media-file-icon">{{ strtoupper($item->ext ?: 'FILE') }}</div>
                            @endif
                        </a>
                        <div class="media-meta">
                            <strong>{{ $item->title ?: $item->name }}</strong>
                            <span>{{ $item->file_name ?: $item->mime_type ?: '-' }}</span>
                            <span>{{ number_format((int) $item->size / 1024, 1) }} KB · {{ $item->width ?: '-' }}x{{ $item->height ?: '-' }}</span>
                        </div>
                        <div class="actions media-actions">
                            <a class="link-action" href="{{ route($routePrefix . '.show', $item->id) }}">{{ __('admin.actions.view') }}</a>
                            <a class="link-action" href="{{ route($routePrefix . '.edit', $item->id) }}">{{ __('admin.actions.edit') }}</a>
                            <form method="post" action="{{ route($routePrefix . '.destroy', $item->id) }}">
                                @csrf
                                @method('delete')
                                <button class="link-action link-danger" type="submit" onclick="return confirm('{{ __('admin.messages.confirm_delete') }}')">{{ __('admin.actions.delete') }}</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="empty-state">{{ __('admin.media.empty_state') }}</div>
                @endforelse
            </div>
        @else
            <div class="table-wrap">
                <table style="min-width: 980px;">
                    <thead>
                        <tr>
                            <th>{{ __('admin.media.preview') }}</th>
                            <th>ID</th>
                            <th>{{ __('fields.name') }}</th>
                            <th>{{ __('admin.media.file') }}</th>
                            <th>{{ __('admin.media.mime') }}</th>
                            <th>{{ __('admin.media.size') }}</th>
                            <th>{{ __('admin.media.dimensions') }}</th>
                            <th>{{ __('admin.table_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>
                                    <div class="media-thumb">
                                        @if ($item->is_image && $item->preview_url)
                                            <img src="{{ $item->preview_url }}" alt="{{ $item->alt ?: $item->name }}" loading="lazy">
                                        @else
                                            <span>{{ strtoupper($item->ext ?: 'FILE') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="cell-muted">#{{ $item->id }}</td>
                                <td>{{ $item->title ?: $item->name }}</td>
                                <td>{{ $item->file_name ?: '-' }}</td>
                                <td>{{ $item->mime_type ?: '-' }}</td>
                                <td>{{ number_format((int) $item->size / 1024, 1) }} KB</td>
                                <td>{{ $item->width ?: '-' }}x{{ $item->height ?: '-' }}</td>
                                <td>
                                    <div class="actions">
                                        <a class="link-action" href="{{ route($routePrefix . '.show', $item->id) }}">{{ __('admin.actions.view') }}</a>
                                        <a class="link-action" href="{{ route($routePrefix . '.edit', $item->id) }}">{{ __('admin.actions.edit') }}</a>
                                        <form method="post" action="{{ route($routePrefix . '.destroy', $item->id) }}">
                                            @csrf
                                            @method('delete')
                                            <button class="link-action link-danger" type="submit" onclick="return confirm('{{ __('admin.messages.confirm_delete') }}')">{{ __('admin.actions.delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="empty-state">{{ __('admin.media.empty_state') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        <div class="pagination">{{ $items->links('vendor.pagination.admin') }}</div>
    </div>
@endsection
