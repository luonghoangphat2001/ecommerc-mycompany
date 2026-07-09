@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            <p class="page-description">{{ __('admin.form.description') }}</p>
        </div>
        <div class="actions">
            <a class="btn btn-secondary" href="{{ route($routePrefix . '.index') }}">{{ __('admin.actions.back') }}</a>
        </div>
    </div>

    <div class="card">
        <form method="post" action="{{ $record ? route($routePrefix . '.update', $record->id) : route($routePrefix . '.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($record)
                @method('put')
            @endif

            <div class="form-grid">
                @foreach ($fields as $name => $field)
                    @php
                        $type = $field['type'] ?? 'text';
                        $defaultValue = $formData[$name] ?? data_get($record, $name);
                        $displayValue = is_array($defaultValue) ? json_encode($defaultValue, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $defaultValue;
                        
                        $isWideDefault = in_array($type, ['textarea', 'editor', 'multiselect', 'tree-select', 'checkboxgroup', 'image', 'tags-checkboxes'], true);
                        $colspan = $field['colspan'] ?? null;
                        if (!$colspan) {
                            $isWide = $field['isWide'] ?? $isWideDefault;
                            $colspan = $isWide ? '1 / -1' : 'span 1';
                        } else {
                            $colspan = $colspan === 'full' ? '1 / -1' : 'span ' . $colspan;
                        }
                    @endphp

                    <div class="form-row" style="grid-column: {{ $colspan }};">
                        <label for="{{ $name }}">{{ $field['label'] ?? $name }}</label>

                        @if ($type === 'textarea')
                            <textarea id="{{ $name }}" name="{{ $name }}" rows="6">{{ old($name, $displayValue) }}</textarea>
                        @elseif ($type === 'editor')
                            <textarea id="{{ $name }}" name="{{ $name }}" class="ckeditor-input">{{ old($name, $displayValue) }}</textarea>
                        @elseif ($type === 'select')
                            <select id="{{ $name }}" name="{{ $name }}">
                                @foreach (($field['options'] ?? []) as $value => $label)
                                    <option value="{{ $value }}" @selected((string) old($name, $displayValue) === (string) $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @elseif ($type === 'multiselect')
                            @php($selected = old($name, $formData[$name] ?? []))
                            <select id="{{ $name }}" name="{{ $name }}[]" multiple size="7">
                                @foreach (($field['options'] ?? []) as $value => $label)
                                    <option value="{{ $value }}" @selected(in_array((string) $value, array_map('strval', (array) $selected), true))>{{ $label }}</option>
                                @endforeach
                            </select>
                        @elseif ($type === 'tree-select')
                            @php($selected = old($name, $formData[$name] ?? []))
                            <div class="tree-select-wrapper" style="max-height: 250px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                @include('admin.crud.tree_node', ['nodes' => $field['tree_nodes'] ?? [], 'level' => 0, 'name' => $name, 'selected' => $selected])
                            </div>
                        @elseif ($type === 'checkboxgroup')
                            @php($selected = old($name, $formData[$name] ?? []))
                            <div class="checkbox-grid" id="{{ $name }}">
                                @foreach (($field['options'] ?? []) as $value => $label)
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="{{ $name }}[]" value="{{ $value }}" @checked(in_array((string) $value, array_map('strval', (array) $selected), true))>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @elseif ($type === 'image')
                            <div class="image-upload-wrapper">
                                <input type="file" id="{{ $name }}" name="{{ $name }}" accept="image/*" onchange="previewImage(this, 'preview-{{ $name }}')">
                                <div class="image-preview" style="margin-top: 10px;">
                                    @php($imgSrc = old($name, $displayValue))
                                    @if($imgSrc)
                                        <img id="preview-{{ $name }}" src="{{ Str::startsWith($imgSrc, ['http', '/']) ? $imgSrc : asset('storage/' . $imgSrc) }}" style="max-height: 200px; max-width: 100%; object-fit: contain;">
                                    @else
                                        <img id="preview-{{ $name }}" style="max-height: 200px; max-width: 100%; object-fit: contain; display: none;">
                                    @endif
                                </div>
                            </div>
                        @elseif ($type === 'tags-checkboxes')
                            @php($selected = old($name, $formData[$name] ?? []))
                            <div class="tree-select-wrapper" style="max-height: 250px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    @foreach (($field['options'] ?? []) as $value => $label)
                                        <label class="checkbox-item" style="margin-bottom: 0;">
                                            <input type="checkbox" name="{{ $name }}[]" value="{{ $value }}" @checked(in_array((string) $value, array_map('strval', (array) $selected), true))>
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @elseif ($type === 'tags')
                        @else
                            <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $displayValue) }}">
                        @endif

                        @if (!empty($field['hint']))
                            <small class="field-hint">{{ $field['hint'] }}</small>
                        @endif

                        @error($name)
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="form-footer">
                <a class="btn btn-secondary" href="{{ route($routePrefix . '.index') }}">{{ __('admin.actions.cancel') }}</a>
                <button class="btn" type="submit">{{ __('admin.actions.save') }}</button>
            </div>
        </form>
    </div>

    @if($record && method_exists($record, 'comments'))
        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <h3>Comments</h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tác giả</th>
                            <th>Nội dung</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($record->comments as $comment)
                            <tr>
                                <td>{{ $comment->author->name ?? $comment->author_name ?? 'Khách' }}</td>
                                <td>{{ Str::limit($comment->content, 100) }}</td>
                                <td>{{ $comment->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $comment->is_approved ?? $comment->status ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Chưa có bình luận nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const editors = document.querySelectorAll('.ckeditor-input');
        editors.forEach(editor => {
            ClassicEditor
                .create(editor)
                .catch(error => {
                    console.error(error);
                });
        });
    });

    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    }
</script>
@endpush
