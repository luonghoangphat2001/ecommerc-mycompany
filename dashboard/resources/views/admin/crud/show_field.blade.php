@php
    $isEmpty = is_null($value) || $value === '';
    if (is_array($value) && empty($value)) {
        $isEmpty = true;
    }
    $compact = $compact ?? false;
@endphp

@if ($isEmpty)
    <span class="val-empty">Trống</span>
@elseif (isset($record) && $record instanceof \Spatie\Permission\Models\Permission && isset($name) && $name === 'name')
    <span class="badge bg-blue" style="font-weight: 600;">{{ \App\Support\AdminLabel::permission($value) }}</span>
    <small class="cell-muted" style="display:block; font-size: 11px; margin-top: 2px;">code: {{ $value }}</small>
@elseif (isset($record) && $record instanceof \Spatie\Permission\Models\Role && isset($name) && $name === 'name')
    <span class="badge bg-blue" style="font-weight: 600;">{{ \App\Support\AdminLabel::role($value) }}</span>
    <small class="cell-muted" style="display:block; font-size: 11px; margin-top: 2px;">code: {{ $value }}</small>
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
        <img src="{{ $imageUrl }}" alt="Preview" class="preview-img" style="{{ $compact ? 'max-height: 40px; border-radius: 4px;' : '' }}">
    </a>
@elseif ($type === 'editor')
    @if ($compact)
        <span class="val-empty">Nội dung HTML</span>
    @else
        <div class="val-html">
            {!! $value !!}
        </div>
    @endif
@elseif ($type === 'boolean' || $type === 'select')
    @if (isset($field['options']) && array_key_exists($value, $field['options']))
        {{ $field['options'][$value] }}
    @elseif (is_bool($value) || $value === '1' || $value === '0')
        <span class="badge {{ $value ? 'bg-green' : 'bg-red' }}">
            {{ $value ? 'Có' : 'Không' }}
        </span>
    @else
        {{ $value }}
    @endif
@elseif (is_array($value))
    @if ($compact)
        {{ count($value) }} mục
    @else
        <div class="val-html" style="font-family: monospace; white-space: pre-wrap; font-size: 12px;">{{ json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</div>
    @endif
@else
    {!! nl2br(e(\App\Support\AdminValue::format($value, $compact))) !!}
@endif
