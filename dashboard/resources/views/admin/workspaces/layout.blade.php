@extends('admin.layouts.app', ['title' => $title ?? 'Workspace'])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $title ?? 'Workspace' }}</h1>
            <p class="page-description">{{ $description ?? 'Workspace details' }}</p>
        </div>
        @if(isset($workspaceCode))
            <div class="actions">
                <a class="btn btn-secondary" href="{{ route('admin.workspace.export', ['code' => $workspaceCode] + request()->query()) }}">Export Excel</a>
            </div>
        @endif
    </div>

    @if (session('status'))
        <div class="status-message">{{ session('status') }}</div>
    @endif

    @if(isset($workspaceCode) && ! empty($importDatasets ?? []))
        <div class="card workspace-import-card">
            <form method="post" action="{{ route('admin.workspace.import', $workspaceCode) }}" class="import-form" enctype="multipart/form-data">
                @csrf
                <select name="dataset" required>
                    @foreach($importDatasets as $datasetKey => $datasetLabel)
                        <option value="{{ $datasetKey }}">{{ $datasetLabel }}</option>
                    @endforeach
                </select>
                <input type="file" name="file" accept=".csv,.txt,.xls,.xlsx" required>
                <button class="btn btn-secondary" type="submit">Import Excel</button>
            </form>
            @error('dataset')<div class="error import-error">{{ $message }}</div>@enderror
            @error('file')<div class="error import-error">{{ $message }}</div>@enderror
        </div>
    @endif

    @yield('workspace_filters')

    <!-- Header KPIs -->
    <div class="stats">
        @yield('kpis')
    </div>

    <!-- 1-Column Grid Layout -->
    <div class="workspace-grid" style="display: grid; grid-template-columns: 100%; min-height: 600px;">
        
        <!-- Main Workspace Tabs -->
        <div class="workspace-main card" style="padding: 0;">
            <div x-data="{ activeTab: 0 }">
                <!-- Tab Headers -->
                @if(isset($tabs) && count($tabs) > 0)
                <div class="tabs-header" style="display: flex; border-bottom: 1px solid #e2e8f0; background: #f8fafc; border-radius: 8px 8px 0 0;">
                    @foreach($tabs as $index => $tab)
                        <button 
                            @click="activeTab = {{ $index }}"
                            :class="{ 'border-b-2 border-primary text-primary font-bold': activeTab === {{ $index }}, 'text-gray-500 hover:text-gray-700': activeTab !== {{ $index }} }"
                            style="padding: 15px 20px; border: none; background: transparent; cursor: pointer; font-size: 14px;">
                            {{ $tab }}
                        </button>
                    @endforeach
                </div>
                @endif
                
                <!-- Tab Contents -->
                <div class="tabs-content" style="padding: 20px;">
                    @yield('tab_contents')
                </div>
            </div>
        </div>

    </div>
@endsection
