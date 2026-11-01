@php
    $isEmpty = is_null($value) || $value === '';
    if (is_array($value) && empty($value)) {
        $isEmpty = true;
    }
    $compact = $compact ?? false;

    // Resolve translatable value according to active language (do not show 2 languages stacked)
    $displayVal = $value;
    if (is_string($value) && str_starts_with(trim($value), '{') && str_ends_with(trim($value), '}')) {
        $decoded = json_decode($value, true);
        if (is_array($decoded) && (isset($decoded['vi']) || isset($decoded['en']))) {
            $value = $decoded;
        }
    }
    if (is_array($value) && (isset($value['vi']) || isset($value['en']))) {
        $currentLocale = app()->getLocale();
        $displayVal = $value[$currentLocale] ?? $value['vi'] ?? $value['en'] ?? reset($value);
    } elseif (isset($record) && is_object($record) && method_exists($record, 'getTranslation') && isset($name)) {
        $isTranslatable = (method_exists($record, 'isTranslatableAttribute') && $record->isTranslatableAttribute($name))
            || in_array($name, $translatableFields ?? [], true)
            || (property_exists($record, 'translatable') && is_array($record->translatable) && in_array($name, $record->translatable, true));
        if ($isTranslatable) {
            $currentLocale = app()->getLocale();
            $displayVal = $record->getTranslation($name, $currentLocale, false) ?: ($record->getTranslation($name, config('app.fallback_locale', 'vi'), false) ?: $record->{$name});
        }
    }
    $value = $displayVal;
    if (is_null($value) || $value === '') {
        $isEmpty = true;
    }
@endphp

@if ($isEmpty)
    <span class="val-empty">—</span>
@elseif (isset($name) && ($name === 'shop_brand_id' || $name === 'brand'))
    @php
        $brandObj = (isset($record) && method_exists($record, 'brand') && $record->brand) ? $record->brand : \App\Models\Brand::find($value);
    @endphp
    <span style="font-weight: 600; color: var(--fi-text);">{{ $brandObj ? $brandObj->name : ($value ?: '—') }}</span>
@elseif (isset($name) && in_array($name, ['price', 'old_price', 'sale_price', 'cost'], true) && is_numeric($value))
    <span class="mono" style="font-weight: 700; color: var(--fi-text); font-size: 13.5px;">{{ number_format((float) $value) }} <span style="font-size: 11px; font-weight: 500; color: var(--fi-muted);">VND</span></span>
@elseif (isset($record) && $record instanceof \Spatie\Permission\Models\Permission && isset($name) && $name === 'name')
    <span class="badge bg-blue">{{ \App\Support\AdminLabel::permission($value) }}</span>
    <small class="cell-muted mono" style="display: block; font-size: 11px; margin-top: 2px;">{{ $value }}</small>
@elseif (isset($record) && $record instanceof \Spatie\Permission\Models\Role && isset($name) && $name === 'name')
    <span class="badge bg-blue">{{ \App\Support\AdminLabel::role($value) }}</span>
    <small class="cell-muted mono" style="display: block; font-size: 11px; margin-top: 2px;">{{ $value }}</small>
@elseif ($type === 'image')
    @php
        $imagePath = $value;
        if (is_numeric($value) && method_exists($record, 'featuredImage') && $record->featuredImage) {
            $imagePath = $record->featuredImage->path;
        } elseif (is_numeric($value)) {
            $media = \Awcodes\Curator\Models\Media::find($value);
            $imagePath = $media ? $media->path : $value;
        }
        $imageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath);
    @endphp
    <a href="{{ $imageUrl }}" target="_blank">
        <img src="{{ $imageUrl }}" alt="Preview" class="preview-img" style="{{ $compact ? 'width: 38px; height: 38px; object-fit: cover; border-radius: 6px; padding: 0;' : '' }}">
    </a>
@elseif ($type === 'editor')
    @if ($compact)
        <span class="badge badge-secondary">HTML</span>
    @else
        <div class="val-html">
            {!! $value !!}
        </div>
    @endif
@elseif ($type === 'boolean' || $type === 'select')
    @php
        $lookupKey = $value instanceof \BackedEnum ? $value->value : $value;
    @endphp
    @if (isset($field['options']) && (is_string($lookupKey) || is_int($lookupKey)) && array_key_exists($lookupKey, $field['options']))
        <span class="badge badge-{{ is_string($lookupKey) ? $lookupKey : 'secondary' }}">{{ $field['options'][$lookupKey] }}</span>
    @elseif (is_bool($value) || $value === '1' || $value === '0')
        <span class="badge {{ $value ? 'bg-green' : 'bg-gray' }}">
            {{ $value ? __('admin.common.enabled') : __('admin.common.disabled') }}
        </span>
    @else
        <span class="badge badge-secondary">{{ $value instanceof \BackedEnum ? $value->value : $value }}</span>
    @endif
@elseif (is_array($value))
    @if ($compact)
        <span class="badge badge-secondary">{{ count($value) }} {{ __('admin.common.items') }}</span>
    @else
        <div class="val-html mono" style="white-space: pre-wrap; font-size: 12px;">{{ json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</div>
    @endif
@else
    {!! nl2br(e(\App\Support\AdminValue::format($value, $compact))) !!}
@endif
