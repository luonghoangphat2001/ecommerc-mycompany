@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <x-admin.page-header
        :title="__('admin.sidebar.media')"
        :description="__('admin.media.description')"
        :exportUrl="route($routePrefix . '.export', request()->query())"
        :importUrl="route($routePrefix . '.import')"
        :createUrl="route($routePrefix . '.create')"
    />

    @if (session('status'))
        <div class="status-message">{{ session('status') }}</div>
    @endif

    <div class="table-panel card">
        <x-admin.table-toolbar :resetUrl="route($routePrefix . '.index', ['view' => $viewMode])">
            <input type="hidden" name="view" value="{{ $viewMode }}">
            <x-admin.search-input
                name="q"
                :label="__('admin.actions.search')"
                :placeholder="__('admin.media.search_placeholder')"
                width="260px"
            />

            <x-slot:right>
                <div class="segmented-tabs" role="tablist" aria-label="View Mode">
                    <a class="segmented-tab {{ $viewMode === 'grid' ? 'active' : '' }}" href="{{ route($routePrefix . '.index', array_merge(request()->query(), ['view' => 'grid'])) }}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                        <span>Grid</span>
                    </a>
                    <a class="segmented-tab {{ $viewMode === 'table' ? 'active' : '' }}" href="{{ route($routePrefix . '.index', array_merge(request()->query(), ['view' => 'table'])) }}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/></svg>
                        <span>Table</span>
                    </a>
                </div>
            </x-slot:right>
        </x-admin.table-toolbar>

        @if ($viewMode === 'grid')
            <div style="padding: 18px 20px;">
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
                                <span class="mono">{{ number_format((int) $item->size / 1024, 1) }} KB · {{ $item->width ?: '-' }}x{{ $item->height ?: '-' }}</span>
                            </div>
                            <div class="media-actions">
                                <x-admin.row-actions
                                    :viewUrl="route($routePrefix . '.show', $item->id)"
                                    :editUrl="route($routePrefix . '.edit', $item->id)"
                                    :deleteUrl="route($routePrefix . '.destroy', $item->id)"
                                />
                            </div>
                        </article>
                    @empty
                        <div class="empty-state" style="grid-column: 1 / -1;">{{ __('admin.media.empty_state') }}</div>
                    @endforelse
                </div>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 84px;">{{ __('admin.media.preview') }}</th>
                            <th class="text-center" style="width: 72px;">ID</th>
                            <th>{{ __('fields.name') }}</th>
                            <th>{{ __('admin.media.file') }}</th>
                            <th style="width: 150px;">{{ __('admin.media.mime') }}</th>
                            <th class="text-right" style="width: 110px;">{{ __('admin.media.size') }}</th>
                            <th class="text-center" style="width: 120px;">{{ __('admin.media.dimensions') }}</th>
                            <th class="text-right" style="width: 180px;">{{ __('admin.table_actions') }}</th>
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
                                <td class="cell-muted mono text-center">#{{ $item->id }}</td>
                                <td style="font-weight: 600; color: var(--fi-text);">{{ $item->title ?: $item->name }}</td>
                                <td class="mono" style="font-size: 12px;">{{ $item->file_name ?: '-' }}</td>
                                <td class="cell-muted mono">{{ $item->mime_type ?: '-' }}</td>
                                <td class="mono text-right">{{ number_format((int) $item->size / 1024, 1) }} KB</td>
                                <td class="mono text-center">{{ $item->width ?: '-' }}x{{ $item->height ?: '-' }}</td>
                                <td class="text-right">
                                    <x-admin.row-actions
                                        :viewUrl="route($routePrefix . '.show', $item->id)"
                                        :editUrl="route($routePrefix . '.edit', $item->id)"
                                        :deleteUrl="route($routePrefix . '.destroy', $item->id)"
                                    />
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
