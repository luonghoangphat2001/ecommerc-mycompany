@props([
    'action' => null,
    'resetUrl' => null,
    'method' => 'get',
    'submitLabel' => null,
    'resetLabel' => null,
])

<form method="{{ $method }}" @if($action) action="{{ $action }}" @endif class="admin-table-toolbar">
    <div class="admin-toolbar-filters">
        {{ $slot }}
    </div>

    <div class="admin-toolbar-actions">
        @if (isset($right))
            {{ $right }}
        @endif

        @if ($resetUrl)
            <a href="{{ $resetUrl }}" class="btn btn-secondary btn-sm">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                <span>{{ $resetLabel ?? __('admin.actions.reset') }}</span>
            </a>
        @endif

        <button class="btn btn-primary btn-sm" type="submit">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            <span>{{ $submitLabel ?? __('admin.actions.filter_data') }}</span>
        </button>
    </div>
</form>

