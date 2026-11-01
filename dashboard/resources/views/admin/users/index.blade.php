@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <x-admin.page-header
        :title="$title"
        :description="__('admin.list.description')"
        :exportUrl="($canImportExport ?? false) ? route($routePrefix . '.export', request()->query()) : null"
        :importUrl="($canImportExport ?? false) ? route($routePrefix . '.import') : null"
        :createUrl="($canCreate ?? true) ? route($routePrefix . '.create') : null"
    >
        @foreach (($headerActions ?? []) as $action)
            @if (($action['method'] ?? 'get') === 'post')
                <form method="post" action="{{ $action['url'] }}" style="margin: 0;">
                    @csrf
                    <button class="btn {{ $action['class'] ?? 'btn-secondary' }}" type="submit">{{ $action['label'] }}</button>
                </form>
            @else
                <a class="btn {{ $action['class'] ?? 'btn-secondary' }}" href="{{ $action['url'] }}">{{ $action['label'] }}</a>
            @endif
        @endforeach
    </x-admin.page-header>

    @if (session('status'))
        <div class="status-message">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @php
        $roleOptions = [];
        foreach (\Spatie\Permission\Models\Role::orderBy('name')->pluck('name') as $r) {
            $roleOptions[$r] = \App\Support\AdminLabel::role($r);
        }
        $departmentOptions = \App\Models\Department::orderBy('name')->pluck('name', 'id')->toArray();
    @endphp

    <x-admin.data-table :paginator="$items">
        <x-slot:toolbar>
            <x-admin.table-toolbar :resetUrl="route($routePrefix . '.index')">
                <x-admin.search-input
                    name="q"
                    :label="__('admin.actions.search')"
                    placeholder="Tên, email, SĐT..."
                    width="240px"
                />

                <x-admin.filter-select
                    name="role"
                    label="Vai trò"
                    :options="$roleOptions"
                    placeholder="-- Tất cả vai trò --"
                    width="180px"
                />

                <x-admin.filter-select
                    name="department_id"
                    label="Phòng ban"
                    :options="$departmentOptions"
                    placeholder="-- Tất cả phòng ban --"
                    width="190px"
                />

                <div class="admin-filter-item" style="min-width: 155px; max-width: 175px;">
                    <label for="user-filter-date">Ngày tham gia</label>
                    <input id="user-filter-date" type="date" name="date" value="{{ request('date') }}">
                </div>
            </x-admin.table-toolbar>
        </x-slot:toolbar>

        <x-slot:head>
            <tr>
                <th class="text-center" style="width: 72px;">ID</th>
                @foreach ($fields as $name => $field)
                    <th>{{ $field['label'] ?? $name }}</th>
                @endforeach
                <th class="text-right" style="width: 180px;">{{ __('admin.table_actions') }}</th>
            </tr>
        </x-slot:head>

        @forelse ($items as $item)
            <tr>
                <td class="cell-muted mono text-center">#{{ $item->id }}</td>
                @foreach ($fields as $name => $field)
                    @php
                        $value = data_get($item, $name);
                        $type = $field['type'] ?? 'text';
                    @endphp
                    <td>
                        @include('admin.crud.show_field', ['type' => $type, 'value' => $value, 'record' => $item, 'name' => $name, 'field' => $field, 'compact' => true])
                    </td>
                @endforeach
                <td class="text-right">
                    <x-admin.row-actions
                        :viewUrl="route($routePrefix . '.show', $item->id)"
                        :editUrl="($canEdit ?? true) ? route($routePrefix . '.edit', $item->id) : null"
                        :deleteUrl="($canDelete ?? true) ? route($routePrefix . '.destroy', $item->id) : null"
                    />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="100%" class="empty-state">{{ __('admin.messages.empty_state') }}</td>
            </tr>
        @endforelse
    </x-admin.data-table>
@endsection
