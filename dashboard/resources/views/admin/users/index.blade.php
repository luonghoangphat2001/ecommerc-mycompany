@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            <p class="page-description">{{ __('admin.list.description') }}</p>
        </div>
        <div class="actions">
            @foreach (($headerActions ?? []) as $action)
                @if (($action['method'] ?? 'get') === 'post')
                    <form method="post" action="{{ $action['url'] }}">
                        @csrf
                        <button class="btn {{ $action['class'] ?? 'btn-secondary' }}" type="submit">{{ $action['label'] }}</button>
                    </form>
                @else
                    <a class="btn {{ $action['class'] ?? 'btn-secondary' }}" href="{{ $action['url'] }}">{{ $action['label'] }}</a>
                @endif
            @endforeach
            @if ($canImportExport ?? false)
                <a class="btn btn-secondary" href="{{ route($routePrefix . '.export', request()->query()) }}">{{ __('admin.actions.export_csv') }}</a>
            @endif
            @if ($canCreate ?? true)
                <a class="btn" href="{{ route($routePrefix . '.create') }}">{{ __('admin.actions.create') }}</a>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="status-message">{{ session('status') }}</div>
    @endif

    <div class="table-panel card">
        <form method="get" class="list-filter-panel">
            <div class="list-filter-grid">
                <div>
                    <label>{{ __('admin.actions.search') }}</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Tên, email, SĐT...">
                </div>

                <div>
                    <label>Vai trò</label>
                    <select name="role">
                        <option value="">-- Tất cả --</option>
                        @foreach(\Spatie\Permission\Models\Role::orderBy('name')->pluck('name') as $r)
                            <option value="{{ $r }}" @selected(request('role') == $r)>{{ \App\Support\AdminLabel::role($r) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Phòng ban</label>
                    <select name="department_id">
                        <option value="">-- Tất cả --</option>
                        @foreach(\App\Models\Department::orderBy('name')->pluck('name', 'id') as $id => $deptName)
                            <option value="{{ $id }}" @selected(request('department_id') == $id)>{{ $deptName }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Ngày tham gia</label>
                    <input type="date" name="date" value="{{ request('date') }}">
                </div>
            </div>

            <div class="list-filter-actions">
                <a href="{{ route($routePrefix . '.index') }}" class="btn btn-secondary">{{ __('admin.actions.reset') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('admin.actions.filter_data') }}</button>
            </div>
        </form>

        @if ($canImportExport ?? false)
            <div class="crud-import-row">
                <form method="post" action="{{ route($routePrefix . '.import') }}" class="import-form" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="file" accept=".csv,text/csv">
                    <button class="btn btn-secondary" type="submit">{{ __('admin.actions.import_csv') }}</button>
                </form>
            </div>
        @endif

        @error('file')
            <div class="error import-error">{{ $message }}</div>
        @enderror

        <div class="table-wrap">
            <table style="min-width: 980px;">
                <thead>
                    <tr>
                        <th>ID</th>
                        @foreach ($fields as $name => $field)
                            <th>{{ $field['label'] ?? $name }}</th>
                        @endforeach
                        <th>{{ __('admin.table_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td class="cell-muted">#{{ $item->id }}</td>
                            @foreach ($fields as $name => $field)
                                @php
                                    $value = data_get($item, $name);
                                    $type = $field['type'] ?? 'text';
                                @endphp
                                <td>
                                    @include('admin.crud.show_field', ['type' => $type, 'value' => $value, 'record' => $item, 'compact' => true])
                                </td>
                            @endforeach
                            <td>
                                <div class="actions">
                                    <a class="link-action" href="{{ route($routePrefix . '.show', $item->id) }}">{{ __('admin.actions.view') }}</a>
                                    @if ($canEdit ?? true)
                                        <a class="link-action" href="{{ route($routePrefix . '.edit', $item->id) }}">{{ __('admin.actions.edit') }}</a>
                                    @endif
                                    @if ($canDelete ?? true)
                                        <form method="post" action="{{ route($routePrefix . '.destroy', $item->id) }}">
                                            @csrf
                                            @method('delete')
                                            <button class="link-action link-danger" type="submit" onclick="return confirm('{{ __('admin.messages.confirm_delete') }}')">{{ __('admin.actions.delete') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%" class="empty-state">{{ __('admin.messages.empty_state') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $items->links('vendor.pagination.admin') }}</div>
    </div>
@endsection
