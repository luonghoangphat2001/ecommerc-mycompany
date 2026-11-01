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

    @if (!empty($tabs))
        <div style="margin-bottom: var(--fi-space-4, 16px);">
            <x-admin.tabs
                mode="link"
                :active="$activeTab ?? ''"
                :tabs="$tabs"
            />
        </div>
    @endif

    @if (session('status'))
        <div class="status-message">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <x-admin.data-table :paginator="$items">
        <x-slot:toolbar>
            <x-admin.table-toolbar :resetUrl="route($routePrefix . '.index')">
                <x-admin.search-input
                    name="q"
                    :label="__('admin.actions.search')"
                    :placeholder="__('admin.actions.search') . '...'"
                    width="240px"
                />

                @foreach (($filters ?? []) as $name => $filter)
                    @if (($filter['type'] ?? 'select') === 'date')
                        <div class="admin-filter-item" style="min-width: 155px; max-width: 175px;">
                            <label for="filter-{{ $name }}">{{ $filter['label'] ?? $name }}</label>
                            <input id="filter-{{ $name }}" type="date" name="{{ $name }}" value="{{ request($name) }}">
                        </div>
                    @else
                        <x-admin.filter-select
                            :name="$name"
                            :label="$filter['label'] ?? $name"
                            :options="$filter['options'] ?? []"
                            :placeholder="$filter['placeholder'] ?? '-- Tất cả --'"
                        />
                    @endif
                @endforeach
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
