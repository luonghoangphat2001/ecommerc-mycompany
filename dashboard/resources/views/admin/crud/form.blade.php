@extends('admin.layouts.app', ['title' => $title])

@section('content')
@php
    // Categorize fields into dedicated groups for clean enterprise layout
    $sidebarMediaFields = [];
    $sidebarTaxonomyFields = [];
    $sidebarMetaFields = [];
    $seoFields = [];
    $pricingFields = [];
    $mainFields = [];

    $metaFieldKeys = [
        'is_visible', 'status', 'post_type', 'type', 'featured', 'is_featured',
        'published_at', 'author_id', 'shop_brand_id', 'department_id',
        'apply_tax', 'backorder', 'requires_shipping', 'is_active', 'is_urgent'
    ];
    $pricingFieldKeys = [
        'price', 'old_price', 'sale_price', 'sale_start_date', 'sale_end_date',
        'cost', 'qty', 'security_stock', 'stock_quantity', 'low_stock_threshold',
        'amount', 'base_salary', 'allowance', 'tax', 'insurance', 'net_salary'
    ];

    foreach ($fields as $name => $field) {
        $type = $field['type'] ?? 'text';
        if ($type === 'image') {
            $sidebarMediaFields[$name] = $field;
        } elseif (in_array($name, ['categories', 'tags'], true) || in_array($type, ['tree-select', 'tags-checkboxes'], true)) {
            $sidebarTaxonomyFields[$name] = $field;
        } elseif (in_array($name, ['seo_title', 'seo_description'], true) || \Illuminate\Support\Str::startsWith($name, 'seo_')) {
            $seoFields[$name] = $field;
        } elseif (in_array($name, $metaFieldKeys, true)) {
            $sidebarMetaFields[$name] = $field;
        } elseif (in_array($name, $pricingFieldKeys, true)) {
            $pricingFields[$name] = $field;
        } else {
            $mainFields[$name] = $field;
        }
    }

    // Sort main fields so title/name & slug/sku appear at the very top
    $topKeys = ['title', 'name', 'slug', 'sku'];
    $orderedMain = [];
    foreach ($topKeys as $k) {
        if (isset($mainFields[$k])) {
            $orderedMain[$k] = $mainFields[$k];
            unset($mainFields[$k]);
        }
    }
    $mainFields = array_merge($orderedMain, $mainFields);

    $hasSidebar = !empty($sidebarMetaFields) || !empty($sidebarMediaFields) || !empty($sidebarTaxonomyFields);
@endphp

    <x-admin.page-header
        :title="$title"
        :description="$record ? 'Chỉnh sửa và cập nhật thông tin bản ghi #' . $record->id : 'Tạo mới bản ghi trong hệ thống'"
        :backUrl="route($routePrefix . '.index')"
    >
        <x-slot:actions>
            <a class="btn btn-secondary" href="{{ route($routePrefix . '.index') }}">
                &larr; {{ __('admin.actions.cancel') }}
            </a>
            @if ($record && \Illuminate\Support\Facades\Route::has($routePrefix . '.show'))
                <a class="btn btn-secondary" href="{{ route($routePrefix . '.show', $record->id) }}">
                    Xem chi tiết
                </a>
            @endif
            <button class="btn btn-primary" type="submit" form="crud-main-form">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                {{ __('admin.actions.save') }}
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    @if (!empty($tabs))
        <div style="margin-bottom: var(--fi-space-4, 16px);">
            <x-admin.tabs
                mode="link"
                :active="$activeTab ?? ''"
                :tabs="$tabs"
            />
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div style="background: var(--fi-danger-bg); color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; border: 1px solid var(--fi-danger-border);">
            <div style="font-weight: 700; font-size: 13px; margin-bottom: 4px;">Vui lòng kiểm tra lại dữ liệu đã nhập:</div>
            <ul style="margin: 0; padding-left: 20px; font-size: 12.5px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="crud-main-form" method="post" action="{{ $record ? route($routePrefix . '.update', $record->id) : route($routePrefix . '.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($record)
            @method('put')
        @endif

        <div class="{{ $hasSidebar ? 'edit-layout' : '' }}">
            <!-- ================= MAIN CONTENT COLUMN ================= -->
            <div class="{{ $hasSidebar ? 'show-main' : '' }}">
                <!-- Card 1: Thông tin nội dung chính -->
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--fi-primary);"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            <h3 class="card-title">Thông tin nội dung chính</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            @foreach ($mainFields as $name => $field)
                                @php
                                    $type = $field['type'] ?? 'text';
                                    $defaultValue = $formData[$name] ?? data_get($record, $name);
                                    if ($defaultValue instanceof \DateTimeInterface) {
                                        $displayValue = $type === 'datetime-local' ? $defaultValue->format('Y-m-d\TH:i') : $defaultValue->format('Y-m-d');
                                    } elseif ($defaultValue instanceof \BackedEnum) {
                                        $displayValue = $defaultValue->value;
                                    } elseif (is_array($defaultValue)) {
                                        $displayValue = json_encode($defaultValue, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                                    } else {
                                        $displayValue = $defaultValue;
                                    }

                                    $isWide = in_array($type, ['textarea', 'editor', 'blocks'], true) || in_array($name, ['content', 'description', 'blocks'], true) || ($field['colspan'] ?? null) === 'full';
                                @endphp

                                <div class="form-row" style="{{ $isWide ? 'grid-column: 1 / -1;' : '' }}">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                        <label for="{{ $name }}" style="margin: 0; font-weight: 600;">
                                            {{ $field['label'] ?? $name }}
                                            @if(!empty($field['rules']) && in_array('required', (array) $field['rules'], true))
                                                <span style="color: #ef4444;">*</span>
                                            @endif
                                        </label>
                                        @if($name === 'slug')
                                            <button type="button" onclick="generateSlugFromTitle()" class="link-action" style="font-size: 11.5px; color: var(--fi-primary); cursor: pointer; border: none; background: none; padding: 0;">
                                                &olarr; Tạo tự động từ tiêu đề
                                            </button>
                                        @endif
                                    </div>

                                    @if ($type === 'textarea')
                                        <textarea id="{{ $name }}" name="{{ $name }}" rows="4" class="form-control">{{ old($name, $displayValue) }}</textarea>
                                    @elseif ($type === 'editor')
                                        <textarea id="{{ $name }}" name="{{ $name }}" class="ckeditor-input">{{ old($name, $displayValue) }}</textarea>
                                    @elseif ($type === 'select')
                                        <select id="{{ $name }}" name="{{ $name }}" class="form-control">
                                            @foreach (($field['options'] ?? []) as $value => $label)
                                                <option value="{{ $value }}" @selected((string) old($name, $displayValue) === (string) $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    @elseif ($type === 'multiselect')
                                        @php
                                            $selected = old($name, isset($formData[$name]) ? $formData[$name] : (is_iterable(data_get($record, $name)) ? collect(data_get($record, $name))->pluck('name')->toArray() : []));
                                        @endphp
                                        <select id="{{ $name }}" name="{{ $name }}[]" multiple size="6" style="min-height: 130px;" class="form-control">
                                            @foreach (($field['options'] ?? []) as $value => $label)
                                                <option value="{{ $value }}" @selected(in_array((string) $value, array_map('strval', (array) $selected), true))>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    @elseif ($type === 'checkboxgroup')
                                        @php
                                            $selected = old($name, $formData[$name] ?? []);
                                        @endphp
                                        <div class="checkbox-grid" id="{{ $name }}">
                                            @foreach (($field['options'] ?? []) as $value => $label)
                                                <label class="checkbox-item">
                                                    <input type="checkbox" name="{{ $name }}[]" value="{{ $value }}" @checked(in_array((string) $value, array_map('strval', (array) $selected), true))>
                                                    <span>{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif ($type === 'blocks')
                                        @php
                                            $blocksData = old($name, $displayValue);
                                            if (is_string($blocksData)) {
                                                $blocksData = json_decode($blocksData, true);
                                                if (json_last_error() !== JSON_ERROR_NONE) $blocksData = [];
                                            }
                                        @endphp
                                        <div x-data="blockManager({{ json_encode($blocksData ?: []) }})" class="blocks-editor-wrapper" style="margin-top: 6px;">
                                            <input type="hidden" name="{{ $name }}" :value="JSON.stringify(serializeBlocks())">

                                            <div id="sortable-{{ $name }}" class="blocks-container" style="display: flex; flex-direction: column; gap: 14px;">
                                                <template x-for="(block, index) in blocks" :key="block._key || index">
                                                    <div class="block-item" style="border: 1px solid var(--fi-border); border-radius: 8px; background: #fff; overflow: hidden;">
                                                        <div style="background: #f8fafc; padding: 10px 14px; border-bottom: 1px solid var(--fi-border); display: flex; justify-content: space-between; align-items: center;">
                                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                                <div class="drag-handle" style="cursor: grab; color: #94a3b8; user-select: none; display: flex; align-items: center;" title="Kéo để di chuyển">
                                                                    <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                                                                </div>
                                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                                    <span style="font-weight: 700; font-size: 11.5px; color: #475569; background: #e2e8f0; border-radius: 4px; padding: 2px 7px;" x-text="'#' + (index + 1)"></span>
                                                                    <span style="font-weight: 600; font-size: 13px; color: var(--fi-text);" x-text="getBlockLabel(block.type)"></span>
                                                                </div>
                                                            </div>
                                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                                <button type="button" @click="moveBlockUp(index)" :disabled="index === 0" class="btn btn-secondary btn-sm" style="padding: 2px 7px; font-size: 11px;" title="Di chuyển lên">&uarr;</button>
                                                                <button type="button" @click="moveBlockDown(index)" :disabled="index === blocks.length - 1" class="btn btn-secondary btn-sm" style="padding: 2px 7px; font-size: 11px;" title="Di chuyển xuống">&darr;</button>
                                                                <button type="button" @click="removeBlock(index)" class="link-action link-danger" style="font-size: 12px; margin-left: 6px;" title="Xóa Block">Xóa</button>
                                                            </div>
                                                        </div>

                                                        <div class="block-content" style="padding: 14px;">
                                                            <!-- Block Type: Editor / Markdown (Rich Text Editor - Word Copy/Paste) -->
                                                            <template x-if="block.type === 'editor' || block.type === 'markdown'">
                                                                <div>
                                                                    <div style="font-weight: 600; font-size: 12.5px; color: #334155; margin-bottom: 6px;">
                                                                        Soạn thảo nội dung (Định dạng phong phú & dán trực tiếp từ Microsoft Word):
                                                                    </div>
                                                                    <div x-init="$nextTick(() => initEditor($el, block))" style="min-height: 220px;"></div>
                                                                </div>
                                                            </template>

                                                            <!-- Block Type: Heading (H1, H2, H3, H4, H5, H6) -->
                                                            <template x-if="block.type === 'heading'">
                                                                <div>
                                                                    <label style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 6px; display: block;">Tiêu đề (Heading):</label>
                                                                    <div style="display: flex; gap: 8px;">
                                                                        <select x-model="block.level" class="form-control" style="width: 140px; font-weight: 600; font-size: 13px;">
                                                                            <option value="1">H1 (Tiêu đề 1)</option>
                                                                            <option value="2">H2 (Tiêu đề 2)</option>
                                                                            <option value="3">H3 (Tiêu đề 3)</option>
                                                                            <option value="4">H4 (Tiêu đề 4)</option>
                                                                            <option value="5">H5 (Tiêu đề 5)</option>
                                                                            <option value="6">H6 (Tiêu đề 6)</option>
                                                                        </select>
                                                                        <input type="text" x-model="block.value" class="form-control" placeholder="Nhập nội dung tiêu đề..." style="flex: 1; font-size: 13px;">
                                                                    </div>
                                                                </div>
                                                            </template>

                                                            <!-- Block Type: Media (Hình ảnh tải lên từ máy) -->
                                                            <template x-if="block.type === 'media'">
                                                                <div>
                                                                    <label style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 6px; display: block;">Hình ảnh / Media:</label>
                                                                    <div style="display: flex; align-items: center; gap: 10px;">
                                                                        <label class="btn btn-secondary btn-sm" style="cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
                                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                                                            <span x-text="block.value ? 'Chọn ảnh khác từ máy' : 'Tải ảnh lên từ máy'"></span>
                                                                            <input type="file" accept="image/*" @change="uploadBlockMedia($event, block)" style="display: none;">
                                                                        </label>
                                                                        <span x-show="block._uploading" style="font-size: 12px; color: #0284c7; display: inline-flex; align-items: center; gap: 4px;">
                                                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle><path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path></svg>
                                                                            Đang tải ảnh lên...
                                                                        </span>
                                                                        <span x-show="block.value" style="font-size: 11.5px; color: #64748b; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" x-text="block.value"></span>
                                                                    </div>
                                                                    <template x-if="block.value">
                                                                        <div style="margin-top: 10px; position: relative; display: inline-block;">
                                                                            <img :src="block.value" class="preview-img" style="max-height: 180px; border-radius: 6px; border: 1px solid var(--fi-border); object-fit: cover;" x-on:error="$event.target.style.display='none'">
                                                                            <button type="button" @click="block.value = ''" style="position: absolute; top: 4px; right: 4px; background: rgba(0,0,0,0.6); color: #fff; border: none; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 13px;" title="Xóa ảnh">&times;</button>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </template>

                                                            <!-- Block Type: Gallery (Thư viện nhiều hình ảnh tải lên) -->
                                                            <template x-if="block.type === 'gallery'">
                                                                <div>
                                                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                                                        <label style="font-weight: 600; font-size: 12.5px; color: #334155; margin: 0;">
                                                                            Thư viện hình ảnh (Chọn & tải lên nhiều ảnh):
                                                                        </label>
                                                                        <label class="btn btn-secondary btn-sm" style="cursor: pointer; display: inline-flex; align-items: center; gap: 5px; font-size: 11.5px; font-weight: 600;">
                                                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                                                            + Tải nhiều ảnh từ máy
                                                                            <input type="file" accept="image/*" multiple @change="uploadGalleryImages($event, block)" style="display: none;">
                                                                        </label>
                                                                    </div>

                                                                    <span x-show="block._uploading" style="font-size: 12px; color: #0284c7; display: block; margin-bottom: 8px;">
                                                                        Đang tải các ảnh lên... Vui lòng đợi trong giây lát.
                                                                    </span>

                                                                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 10px;">
                                                                        <template x-for="(img, imgIdx) in block.galleryImages" :key="imgIdx">
                                                                            <div style="position: relative; border-radius: 6px; overflow: hidden; border: 1px solid var(--fi-border); background: #f8fafc; aspect-ratio: 1; display: flex; align-items: center; justify-content: center;">
                                                                                <img :src="img" style="width: 100%; height: 100%; object-fit: cover;" x-on:error="$event.target.style.display='none'">
                                                                                <button type="button" @click="block.galleryImages.splice(imgIdx, 1)" style="position: absolute; top: 4px; right: 4px; background: rgba(0,0,0,0.65); color: #fff; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px;" title="Xóa ảnh">&times;</button>
                                                                            </div>
                                                                        </template>
                                                                    </div>

                                                                    <template x-if="!block.galleryImages || block.galleryImages.length === 0">
                                                                        <div style="padding: 20px; text-align: center; background: #f8fafc; border: 1px dashed var(--fi-border); border-radius: 6px; margin-top: 6px;">
                                                                            <span style="font-size: 12px; color: #94a3b8;">Chưa có ảnh nào trong album. Bấm nút "+ Tải nhiều ảnh từ máy" ở trên để chọn nhiều ảnh cùng lúc.</span>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </template>

                                                            <!-- Block Type: Text -->
                                                            <template x-if="block.type === 'text'">
                                                                <div>
                                                                    <label style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 6px; display: block;">Văn bản ngắn:</label>
                                                                    <input type="text" x-model="block.value" class="form-control" placeholder="Nhập văn bản ngắn...">
                                                                </div>
                                                            </template>

                                                            <!-- Block Type: Textarea -->
                                                            <template x-if="block.type === 'textarea'">
                                                                <div>
                                                                    <label style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 6px; display: block;">Đoạn văn thuần (Textarea):</label>
                                                                    <textarea x-model="block.value" rows="4" class="form-control" placeholder="Nhập nội dung đoạn văn..."></textarea>
                                                                </div>
                                                            </template>

                                                            <!-- Block Type: Code Editor -->
                                                            <template x-if="block.type === 'code-editor'">
                                                                <div>
                                                                    <label style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 6px; display: block;">HTML / Script Code:</label>
                                                                    <textarea x-model="block.value" rows="6" placeholder="<iframe...> hoặc <script>..." class="form-control mono" style="background: #0f172a; color: #e2e8f0; font-family: monospace; font-size: 12.5px;"></textarea>
                                                                </div>
                                                            </template>

                                                            <!-- Fallback for other block types -->
                                                            <template x-if="block.type !== 'editor' && block.type !== 'markdown' && block.type !== 'heading' && block.type !== 'text' && block.type !== 'textarea' && block.type !== 'code-editor' && block.type !== 'media' && block.type !== 'gallery'">
                                                                <div>
                                                                    <label x-text="'Giá trị (' + block.type + ')'" style="font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 6px; display: block;"></label>
                                                                    <input type="text" x-model="block.value" class="form-control">
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>

                                            <div style="margin-top: 18px; padding-top: 14px; border-top: 1px dashed var(--fi-border); display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 8px;">
                                                <span style="font-size: 12px; font-weight: 600; color: #64748b; margin-right: 4px;">Thêm khối nội dung:</span>
                                                <button type="button" @click="addBlock('editor')" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 5px; font-weight: 600; color: #0284c7; background: #f0f9ff; border-color: #bae6fd;">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                                    Soạn thảo văn bản (Word)
                                                </button>
                                                <button type="button" @click="addBlock('heading')" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 5px; font-weight: 500;">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                                    Tiêu đề (Heading)
                                                </button>
                                                <button type="button" @click="addBlock('media')" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 5px; font-weight: 500;">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                                    Hình ảnh (Tải lên)
                                                </button>
                                                <button type="button" @click="addBlock('gallery')" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 5px; font-weight: 500;">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                                    Thư viện ảnh (Gallery)
                                                </button>
                                                <div style="position: relative; display: inline-block;" @click.away="showBlockMenu = false">
                                                    <button type="button" @click="showBlockMenu = !showBlockMenu" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 4px;">
                                                        Khác...
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"></polyline></svg>
                                                    </button>
                                                    <div x-show="showBlockMenu" style="display: none; position: absolute; bottom: calc(100% + 6px); right: 0; background: #fff; border: 1px solid var(--fi-border); border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15); width: 220px; text-align: left; overflow: hidden; z-index: 50;">
                                                        <template x-for="type in blockTypes.filter(t => !['editor', 'heading', 'media', 'gallery'].includes(t.value))" :key="type.value">
                                                            <button type="button" @click="addBlock(type.value); showBlockMenu = false" style="display: block; width: 100%; text-align: left; padding: 9px 12px; border: none; background: none; cursor: pointer; font-size: 12.5px; color: var(--fi-text); border-bottom: 1px solid var(--fi-border-subtle);" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='none'">
                                                                <span x-text="type.label"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $displayValue) }}" class="form-control">
                                    @endif

                                    @if (!empty($field['hint']))
                                        <small class="field-hint">{{ $field['hint'] }}</small>
                                    @endif

                                    @error($name)
                                        <div class="error" style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Card 2: Giá bán & Quản lý kho hàng (Nếu có trường pricing) -->
                @if (!empty($pricingFields))
                    <div class="card">
                        <div class="card-header">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #059669;"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                <h3 class="card-title">Giá bán & Quản lý kho hàng</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-grid">
                                @foreach ($pricingFields as $name => $field)
                                    @if ($name === 'sale_start_date' && isset($pricingFields['sale_end_date']))
                                        @php
                                            $startField = $field;
                                            $endField = $pricingFields['sale_end_date'];
                                            $startVal = $formData['sale_start_date'] ?? data_get($record, 'sale_start_date');
                                            $startDisplay = ($startVal instanceof \DateTimeInterface) ? (($startField['type'] ?? 'datetime-local') === 'datetime-local' ? $startVal->format('Y-m-d\TH:i') : $startVal->format('Y-m-d')) : $startVal;
                                            $endVal = $formData['sale_end_date'] ?? data_get($record, 'sale_end_date');
                                            $endDisplay = ($endVal instanceof \DateTimeInterface) ? (($endField['type'] ?? 'datetime-local') === 'datetime-local' ? $endVal->format('Y-m-d\TH:i') : $endVal->format('Y-m-d')) : $endVal;
                                        @endphp
                                        <div class="form-row-wide" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px;">
                                            <div class="form-row">
                                                <label for="sale_start_date">
                                                    {{ $startField['label'] ?? 'Ngày bắt đầu KM' }}
                                                    @if(!empty($startField['rules']) && in_array('required', (array) $startField['rules'], true))
                                                        <span style="color: #ef4444;">*</span>
                                                    @endif
                                                </label>
                                                <input id="sale_start_date" type="{{ $startField['type'] ?? 'datetime-local' }}" name="sale_start_date" value="{{ old('sale_start_date', $startDisplay) }}" class="form-control mono">
                                                @if (!empty($startField['hint']))
                                                    <small class="field-hint">{{ $startField['hint'] }}</small>
                                                @endif
                                                @error('sale_start_date')
                                                    <div class="error" style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-row">
                                                <label for="sale_end_date">
                                                    {{ $endField['label'] ?? 'Ngày kết thúc KM' }}
                                                    @if(!empty($endField['rules']) && in_array('required', (array) $endField['rules'], true))
                                                        <span style="color: #ef4444;">*</span>
                                                    @endif
                                                </label>
                                                <input id="sale_end_date" type="{{ $endField['type'] ?? 'datetime-local' }}" name="sale_end_date" value="{{ old('sale_end_date', $endDisplay) }}" class="form-control mono">
                                                @if (!empty($endField['hint']))
                                                    <small class="field-hint">{{ $endField['hint'] }}</small>
                                                @endif
                                                @error('sale_end_date')
                                                    <div class="error" style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    @elseif ($name === 'sale_end_date' && isset($pricingFields['sale_start_date']))
                                        @continue
                                    @else
                                        @php
                                            $type = $field['type'] ?? 'number';
                                            $defaultValue = $formData[$name] ?? data_get($record, $name);
                                            if ($defaultValue instanceof \DateTimeInterface) {
                                                $displayValue = $type === 'datetime-local' ? $defaultValue->format('Y-m-d\TH:i') : $defaultValue->format('Y-m-d');
                                            } else {
                                                $displayValue = $defaultValue;
                                            }
                                        @endphp
                                        <div class="form-row">
                                            <label for="{{ $name }}">
                                                {{ $field['label'] ?? $name }}
                                                @if(!empty($field['rules']) && in_array('required', (array) $field['rules'], true))
                                                    <span style="color: #ef4444;">*</span>
                                                @endif
                                            </label>
                                            <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $displayValue) }}" class="form-control mono">
                                            @if (!empty($field['hint']))
                                                <small class="field-hint">{{ $field['hint'] }}</small>
                                            @endif
                                            @error($name)
                                                <div class="error" style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Card 3: Tối ưu hóa SEO (Google Search Engine Optimization) -->
                @if (!empty($seoFields))
                    <div class="card">
                        <div class="card-header">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #2563eb;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                <h3 class="card-title">Tối ưu hóa công cụ tìm kiếm (Google SEO)</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Live Google Search Snippet Preview -->
                            <div class="seo-snippet-preview">
                                <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                                    <div style="width: 14px; height: 14px; border-radius: 50%; background: #2563eb; display: grid; place-items: center; color: #fff; font-size: 8px; font-weight: 800;">G</div>
                                    <div class="seo-snippet-url">
                                        https://mycompany.com/{{ $routePrefix === 'admin.posts' ? 'posts' : 'products' }}/<span id="seo-preview-slug">{{ old('slug', $record->slug ?? 'duong-dan-bai-viet') }}</span>
                                    </div>
                                </div>
                                <div id="seo-preview-title" class="seo-snippet-title">
                                    {{ old('seo_title', $record->seo_title ?? ($record->title ?? ($record->name ?? 'Tiêu đề bài viết sẽ hiển thị ở đây'))) }}
                                </div>
                                <div id="seo-preview-desc" class="seo-snippet-desc">
                                    {{ old('seo_description', $record->seo_description ?? 'Mô tả ngắn gọn về bài viết để thu hút người dùng nhấp vào kết quả tìm kiếm trên Google...') }}
                                </div>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 14px;">
                                @foreach ($seoFields as $name => $field)
                                    @php
                                        $type = $field['type'] ?? 'text';
                                        $defaultValue = $formData[$name] ?? data_get($record, $name);
                                        $displayValue = is_array($defaultValue) ? json_encode($defaultValue, JSON_UNESCAPED_UNICODE) : $defaultValue;
                                    @endphp
                                    <div class="form-row">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                            <label for="{{ $name }}" style="margin: 0;">{{ $field['label'] ?? $name }}</label>
                                            @if($name === 'seo_title')
                                                <small style="font-size: 11px; color: var(--fi-muted);"><span id="seo-title-count">0</span> / 60 ký tự</small>
                                            @elseif($name === 'seo_description')
                                                <small style="font-size: 11px; color: var(--fi-muted);"><span id="seo-desc-count">0</span> / 160 ký tự</small>
                                            @endif
                                        </div>

                                        @if ($type === 'textarea')
                                            <textarea id="{{ $name }}" name="{{ $name }}" rows="3" class="form-control js-seo-input" data-seo-target="desc" placeholder="Tóm tắt nội dung hấp dẫn cho công cụ tìm kiếm">{{ old($name, $displayValue) }}</textarea>
                                        @else
                                            <input id="{{ $name }}" type="text" name="{{ $name }}" value="{{ old($name, $displayValue) }}" class="form-control js-seo-input" data-seo-target="title" placeholder="Tiêu đề trang xuất hiện trên Google">
                                        @endif

                                        @if (!empty($field['hint']))
                                            <small class="field-hint">{{ $field['hint'] }}</small>
                                        @endif
                                        @error($name)
                                            <div class="error" style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Card 4: Đánh giá & Bình luận sản phẩm / Bình luận bài viết nếu có -->
                @php
                    $isProduct = ($record instanceof \App\Models\Product) || ($routePrefix === 'admin.products') || (isset($routePrefix) && str_contains($routePrefix, 'products')) || request()->is('*products*');
                    $isPost = ($record instanceof \App\Models\Post) || ($routePrefix === 'admin.posts') || (isset($routePrefix) && str_contains($routePrefix, 'posts')) || request()->is('*posts*');
                @endphp
                @if($record && ($isProduct || $isPost) && method_exists($record, 'comments'))
                    <div class="card" style="margin-top: 18px; overflow: hidden;">
                        <div class="card-header">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #64748b;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                <h3 class="card-title">
                                    {{ $isProduct ? 'Đánh giá & Bình luận sản phẩm' : 'Bình luận bài viết' }}
                                    ({{ $record->comments->count() }})
                                </h3>
                            </div>
                        </div>
                        <div class="table-wrap" style="border: 0; border-radius: 0;">
                            <table>
                                <thead>
                                    <tr>
                                        <th style="padding: 10px 14px;">Tác giả</th>
                                        <th style="padding: 10px 14px;">Nội dung</th>
                                        <th style="padding: 10px 14px;">Thời gian</th>
                                        <th style="padding: 10px 14px; text-align: center;">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($record->comments as $comment)
                                        <tr>
                                            <td style="font-weight: 600; color: var(--fi-text); padding: 10px 14px;">{{ $comment->author->name ?? $comment->author_name ?? 'Khách' }}</td>
                                            <td style="padding: 10px 14px;">{{ Str::limit($comment->content, 100) }}</td>
                                            <td class="cell-muted mono" style="padding: 10px 14px;">{{ $comment->created_at->format('d/m/Y H:i') }}</td>
                                            <td style="padding: 10px 14px; text-align: center;"><x-admin.status-badge :status="$comment->is_approved ?? $comment->status ?? 'pending'" /></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="empty-state" style="padding: 24px; text-align: center; color: var(--fi-muted);">
                                                {{ $isProduct ? 'Chưa có đánh giá hoặc bình luận nào cho sản phẩm này.' : 'Chưa có bình luận nào cho bài viết này.' }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <!-- ================= SIDEBAR COLUMN ================= -->
            @if ($hasSidebar)
                <div class="show-sidebar">
                    <!-- Card A: Thiết lập xuất bản & Trạng thái -->
                    <div class="card">
                        <div class="card-header">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--fi-gold);"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                <h3 class="card-title">Thiết lập & Xuất bản</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div style="display: flex; flex-direction: column; gap: 14px;">
                                @foreach ($sidebarMetaFields as $name => $field)
                                    @php
                                        $type = $field['type'] ?? 'text';
                                        $defaultValue = $formData[$name] ?? data_get($record, $name);
                                        if ($defaultValue instanceof \DateTimeInterface) {
                                            $displayValue = $type === 'datetime-local' ? $defaultValue->format('Y-m-d\TH:i') : $defaultValue->format('Y-m-d');
                                        } elseif ($defaultValue instanceof \BackedEnum) {
                                            $displayValue = $defaultValue->value;
                                        } else {
                                            $displayValue = $defaultValue;
                                        }
                                    @endphp

                                    <div class="form-row">
                                        <label for="{{ $name }}" style="margin-bottom: 5px; font-weight: 600;">
                                            {{ $field['label'] ?? $name }}
                                            @if(!empty($field['rules']) && in_array('required', (array) $field['rules'], true))
                                                <span style="color: #ef4444;">*</span>
                                            @endif
                                        </label>

                                        @if ($type === 'select')
                                            <select id="{{ $name }}" name="{{ $name }}" class="form-control">
                                                @foreach (($field['options'] ?? []) as $value => $label)
                                                    <option value="{{ $value }}" @selected((string) old($name, $displayValue) === (string) $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        @elseif ($type === 'date' || $type === 'datetime-local')
                                            <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $displayValue) }}" class="form-control">
                                        @else
                                            <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $displayValue) }}" class="form-control">
                                        @endif

                                        @if (!empty($field['hint']))
                                            <small class="field-hint">{{ $field['hint'] }}</small>
                                        @endif
                                        @error($name)
                                            <div class="error" style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach

                                <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px; padding-top: 14px; border-top: 1px solid var(--fi-border);">
                                    <button class="btn btn-primary" type="submit" form="crud-main-form" style="width: 100%; justify-content: center;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                        {{ __('admin.actions.save') }}
                                    </button>
                                    <a class="btn btn-secondary" href="{{ route($routePrefix . '.index') }}" style="width: 100%; justify-content: center;">
                                        {{ __('admin.actions.cancel') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card B: Hình ảnh đại diện (Featured Image) -->
                    @if (!empty($sidebarMediaFields))
                        @foreach ($sidebarMediaFields as $name => $field)
                            @php
                                $defaultValue = $formData[$name] ?? data_get($record, $name);
                                $imgSrc = old($name, $defaultValue);
                                if (is_numeric($imgSrc)) {
                                    $media = \Awcodes\Curator\Models\Media::find($imgSrc);
                                    $imgSrc = $media ? $media->path : $imgSrc;
                                }
                                $resolvedImgUrl = $imgSrc ? (Str::startsWith($imgSrc, ['http', '/']) ? $imgSrc : asset('storage/' . $imgSrc)) : null;
                            @endphp
                            <div class="card">
                                <div class="card-header">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #6366f1;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                        <h3 class="card-title">{{ $field['label'] ?? 'Hình ảnh đại diện' }}</h3>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="featured-media-card">
                                        <div class="featured-media-frame">
                                            <img id="preview-{{ $name }}" src="{{ $resolvedImgUrl ?: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' }}" alt="Preview" style="{{ $resolvedImgUrl ? '' : 'display: none;' }}">
                                            <div id="placeholder-{{ $name }}" class="featured-media-placeholder" style="{{ $resolvedImgUrl ? 'display: none;' : '' }}">
                                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="color: #94a3b8;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                                <span style="font-size: 11.5px; color: #64748b;">Chưa có ảnh đại diện</span>
                                            </div>
                                        </div>
                                        <div class="custom-file-picker" style="width: 100%; justify-content: center;">
                                            <input type="file" id="{{ $name }}" name="{{ $name }}" accept="image/*" onchange="previewFeaturedImage(this, 'preview-{{ $name }}', 'placeholder-{{ $name }}')">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                            <span>Chọn / Tải lên hình ảnh</span>
                                        </div>
                                        @if($imgSrc)
                                            <div style="margin-top: 8px; font-size: 11px; color: var(--fi-muted); font-family: var(--fi-font-mono); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                {{ is_string($imgSrc) ? basename($imgSrc) : 'image.jpg' }}
                                            </div>
                                        @endif
                                    </div>
                                    @error($name)
                                        <div class="error" style="color: #dc2626; font-size: 12px; margin-top: 6px;">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endforeach
                    @endif

                    <!-- Card C: Danh mục & Phân loại (Tree Select) -->
                    @foreach ($sidebarTaxonomyFields as $name => $field)
                        @if(($field['type'] ?? '') === 'tree-select' || $name === 'categories')
                            @php
                                $selected = old($name, $formData[$name] ?? []);
                            @endphp
                            <div class="card">
                                <div class="card-header">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #0891b2;"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                                        <h3 class="card-title">{{ $field['label'] ?? 'Danh mục' }}</h3>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="tree-select-wrapper">
                                        @include('admin.crud.tree_node', ['nodes' => $field['tree_nodes'] ?? [], 'level' => 0, 'name' => $name, 'selected' => $selected])
                                    </div>
                                    @error($name)
                                        <div class="error" style="color: #dc2626; font-size: 12px; margin-top: 6px;">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endif
                    @endforeach

                    <!-- Card D: Thẻ / Nhãn (Tags) -->
                    @foreach ($sidebarTaxonomyFields as $name => $field)
                        @if(($field['type'] ?? '') === 'tags-checkboxes' || $name === 'tags')
                            @php
                                $selected = old($name, $formData[$name] ?? []);
                            @endphp
                            <div class="card">
                                <div class="card-header">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #8b5cf6;"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                                        <h3 class="card-title">{{ $field['label'] ?? 'Thẻ / Tags' }}</h3>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="tag-pills-grid">
                                        @forelse (($field['options'] ?? []) as $value => $label)
                                            <label class="tag-pill-label">
                                                <input type="checkbox" name="{{ $name }}[]" value="{{ $value }}" @checked(in_array((string) $value, array_map('strval', (array) $selected), true))>
                                                <span>{{ $label }}</span>
                                            </label>
                                        @empty
                                            <span style="font-size: 12px; color: var(--fi-muted); padding: 4px;">Chưa có thẻ nào được cấu hình.</span>
                                        @endforelse
                                    </div>
                                    @error($name)
                                        <div class="error" style="color: #dc2626; font-size: 12px; margin-top: 6px;">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </form>
@endsection

@push('scripts')
<style>
    .blocks-container .ck-editor__editable_inline {
        min-height: 220px;
        font-size: 14px;
        line-height: 1.6;
        color: #1e293b;
        background: #ffffff;
    }
    .blocks-container .ck.ck-editor {
        width: 100%;
    }
    .blocks-container .ck.ck-toolbar {
        border-radius: 6px 6px 0 0;
        border-color: #cbd5e1;
        background: #f8fafc;
    }
    .blocks-container .ck.ck-content {
        border-radius: 0 0 6px 6px;
        border-color: #cbd5e1;
    }
    .blocks-container .block-item {
        border: 1px solid var(--fi-border);
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .blocks-container .block-item:hover {
        border-color: #94a3b8;
    }
</style>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script src="{{ asset('vendor/sortablejs/sortable.min.js') }}"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('blockManager', (initialBlocks) => ({
            blocks: [],
            showBlockMenu: false,
            blockTypes: [
                { value: 'editor', label: 'Soạn thảo văn bản (Word / Rich Text)' },
                { value: 'heading', label: 'Tiêu đề (Heading H1 - H6)' },
                { value: 'media', label: 'Hình ảnh (Tải lên)' },
                { value: 'gallery', label: 'Thư viện ảnh (Gallery)' },
                { value: 'text', label: 'Văn bản ngắn (Text)' },
                { value: 'textarea', label: 'Đoạn văn thuần (Textarea)' },
                { value: 'code-editor', label: 'Mã nhúng HTML / Code' },
            ],
            init() {
                const raw = Array.isArray(initialBlocks) ? initialBlocks : [];
                this.blocks = raw.map(b => this.normalizeBlock(b));

                this.$nextTick(() => {
                    const containers = this.$el.querySelectorAll('.blocks-container');
                    containers.forEach(el => {
                        if (window.Sortable) {
                            Sortable.create(el, {
                                handle: '.drag-handle',
                                animation: 150,
                                onEnd: (evt) => {
                                    const movedItem = this.blocks.splice(evt.oldIndex, 1)[0];
                                    this.blocks.splice(evt.newIndex, 0, movedItem);
                                }
                            });
                        }
                    });
                });
            },
            normalizeBlock(b) {
                const activeLocale = '{{ app()->getLocale() ?: 'vi' }}';
                let val = b.value ?? '';
                let textVal = '';
                let existingTranslations = {};

                if (typeof val === 'object' && val !== null && !Array.isArray(val)) {
                    existingTranslations = { ...val };
                    textVal = val[activeLocale] ?? val['vi'] ?? val['en'] ?? Object.values(val)[0] ?? '';
                } else if (Array.isArray(val)) {
                    textVal = val;
                } else {
                    textVal = val ? String(val) : '';
                    existingTranslations = { [activeLocale]: textVal };
                }

                const bType = (b.type === 'markdown' || b.type === 'editor') ? 'editor' : (b.type || 'editor');
                let galleryList = [];
                if (bType === 'gallery') {
                    if (Array.isArray(b.images)) {
                        galleryList = b.images;
                    } else if (Array.isArray(val)) {
                        galleryList = val;
                    }
                }

                return {
                    _key: b._key || Math.random().toString(36).substr(2, 9),
                    type: bType,
                    level: b.level ? String(b.level) : '2',
                    value: bType === 'gallery' ? '' : textVal,
                    galleryImages: galleryList,
                    translations: existingTranslations,
                    _uploading: false
                };
            },
            getBlockLabel(typeValue) {
                if (typeValue === 'markdown' || typeValue === 'editor') {
                    return 'Soạn thảo văn bản (Word)';
                }
                const type = this.blockTypes.find(t => t.value === typeValue);
                return type ? type.label : typeValue;
            },
            addBlock(type = 'editor') {
                this.blocks.push({
                    _key: Math.random().toString(36).substr(2, 9),
                    type: type,
                    level: '2',
                    value: '',
                    galleryImages: type === 'gallery' ? [] : [],
                    translations: {},
                    _uploading: false
                });
            },
            removeBlock(index) {
                if (confirm('Bạn có chắc muốn xóa block này?')) {
                    this.blocks.splice(index, 1);
                }
            },
            moveBlockUp(index) {
                if (index > 0) {
                    const item = this.blocks.splice(index, 1)[0];
                    this.blocks.splice(index - 1, 0, item);
                }
            },
            moveBlockDown(index) {
                if (index < this.blocks.length - 1) {
                    const item = this.blocks.splice(index, 1)[0];
                    this.blocks.splice(index + 1, 0, item);
                }
            },
            initEditor(container, block) {
                if (container.dataset.editorReady === 'true') return;

                const tryInit = () => {
                    if (!window.ClassicEditor) {
                        setTimeout(tryInit, 100);
                        return;
                    }
                    if (container.dataset.editorReady === 'true') return;
                    container.dataset.editorReady = 'true';

                    const wrapper = document.createElement('div');
                    container.appendChild(wrapper);

                    ClassicEditor.create(wrapper, {
                        toolbar: [
                            'heading', '|',
                            'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote',
                            '|', 'insertTable', 'undo', 'redo'
                        ],
                        placeholder: 'Nhập nội dung hoặc dán trực tiếp từ Microsoft Word / Google Docs...'
                    }).then(editor => {
                        container._ckEditor = editor;
                        container._block = block;
                        editor.setData(block.value || '');
                        editor.model.document.on('change:data', () => {
                            block.value = editor.getData();
                        });
                    }).catch(err => {
                        console.error('ClassicEditor error in block:', err);
                        container.dataset.editorReady = 'false';
                    });
                };

                tryInit();
            },
            uploadBlockMedia(event, block) {
                const file = event.target.files && event.target.files[0];
                if (!file) return;

                block._uploading = true;
                const formData = new FormData();
                formData.append('file', file);
                formData.append('_token', '{{ csrf_token() }}');

                fetch('{{ route('admin.media.upload') }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    block._uploading = false;
                    if (data && data.url) {
                        block.value = data.url;
                    } else {
                        alert('Không thể tải ảnh lên. Vui lòng thử lại.');
                    }
                })
                .catch(err => {
                    block._uploading = false;
                    console.error('Upload error:', err);
                    alert('Lỗi khi tải ảnh lên.');
                });
            },
            uploadGalleryImages(event, block) {
                const files = event.target.files;
                if (!files || files.length === 0) return;

                if (!Array.isArray(block.galleryImages)) {
                    block.galleryImages = [];
                }

                block._uploading = true;
                const uploadPromises = Array.from(files).map(file => {
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('_token', '{{ csrf_token() }}');

                    return fetch('{{ route('admin.media.upload') }}', {
                        method: 'POST',
                        body: formData,
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(res => res.json())
                    .then(data => data && data.url ? data.url : null)
                    .catch(() => null);
                });

                Promise.all(uploadPromises).then(results => {
                    block._uploading = false;
                    results.filter(Boolean).forEach(url => {
                        block.galleryImages.push(url);
                    });
                });
            },
            serializeBlocks() {
                const containers = this.$el.querySelectorAll('[data-editor-ready="true"]');
                containers.forEach(c => {
                    if (c._ckEditor && c._block) {
                        c._block.value = c._ckEditor.getData();
                    }
                });

                const activeLocale = '{{ app()->getLocale() ?: 'vi' }}';

                return this.blocks.map(b => {
                    const type = b.type || 'editor';

                    if (type === 'gallery') {
                        const imgs = Array.isArray(b.galleryImages) ? b.galleryImages.filter(u => u && u.trim() !== '') : [];
                        return {
                            type: 'gallery',
                            images: imgs,
                            value: imgs
                        };
                    }

                    if (type === 'media') {
                        return {
                            type: 'media',
                            value: b.value || ''
                        };
                    }

                    if (type === 'code-editor') {
                        return {
                            type: 'code-editor',
                            value: b.value || ''
                        };
                    }

                    if (type === 'heading') {
                        let finalVal = b.translations && typeof b.translations === 'object' && Object.keys(b.translations).length > 0
                            ? { ...b.translations, [activeLocale]: b.value || '' }
                            : { [activeLocale]: b.value || '' };

                        return {
                            type: 'heading',
                            level: parseInt(b.level || '2', 10),
                            value: finalVal
                        };
                    }

                    // For editor, text, textarea:
                    let finalVal = b.translations && typeof b.translations === 'object' && Object.keys(b.translations).length > 0
                        ? { ...b.translations, [activeLocale]: b.value || '' }
                        : { [activeLocale]: b.value || '' };

                    return {
                        type: type,
                        value: finalVal
                    };
                });
            }
        }));
    });

    // CKEditor 5 Initialization for classic single editors
    document.addEventListener("DOMContentLoaded", function() {
        const editors = document.querySelectorAll('.ckeditor-input');
        editors.forEach(editor => {
            if (window.ClassicEditor) {
                ClassicEditor.create(editor, {
                    toolbar: [
                        'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote',
                        '|', 'insertTable', 'undo', 'redo'
                    ]
                }).catch(error => console.error(error));
            }
        });

        // Ensure all block editors flush data before form submit
        const mainForm = document.getElementById('crud-main-form');
        if (mainForm) {
            mainForm.addEventListener('submit', function() {
                document.querySelectorAll('[data-editor-ready="true"]').forEach(c => {
                    if (c._ckEditor && c._syncProp && c._block) {
                        c._block[c._syncProp] = c._ckEditor.getData();
                    }
                });
            });
        }

        // Live Google SEO Snippet Preview & Character Counter
        const titleInput = document.getElementById('title') || document.getElementById('name');
        const slugInput = document.getElementById('slug');
        const seoTitleInput = document.getElementById('seo_title');
        const seoDescInput = document.getElementById('seo_description');

        const previewTitle = document.getElementById('seo-preview-title');
        const previewSlug = document.getElementById('seo-preview-slug');
        const previewDesc = document.getElementById('seo-preview-desc');

        const titleCount = document.getElementById('seo-title-count');
        const descCount = document.getElementById('seo-desc-count');

        const updateSeoPreview = () => {
            if (previewTitle) {
                const t = (seoTitleInput && seoTitleInput.value.trim()) || (titleInput && titleInput.value.trim()) || 'Tiêu đề bài viết sẽ hiển thị ở đây';
                previewTitle.innerText = t;
            }
            if (previewSlug && slugInput) {
                previewSlug.innerText = slugInput.value.trim() || 'duong-dan-bai-viet';
            }
            if (previewDesc) {
                const d = (seoDescInput && seoDescInput.value.trim()) || 'Mô tả ngắn gọn về bài viết để thu hút người dùng nhấp vào kết quả tìm kiếm trên Google...';
                previewDesc.innerText = d;
            }
            if (titleCount && seoTitleInput) {
                titleCount.innerText = seoTitleInput.value.length;
            }
            if (descCount && seoDescInput) {
                descCount.innerText = seoDescInput.value.length;
            }
        };

        if (titleInput) titleInput.addEventListener('input', updateSeoPreview);
        if (slugInput) slugInput.addEventListener('input', updateSeoPreview);
        if (seoTitleInput) seoTitleInput.addEventListener('input', updateSeoPreview);
        if (seoDescInput) seoDescInput.addEventListener('input', updateSeoPreview);

        updateSeoPreview();
    });

    // Slug Generator from Title
    function generateSlugFromTitle() {
        const titleInput = document.getElementById('title') || document.getElementById('name');
        const slugInput = document.getElementById('slug');
        if (!titleInput || !slugInput) return;

        let str = titleInput.value.toLowerCase().trim();
        str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, 'a');
        str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, 'e');
        str = str.replace(/ì|í|ị|ỉ|ĩ/g, 'i');
        str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, 'o');
        str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, 'u');
        str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g, 'y');
        str = str.replace(/đ/g, 'd');
        str = str.replace(/[^a-z0-9 -]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');

        slugInput.value = str;
        slugInput.dispatchEvent(new Event('input'));
    }

    // Featured Image Preview Handler
    function previewFeaturedImage(input, previewId, placeholderId) {
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById(placeholderId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                if (placeholder) {
                    placeholder.style.display = 'none';
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
