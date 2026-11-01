@props([
    'title',
    'description' => null,
    'backUrl' => null,
    'backLabel' => null,
    'exportUrl' => null,
    'exportLabel' => null,
    'importUrl' => null,
    'importLabel' => null,
    'createUrl' => null,
    'createLabel' => null,
])

<div class="page-header">
    <div>
        <h1 class="page-title">{{ $title }}</h1>
        @if ($description)
            <p class="page-description">{{ $description }}</p>
        @endif
    </div>

    <div class="actions">
        @if (isset($actions))
            {{ $actions }}
        @endif

        {{ $slot }}

        @if ($backUrl)
            <a class="btn btn-secondary" href="{{ $backUrl }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" x2="5" y1="12" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                <span>{{ $backLabel ?? __('admin.actions.back') }}</span>
            </a>
        @endif

        {{-- Export & Import placed side-by-side at the top across all pages --}}
        @if ($exportUrl)
            <x-admin.export-csv :href="$exportUrl" :label="$exportLabel" />
        @endif

        @if ($importUrl)
            <div x-data="{ showImportModal: false }" style="display: inline-block;">
                <button type="button" class="btn btn-secondary" @click="showImportModal = true">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                    <span>{{ $importLabel ?? __('admin.actions.import_csv') }}</span>
                </button>

                <template x-teleport="body">
                    <div x-show="showImportModal" x-cloak style="position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 20px;">
                        <div @click.away="showImportModal = false" class="card" style="width: 100%; max-width: 480px; box-shadow: var(--fi-shadow-lg); border: 1px solid var(--fi-border-strong); background: #ffffff; border-radius: var(--fi-radius-lg); overflow: hidden; margin: 0;">
                            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 18px; border-bottom: 1px solid var(--fi-border);">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--fi-primary);"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                    <h3 class="card-title" style="margin: 0; font-size: 14px; font-weight: 700;">{{ $importLabel ?? __('admin.actions.import_csv') }}</h3>
                                </div>
                                <button type="button" @click="showImportModal = false" style="background: none; border: none; cursor: pointer; color: var(--fi-muted); font-size: 18px; line-height: 1;" title="Đóng">&times;</button>
                            </div>
                            <div class="card-body" style="padding: 20px;">
                                <form method="post" action="{{ $importUrl }}" enctype="multipart/form-data">
                                    @csrf
                                    <div style="margin-bottom: 16px;">
                                        <label style="display: block; font-size: 12.5px; font-weight: 600; color: var(--fi-text); margin-bottom: 6px;">Chọn tệp CSV dữ liệu:</label>
                                        <input type="file" name="file" accept=".csv,text/csv" required class="form-control" style="padding: 7px 10px;">
                                        <small class="field-hint" style="display: block; margin-top: 6px; color: var(--fi-muted); font-size: 11.5px;">Hỗ trợ tệp định dạng .csv (tối đa 5MB). Dữ liệu sẽ được tự động ánh xạ theo tiêu đề cột.</small>
                                    </div>
                                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                                        <button type="button" class="btn btn-secondary" @click="showImportModal = false">{{ __('admin.actions.cancel') }}</button>
                                        <button type="submit" class="btn btn-primary">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                            <span>Tải lên & Nhập</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        @endif

        @if ($createUrl)
            <a class="btn btn-primary" href="{{ $createUrl }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                <span>{{ $createLabel ?? __('admin.actions.create') }}</span>
            </a>
        @endif
    </div>
</div>
