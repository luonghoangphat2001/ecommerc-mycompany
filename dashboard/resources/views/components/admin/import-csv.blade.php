@props([
    'action',
    'label' => 'Nhập dữ liệu hàng loạt từ tệp CSV',
    'buttonLabel' => null,
    'accept' => '.csv,text/csv',
    'datasets' => [],
])

<div class="admin-import-bar" x-data="{ fileName: '' }">
    <div class="admin-import-title">
        <span class="admin-import-badge">IMPORT CSV</span>
        <span>{{ $label }}</span>
    </div>

    <form method="post" action="{{ $action }}" class="import-form" enctype="multipart/form-data">
        @csrf
        @if (!empty($datasets))
            <select name="dataset" required style="width: auto; min-width: 170px; height: 32px; min-height: 32px; font-size: 12px;">
                @foreach ($datasets as $datasetKey => $datasetLabel)
                    <option value="{{ $datasetKey }}">{{ $datasetLabel }}</option>
                @endforeach
            </select>
        @endif

        <label class="custom-file-picker">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <span x-text="fileName || 'Chọn tệp dữ liệu (.csv)...'">Chọn tệp dữ liệu (.csv)...</span>
            <input type="file" name="file" accept="{{ $accept }}" required @change="fileName = $event.target.files[0]?.name || ''">
        </label>

        <button class="btn btn-secondary btn-sm" type="submit">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
            <span>{{ $buttonLabel ?? __('admin.actions.import_csv') }}</span>
        </button>
    </form>
</div>
@error('dataset')
    <div class="error import-error">{{ $message }}</div>
@enderror
@error('file')
    <div class="error import-error">{{ $message }}</div>
@enderror

