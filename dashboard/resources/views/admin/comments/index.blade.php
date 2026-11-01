@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <x-admin.page-header
        :title="$title"
        :description="__('admin.comments.description')"
        :exportUrl="route('admin.comments.export', request()->query())"
        :importUrl="route('admin.comments.import')"
    >
        <a class="btn btn-secondary" href="{{ route('admin.posts.index') }}">{{ __('admin.sidebar.posts') }}</a>
        <a class="btn btn-secondary" href="{{ route('admin.products.index') }}">{{ __('admin.sidebar.products') }}</a>
    </x-admin.page-header>

    <x-admin.data-table :paginator="$items">
        <x-slot:head>
            <tr>
                <th>{{ __('admin.comments.target') }}</th>
                <th style="width: 160px;">{{ __('admin.comments.user') }}</th>
                <th style="width: 180px;">{{ __('fields.title') }}</th>
                <th>{{ __('fields.content') }}</th>
                <th style="width: 120px;">{{ __('admin.common.visibility') }}</th>
                <th style="width: 150px;">{{ __('admin.common.created_at') }}</th>
                <th class="text-right" style="width: 150px;">{{ __('admin.table_actions') }}</th>
            </tr>
        </x-slot:head>

        @forelse ($items as $item)
            <tr>
                <td style="font-weight: 600; color: var(--fi-text);">{{ $item->commentable_label }}</td>
                <td>{{ $item->user?->name ?: '—' }}</td>
                <td>{{ $item->title ?: '—' }}</td>
                <td style="max-width: 360px; white-space: normal;">{{ \Illuminate\Support\Str::limit(strip_tags((string) $item->content), 160) }}</td>
                <td>
                    <x-admin.status-badge :status="$item->is_visible ? 'active' : 'secondary'" :label="$item->is_visible ? 'Hiển thị' : 'Ẩn'" />
                </td>
                <td class="cell-muted mono">{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                <td class="text-right">
                    <x-admin.row-actions
                        :editUrl="route($routePrefix . '.edit', $item->id)"
                        :deleteUrl="route($routePrefix . '.destroy', $item->id)"
                    />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="empty-state">{{ __('admin.comments.empty_state') }}</td>
            </tr>
        @endforelse
    </x-admin.data-table>
@endsection
