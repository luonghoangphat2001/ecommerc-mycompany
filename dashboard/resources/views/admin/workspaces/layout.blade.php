@extends('admin.layouts.app', ['title' => $title ?? 'Workspace'])

@section('content')
    <x-admin.page-header
        :title="$title ?? 'Workspace'"
        :description="$description ?? 'Workspace details'"
        :exportUrl="isset($workspaceCode) ? route('admin.workspace.export', ['code' => $workspaceCode] + request()->query()) : null"
        exportLabel="Export Excel"
    />

    @if (session('status'))
        <div class="status-message">{{ session('status') }}</div>
    @endif

    @yield('workspace_filters')

    <div class="stats">
        @yield('kpis')
    </div>

    <div class="table-panel card" x-data="{ activeTab: 0 }">
        @if(isset($workspaceCode) && ! empty($importDatasets ?? []))
            <x-admin.import-csv
                :action="route('admin.workspace.import', $workspaceCode)"
                label="Nhập dữ liệu phòng ban từ tệp CSV / Excel"
                buttonLabel="Import Excel"
                accept=".csv,.txt,.xls,.xlsx"
                :datasets="$importDatasets"
            />
        @endif

        @if(isset($tabs) && count($tabs) > 0)
            <x-admin.tabs :tabs="$tabs" mode="alpine" />
        @endif

        <div class="tabs-content" style="padding: 20px;">
            @yield('tab_contents')
        </div>
    </div>
@endsection
