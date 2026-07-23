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
                            @php(
                                $selected = old($name, isset($formData[$name]) ? $formData[$name] : (is_iterable(data_get($record, $name)) ? collect(data_get($record, $name))->pluck('name')->toArray() : []))
                            )
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
                                    @php
                                        $imgSrc = old($name, $displayValue);
                                        if (is_numeric($imgSrc)) {
                                            $media = \Awcodes\Curator\Models\Media::find($imgSrc);
                                            $imgSrc = $media ? $media->path : $imgSrc;
                                        }
                                    @endphp
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
                        @elseif ($type === 'blocks')
                            @php($blocksData = old($name, $displayValue))
                            @php
                                if (is_string($blocksData)) {
                                    $blocksData = json_decode($blocksData, true);
                                    if (json_last_error() !== JSON_ERROR_NONE) $blocksData = [];
                                }
                            @endphp
                            <div x-data="blockManager({{ json_encode($blocksData ?: []) }})" class="blocks-editor-wrapper mt-2">
                                <input type="hidden" name="{{ $name }}" :value="JSON.stringify(blocks)">
                                
                                <div id="sortable-{{ $name }}" class="blocks-container" style="display: flex; flex-direction: column; gap: 16px;">
                                    <template x-for="(block, index) in blocks" :key="block._key || index">
                                        <div class="block-item" style="border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                            
                                            <!-- Block Header (Filament Style) -->
                                            <div style="background: #f8fafc; padding: 10px 16px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <div class="drag-handle cursor-move" style="cursor: grab; color: #94a3b8; user-select: none; display: flex; align-items: center;" title="Kéo để di chuyển">
                                                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                                                    </div>
                                                    <span style="font-weight: 600; font-size: 14px; color: #334155;" x-text="getBlockLabel(block.type)"></span>
                                                </div>
                                                <button type="button" @click="removeBlock(index)" style="color: #64748b; background: none; border: none; cursor: pointer; padding: 4px; border-radius: 4px;" onmouseover="this.style.color='#ef4444'; this.style.backgroundColor='#fee2e2'" onmouseout="this.style.color='#64748b'; this.style.backgroundColor='transparent'" title="Xóa Block">
                                                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>

                                            <!-- Block Body -->
                                            <div class="block-content" style="padding: 16px;">
                                                
                                                <!-- Chung cho tất cả các block -->
                                                <div style="margin-bottom: 16px;">
                                                    <label style="display:block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px;">Caption / Chú thích (Tùy chọn)</label>
                                                    <input type="text" x-model="block.caption" placeholder="Nhập chú thích..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);">
                                                </div>

                                                <!-- Text Block -->
                                                <template x-if="block.type === 'text'">
                                                    <div>
                                                        <label style="display:block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px;">Nội dung Text</label>
                                                        <input type="text" x-model="block.value" placeholder="Nhập tiêu đề hoặc văn bản ngắn..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);">
                                                    </div>
                                                </template>

                                                <!-- Textarea Block -->
                                                <template x-if="block.type === 'textarea'">
                                                    <div>
                                                        <label style="display:block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px;">Đoạn văn (Textarea)</label>
                                                        <textarea x-model="block.value" rows="4" placeholder="Nhập nội dung dài..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);"></textarea>
                                                    </div>
                                                </template>

                                                <!-- Code Editor Block -->
                                                <template x-if="block.type === 'code-editor'">
                                                    <div>
                                                        <label style="display:block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px;">HTML / Script Code</label>
                                                        <textarea x-model="block.value" rows="6" placeholder="<script>...</script>" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; background: #1e293b; color: #e2e8f0; font-family: monospace;"></textarea>
                                                    </div>
                                                </template>

                                                <!-- Media Block -->
                                                <template x-if="block.type === 'media'">
                                                    <div>
                                                        <label style="display:block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px;">URL Hình ảnh / Video</label>
                                                        <div style="display: flex; gap: 10px;">
                                                            <input type="text" x-model="block.value" placeholder="https://..." style="flex: 1; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);">
                                                            <button type="button" @click="alert('Tính năng chọn ảnh từ thư viện sẽ được gọi ở đây')" style="padding: 8px 16px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; font-weight: 600; color: #475569; cursor: pointer;">
                                                                Duyệt
                                                            </button>
                                                        </div>
                                                        <template x-if="block.value">
                                                            <div style="margin-top: 10px; border: 1px solid #e2e8f0; padding: 4px; border-radius: 6px; display: inline-block; background: #f8fafc;">
                                                                <img :src="block.value" style="max-height: 150px; border-radius: 4px; object-fit: cover;" x-on:error="$event.target.style.display='none'">
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                                
                                                <!-- Others... -->
                                                <template x-if="block.type !== 'text' && block.type !== 'textarea' && block.type !== 'code-editor' && block.type !== 'media'">
                                                    <div>
                                                        <label style="display:block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px;" x-text="'Giá trị (' + block.type + ')'"></label>
                                                        <input type="text" x-model="block.value" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);">
                                                    </div>
                                                </template>

                                            </div>
                                        </div>
                                    </template>
                                </div>
                                
                                <!-- Add Block Button (Filament Style) -->
                                <div style="margin-top: 24px; text-align: center; position: relative;">
                                    <div style="position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: #e2e8f0; z-index: 1;"></div>
                                    
                                    <div style="position: relative; z-index: 2; display: inline-block;" @click.away="showBlockMenu = false">
                                        <button type="button" @click="showBlockMenu = !showBlockMenu" style="background: #fff; border: 1px solid #e2e8f0; padding: 8px 16px; border-radius: 99px; font-weight: 600; color: #0f172a; font-size: 13px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05); display: inline-flex; align-items: center; gap: 6px;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                                            <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                            Thêm Block
                                        </button>
                                        
                                        <!-- Dropdown Menu -->
                                        <div x-show="showBlockMenu" style="display: none; position: absolute; top: 100%; left: 50%; transform: translateX(-50%); margin-top: 8px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); width: 220px; text-align: left; overflow: hidden;">
                                            <template x-for="type in blockTypes" :key="type.value">
                                                <button type="button" @click="addBlock(type.value); showBlockMenu = false" style="display: block; width: 100%; text-align: left; padding: 10px 16px; border: none; background: none; cursor: pointer; font-size: 13px; color: #334155; border-bottom: 1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='none'">
                                                    <span x-text="type.label"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
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
<!-- Nhúng AlpineJS và SortableJS cho Block Builder -->
<script defer src="{{ asset('vendor/alpinejs/alpine.min.js') }}"></script>
<script src="{{ asset('vendor/sortablejs/sortable.min.js') }}"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('blockManager', (initialBlocks) => ({
            blocks: Array.isArray(initialBlocks) ? initialBlocks.map(b => ({...b, _key: Math.random().toString(36).substr(2, 9)})) : [],
            showBlockMenu: false,
            blockTypes: [
                { value: 'text', label: 'Văn bản ngắn (Text)' },
                { value: 'textarea', label: 'Đoạn văn bản (Textarea)' },
                { value: 'media', label: 'Hình ảnh / Video (Media)' },
                { value: 'gallery', label: 'Thư viện ảnh (Gallery)' },
                { value: 'code-editor', label: 'Mã nhúng (Code Editor)' },
            ],
            init() {
                this.$nextTick(() => {
                    const containers = this.$el.querySelectorAll('.blocks-container');
                    containers.forEach(el => {
                        Sortable.create(el, {
                            handle: '.drag-handle',
                            animation: 150,
                            ghostClass: 'opacity-50',
                            onEnd: (evt) => {
                                const movedItem = this.blocks.splice(evt.oldIndex, 1)[0];
                                this.blocks.splice(evt.newIndex, 0, movedItem);
                            }
                        });
                    });
                });
            },
            getBlockLabel(typeValue) {
                const type = this.blockTypes.find(t => t.value === typeValue);
                return type ? type.label : typeValue;
            },
            addBlock(type = 'text') {
                this.blocks.push({
                    _key: Math.random().toString(36).substr(2, 9),
                    type: type,
                    value: '',
                    caption: ''
                });
            },
            removeBlock(index) {
                if (confirm('Bạn có chắc muốn xóa block này?')) {
                    this.blocks.splice(index, 1);
                }
            }
        }));
    });
</script>

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
