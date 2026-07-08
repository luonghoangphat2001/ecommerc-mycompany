@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            <p class="page-description">{{ __('admin.comments.description') }}</p>
        </div>
        <div class="actions">
            <a class="btn btn-secondary" href="{{ route('admin.posts.index') }}">{{ __('admin.sidebar.posts') }}</a>
            <a class="btn btn-secondary" href="{{ route('admin.products.index') }}">{{ __('admin.sidebar.products') }}</a>
        </div>
    </div>

    <div class="table-panel card">
        <div class="table-wrap">
            <table style="min-width: 1080px;">
                <thead>
                    <tr>
                        <th>{{ __('admin.comments.target') }}</th>
                        <th>{{ __('admin.comments.user') }}</th>
                        <th>{{ __('fields.title') }}</th>
                        <th>{{ __('fields.content') }}</th>
                        <th>{{ __('admin.common.visibility') }}</th>
                        <th>{{ __('admin.common.created_at') }}</th>
                        <th>{{ __('admin.table_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>{{ $item->commentable_label }}</td>
                            <td>{{ $item->user?->name ?: '—' }}</td>
                            <td>{{ $item->title ?: '—' }}</td>
                            <td style="max-width: 360px; white-space: normal;">{{ \Illuminate\Support\Str::limit(strip_tags((string) $item->content), 180) }}</td>
                            <td>{{ $item->is_visible ? 'Có' : 'Không' }}</td>
                            <td class="cell-muted">{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="actions">
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
                        <tr>
                            <td colspan="7" class="empty-state">{{ __('admin.comments.empty_state') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $items->links('vendor.pagination.admin') }}</div>
    </div>
@endsection
