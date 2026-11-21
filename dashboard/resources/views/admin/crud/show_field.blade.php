@if ($value === null || $value === '')
    <span class="val-empty">—</span>
@elseif (in_array($name, ['shop_brand_id', 'brand'], true))
    <span style="font-weight: 600; color: var(--fi-text);">{{ $brandLabel ?? $value }}</span>
@elseif (in_array($name, ['price', 'old_price', 'sale_price', 'cost'], true) && is_numeric($value))
    <span class="mono" style="font-weight: 700; color: var(--fi-text); font-size: 13.5px;">{{ number_format((float) $value) }} <span style="font-size: 11px; font-weight: 500; color: var(--fi-muted);">VND</span></span>
@elseif ($record instanceof \Spatie\Permission\Models\Permission && $name === 'name')
    <span class="badge bg-blue">{{ \App\Support\Admin::permissionLabel($value) }}</span>
    <small class="cell-muted mono" style="display: block; font-size: 11px; margin-top: 2px;">{{ $value }}</small>
@elseif ($record instanceof \Spatie\Permission\Models\Role && $name === 'name')
    <span class="badge bg-blue">{{ \App\Support\Admin::roleLabel($value) }}</span>
    <small class="cell-muted mono" style="display: block; font-size: 11px; margin-top: 2px;">{{ $value }}</small>
@elseif ($type === 'image')
    @if ($imageUrl ?? null)
        <a href="{{ $imageUrl }}" target="_blank">
            <img src="{{ $imageUrl }}" alt="Preview" class="preview-img" style="{{ $compact ? 'width: 38px; height: 38px; object-fit: cover; border-radius: 6px; padding: 0;' : '' }}">
        </a>
    @else
        <span class="val-empty">—</span>
    @endif
@elseif ($type === 'editor')
    @if ($compact)
        <span class="badge badge-secondary">HTML</span>
    @else
        <div class="val-html">{!! $value !!}</div>
    @endif
@elseif ($type === 'boolean' || $type === 'select')
    @if (isset($field['options']) && (is_string($value) || is_int($value)) && array_key_exists($value, $field['options']))
        <span class="badge badge-{{ is_string($value) ? $value : 'secondary' }}">{{ $field['options'][$value] }}</span>
    @elseif (is_bool($value) || $value === '1' || $value === '0')
        <span class="badge {{ $value ? 'bg-green' : 'bg-gray' }}">{{ $value ? __('admin.common.enabled') : __('admin.common.disabled') }}</span>
    @else
        <span class="badge badge-secondary">{{ \App\Support\Admin::format($value, true) }}</span>
    @endif
@elseif ($type === 'blocks')
    @if ($compact)
        <span class="badge badge-secondary">{{ \App\Support\Admin::format($value, true) }}</span>
    @else
        <div class="val-html mono" style="white-space: pre-wrap; font-size: 12px;">{{ \App\Support\Admin::format($value) }}</div>
    @endif
@elseif (is_array($value))
    @if ($compact)
        <span class="badge badge-secondary">{{ \App\Support\Admin::format($value, true) }}</span>
    @else
        <div class="val-html mono" style="white-space: pre-wrap; font-size: 12px;">{{ \App\Support\Admin::format($value) }}</div>
    @endif
@else
    {!! nl2br(e(\App\Support\Admin::format($value, $compact))) !!}
@endif
