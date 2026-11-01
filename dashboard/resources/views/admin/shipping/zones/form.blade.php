@extends('admin.layouts.app', ['title' => $title])

@section('content')
@php
    $northProvinces = $formData['northProvinces'] ?? [];
    $centralProvinces = $formData['centralProvinces'] ?? [];
    $southProvinces = $formData['southProvinces'] ?? [];
    $continents = $formData['continents'] ?? [];
    $countries = $formData['countries'] ?? [];
    $initialLocations = $formData['initialLocations'] ?? ($record ? ($record->locations ?? []) : []);

    $currentName = old('name', is_array($record->name ?? null) ? ($record->name['vi'] ?? ($record->name['en'] ?? '')) : ($record->name ?? ''));
@endphp

<div x-data="shippingZoneManager({
    initialLocations: {{ json_encode($initialLocations) }},
    northList: {{ json_encode(array_keys($northProvinces)) }},
    centralList: {{ json_encode(array_keys($centralProvinces)) }},
    southList: {{ json_encode(array_keys($southProvinces)) }},
    continentMap: {{ json_encode($continents) }}
})">

    {{-- PAGE HEADER --}}
    <x-admin.page-header
        :title="$record ? 'Chỉnh sửa Khu vực vận chuyển: ' . ($currentName ?: '#' . $record->id) : 'Tạo mới Khu vực vận chuyển'"
        :description="'Cấu hình khu vực địa lý áp dụng cước và phạm vi phục vụ đơn hàng.'"
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
            <button class="btn btn-primary" type="submit" form="shipping-zone-form">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                {{ __('admin.actions.save') }}
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- NAVIGATION TABS --}}
    @if (!empty($tabs))
        <div style="margin-bottom: var(--fi-space-4, 16px);">
            <x-admin.tabs
                mode="link"
                :active="$activeTab ?? 'shipping-zones'"
                :tabs="$tabs"
            />
        </div>
    @endif

    {{-- ERROR ALERT --}}
    @if (isset($errors) && $errors->any())
        <div style="background: var(--fi-danger-bg, #fef2f2); color: #991b1b; padding: 12px 16px; border-radius: var(--fi-radius-md, 8px); margin-bottom: 20px; border: 1px solid var(--fi-danger-border, #fecaca);">
            <div style="font-weight: 700; font-size: 13px; margin-bottom: 4px;">Vui lòng kiểm tra lại dữ liệu:</div>
            <ul style="margin: 0; padding-left: 20px; font-size: 12.5px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="shipping-zone-form" method="POST" action="{{ $record ? route($routePrefix . '.update', $record->id) : route($routePrefix . '.store') }}">
        @csrf
        @if ($record)
            @method('PUT')
        @endif

        {{-- Serialized JSON synced with Alpine --}}
        <input type="hidden" name="locations_json" :value="serializeLocations()">

        <div class="edit-layout">
            
            {{-- MAIN COLUMN --}}
            <div class="show-main" style="display: flex; flex-direction: column; gap: 20px;">

                {{-- CARD 1: THÔNG TIN CƠ BẢN --}}
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--fi-primary);"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            <h3 class="card-title">Thông tin cơ bản</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            
                            {{-- TÊN KHU VỰC --}}
                            <div class="form-row" style="grid-column: 1 / -1;">
                                <label for="name" style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                    Tên khu vực <span style="color: #ef4444;">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    value="{{ $currentName }}" 
                                    placeholder="Ví dụ: Miền Bắc, Miền Nam, Toàn quốc..." 
                                    required 
                                    class="form-control"
                                >
                            </div>

                            {{-- MÃ KHU VỰC --}}
                            <div class="form-row">
                                <label for="code" style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                    Mã khu vực (Zone Code)
                                </label>
                                <input 
                                    type="text" 
                                    id="code" 
                                    name="code" 
                                    value="{{ old('code', $record->code ?? '') }}" 
                                    placeholder="VN_NORTH, VN_CENTRAL..." 
                                    class="form-control"
                                    style="font-family: monospace;"
                                >
                            </div>

                            {{-- THỨ TỰ ƯU TIÊN --}}
                            <div class="form-row">
                                <label for="sort" style="margin-bottom: 6px; font-weight: 600; font-size: 13px; display: block;">
                                    Thứ tự ưu tiên
                                </label>
                                <input 
                                    type="number" 
                                    id="sort" 
                                    name="sort" 
                                    value="{{ old('sort', $record->sort ?? 10) }}" 
                                    min="0" 
                                    class="form-control"
                                >
                                <small style="color: var(--fi-muted); font-size: 11.5px; display: block; margin-top: 4px;">Ưu tiên khớp theo số nhỏ trước khi checkout.</small>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- CARD 2: PHẠM VI ĐỊA LÝ ÁP DỤNG --}}
                <div class="card">
                    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--fi-primary);"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                            <h3 class="card-title">Phạm vi địa lý áp dụng</h3>
                        </div>

                        {{-- SCOPE DROPDOWN SELECTOR --}}
                        <div style="min-width: 260px;">
                            <select x-model="scopeType" class="form-control" style="font-weight: 600; height: 36px;">
                                <option value="vietnam_provinces">Tỉnh thành Việt Nam cụ thể</option>
                                <option value="vietnam_nationwide">Toàn quốc Việt Nam (Toàn bộ 63 tỉnh)</option>
                                <option value="international">Quốc tế (Theo Châu lục / Quốc gia)</option>
                                <option value="worldwide_fallback">Toàn cầu (Tất cả quốc gia còn lại - Fallback)</option>
                            </select>
                        </div>
                    </div>

                    <div class="card-body">

                        {{-- SCOPE 1: TỈNH THÀNH VIỆT NAM --}}
                        <div x-show="scopeType === 'vietnam_provinces'" x-transition>
                            
                            {{-- FILTER TOOLBAR --}}
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; background: #f8fafc; padding: 10px 14px; border-radius: var(--fi-radius-md, 8px); border: 1px solid var(--fi-border-subtle, #e2e8f0);">
                                <div style="position: relative; flex: 1; min-width: 220px;">
                                    <input 
                                        type="text" 
                                        x-model="provinceSearch" 
                                        placeholder="Tìm nhanh tỉnh thành (Hà Nội, TP.HCM, Đà Nẵng...)" 
                                        class="form-control"
                                        style="padding-left: 32px; height: 34px; font-size: 12.5px;"
                                    >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" style="position: absolute; left: 10px; top: 10px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                </div>

                                <div style="display: flex; align-items: center; gap: 12px; font-size: 12.5px;">
                                    <span style="color: var(--fi-muted, #64748b);">
                                        Đã chọn: <strong style="color: var(--fi-primary, #4f46e5); font-size: 13.5px;" x-text="selectedProvinces.length"></strong>/63 tỉnh
                                    </span>
                                    <button type="button" @click="selectAllProvinces()" class="btn btn-secondary" style="padding: 4px 10px; font-size: 12px;">
                                        Chọn tất cả
                                    </button>
                                    <button type="button" @click="deselectAllProvinces()" class="btn btn-secondary" style="padding: 4px 10px; font-size: 12px; color: #dc2626;">
                                        Bỏ chọn
                                    </button>
                                </div>
                            </div>

                            {{-- 3 REGION ACCORDIONS --}}
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                
                                {{-- MIỀN BẮC --}}
                                <div style="border: 1px solid var(--fi-border-subtle, #e2e8f0); border-radius: var(--fi-radius-md, 8px); overflow: hidden;">
                                    <div 
                                        @click="toggleExpand('north')"
                                        style="padding: 10px 14px; background: #f8fafc; cursor: pointer; display: flex; align-items: center; justify-content: space-between; user-select: none;"
                                    >
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :style="expanded.north ? 'transform: rotate(90deg);' : ''" style="transition: transform 0.15s ease;"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                            <strong style="font-size: 13px; color: var(--fi-text, #0f172a);">Miền Bắc (25)</strong>
                                        </div>

                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span style="font-size: 11px; font-weight: 600; color: #4338ca; background: #e0e7ff; padding: 2px 8px; border-radius: 4px;" x-text="countSelectedInList(northList) + '/25 đã chọn'"></span>
                                            <button 
                                                type="button" 
                                                @click.stop="toggleAllNorth()" 
                                                style="font-size: 12px; color: var(--fi-primary, #4f46e5); font-weight: 600; background: none; border: none; cursor: pointer; padding: 0;"
                                                x-text="isAllNorthSelected() ? 'Bỏ chọn' : 'Chọn cả miền'"
                                            ></button>
                                        </div>
                                    </div>

                                    <div x-show="expanded.north" style="padding: 12px 14px; border-top: 1px solid var(--fi-border-subtle, #f1f5f9);">
                                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 8px;">
                                            @foreach ($northProvinces as $name => $code)
                                                <label 
                                                    x-show="matchesSearch('{{ $name }}', '{{ $code }}')"
                                                    style="display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #334155; cursor: pointer; padding: 3px 0;"
                                                >
                                                    <input 
                                                        type="checkbox" 
                                                        value="{{ $name }}" 
                                                        :checked="selectedProvinces.includes('{{ $name }}')"
                                                        @change="toggleProvince('{{ $name }}')"
                                                        style="accent-color: var(--fi-primary, #4f46e5); width: 15px; height: 15px;"
                                                    >
                                                    <span :style="selectedProvinces.includes('{{ $name }}') ? 'font-weight: 600; color: #0f172a;' : ''">{{ $name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                {{-- MIỀN TRUNG --}}
                                <div style="border: 1px solid var(--fi-border-subtle, #e2e8f0); border-radius: var(--fi-radius-md, 8px); overflow: hidden;">
                                    <div 
                                        @click="toggleExpand('central')"
                                        style="padding: 10px 14px; background: #f8fafc; cursor: pointer; display: flex; align-items: center; justify-content: space-between; user-select: none;"
                                    >
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :style="expanded.central ? 'transform: rotate(90deg);' : ''" style="transition: transform 0.15s ease;"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                            <strong style="font-size: 13px; color: var(--fi-text, #0f172a);">Miền Trung & Tây Nguyên</strong>
                                            <span style="font-size: 12px; color: var(--fi-muted, #64748b);">(19 tỉnh)</span>
                                        </div>

                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span style="font-size: 11px; font-weight: 600; color: #b45309; background: #fef3c7; padding: 2px 8px; border-radius: 4px;" x-text="countSelectedInList(centralList) + '/19 đã chọn'"></span>
                                            <button 
                                                type="button" 
                                                @click.stop="toggleAllCentral()" 
                                                style="font-size: 12px; color: var(--fi-primary, #4f46e5); font-weight: 600; background: none; border: none; cursor: pointer; padding: 0;"
                                                x-text="isAllCentralSelected() ? 'Bỏ chọn' : 'Chọn cả miền'"
                                            ></button>
                                        </div>
                                    </div>

                                    <div x-show="expanded.central" style="padding: 12px 14px; border-top: 1px solid var(--fi-border-subtle, #f1f5f9);">
                                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 8px;">
                                            @foreach ($centralProvinces as $name => $code)
                                                <label 
                                                    x-show="matchesSearch('{{ $name }}', '{{ $code }}')"
                                                    style="display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #334155; cursor: pointer; padding: 3px 0;"
                                                >
                                                    <input 
                                                        type="checkbox" 
                                                        value="{{ $name }}" 
                                                        :checked="selectedProvinces.includes('{{ $name }}')"
                                                        @change="toggleProvince('{{ $name }}')"
                                                        style="accent-color: var(--fi-primary, #4f46e5); width: 15px; height: 15px;"
                                                    >
                                                    <span :style="selectedProvinces.includes('{{ $name }}') ? 'font-weight: 600; color: #0f172a;' : ''">{{ $name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                {{-- MIỀN NAM --}}
                                <div style="border: 1px solid var(--fi-border-subtle, #e2e8f0); border-radius: var(--fi-radius-md, 8px); overflow: hidden;">
                                    <div 
                                        @click="toggleExpand('south')"
                                        style="padding: 10px 14px; background: #f8fafc; cursor: pointer; display: flex; align-items: center; justify-content: space-between; user-select: none;"
                                    >
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :style="expanded.south ? 'transform: rotate(90deg);' : ''" style="transition: transform 0.15s ease;"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                            <strong style="font-size: 13px; color: var(--fi-text, #0f172a);">Miền Nam</strong>
                                            <span style="font-size: 12px; color: var(--fi-muted, #64748b);">(19 tỉnh)</span>
                                        </div>

                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span style="font-size: 11px; font-weight: 600; color: #15803d; background: #dcfce7; padding: 2px 8px; border-radius: 4px;" x-text="countSelectedInList(southList) + '/19 đã chọn'"></span>
                                            <button 
                                                type="button" 
                                                @click.stop="toggleAllSouth()" 
                                                style="font-size: 12px; color: var(--fi-primary, #4f46e5); font-weight: 600; background: none; border: none; cursor: pointer; padding: 0;"
                                                x-text="isAllSouthSelected() ? 'Bỏ chọn' : 'Chọn cả miền'"
                                            ></button>
                                        </div>
                                    </div>

                                    <div x-show="expanded.south" style="padding: 12px 14px; border-top: 1px solid var(--fi-border-subtle, #f1f5f9);">
                                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 8px;">
                                            @foreach ($southProvinces as $name => $code)
                                                <label 
                                                    x-show="matchesSearch('{{ $name }}', '{{ $code }}')"
                                                    style="display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #334155; cursor: pointer; padding: 3px 0;"
                                                >
                                                    <input 
                                                        type="checkbox" 
                                                        value="{{ $name }}" 
                                                        :checked="selectedProvinces.includes('{{ $name }}')"
                                                        @change="toggleProvince('{{ $name }}')"
                                                        style="accent-color: var(--fi-primary, #4f46e5); width: 15px; height: 15px;"
                                                    >
                                                    <span :style="selectedProvinces.includes('{{ $name }}') ? 'font-weight: 600; color: #0f172a;' : ''">{{ $name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- SCOPE 2: TOÀN QUỐC VIỆT NAM --}}
                        <div x-show="scopeType === 'vietnam_nationwide'" x-transition style="padding: 16px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--fi-radius-md, 8px);">
                            <div style="font-weight: 600; color: #166534; font-size: 13.5px; margin-bottom: 4px;">
                                Áp dụng tự động cho toàn bộ 63 tỉnh thành Việt Nam
                            </div>
                            <p style="font-size: 12.5px; color: #15803d; margin: 0; line-height: 1.5;">
                                Khu vực này hoạt động như cấu hình dự phòng mặc định khi khách hàng đặt hàng tại Việt Nam mà không khớp với khu vực tỉnh thành cụ thể nào ở trên.
                            </p>
                        </div>

                        {{-- SCOPE 3: QUỐC TẾ --}}
                        <div x-show="scopeType === 'international'" x-transition>
                            
                            {{-- CONTINENT SELECTORS --}}
                            <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; align-items: center;">
                                <span style="font-size: 12px; font-weight: 600; color: var(--fi-muted, #64748b); margin-right: 4px;">Châu lục:</span>
                                <button type="button" @click="toggleContinent('AS')" :class="isContinentSelected('AS') ? 'btn btn-primary' : 'btn btn-secondary'" style="padding: 4px 10px; font-size: 12px;">
                                    Châu Á
                                </button>
                                <button type="button" @click="toggleContinent('EU')" :class="isContinentSelected('EU') ? 'btn btn-primary' : 'btn btn-secondary'" style="padding: 4px 10px; font-size: 12px;">
                                    Châu Âu
                                </button>
                                <button type="button" @click="toggleContinent('NA')" :class="isContinentSelected('NA') ? 'btn btn-primary' : 'btn btn-secondary'" style="padding: 4px 10px; font-size: 12px;">
                                    Bắc Mỹ
                                </button>
                                <button type="button" @click="toggleContinent('SA')" :class="isContinentSelected('SA') ? 'btn btn-primary' : 'btn btn-secondary'" style="padding: 4px 10px; font-size: 12px;">
                                    Nam Mỹ
                                </button>
                                <button type="button" @click="toggleContinent('AF')" :class="isContinentSelected('AF') ? 'btn btn-primary' : 'btn btn-secondary'" style="padding: 4px 10px; font-size: 12px;">
                                    Châu Phi
                                </button>
                                <button type="button" @click="toggleContinent('OC')" :class="isContinentSelected('OC') ? 'btn btn-primary' : 'btn btn-secondary'" style="padding: 4px 10px; font-size: 12px;">
                                    Châu Đại Dương
                                </button>
                                <button type="button" @click="selectedCountries = []" style="border: none; background: none; color: #dc2626; font-size: 12px; margin-left: auto; cursor: pointer;">
                                    Bỏ chọn tất cả
                                </button>
                            </div>

                            {{-- SEARCH COUNTRY INPUT --}}
                            <div style="position: relative; margin-bottom: 12px;">
                                <input 
                                    type="text" 
                                    x-model="countrySearch" 
                                    placeholder="Tìm kiếm quốc gia theo tên hoặc mã ISO (US, JP, FR, DE...)" 
                                    class="form-control"
                                    style="padding-left: 32px; height: 34px; font-size: 12.5px;"
                                >
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" style="position: absolute; left: 10px; top: 10px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </div>

                            {{-- COUNTRY LIST GRID --}}
                            <div style="border: 1px solid var(--fi-border-subtle, #e2e8f0); border-radius: var(--fi-radius-md, 8px); max-height: 280px; overflow-y: auto; padding: 12px; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 6px; background: #ffffff;">
                                @foreach ($countries as $iso => $countryName)
                                    @if ($iso !== 'VN')
                                        <label 
                                            x-show="matchesCountrySearch('{{ $iso }}', '{{ addslashes($countryName) }}')"
                                            style="display: flex; align-items: center; justify-content: space-between; padding: 5px 8px; border-radius: 4px; font-size: 12px; cursor: pointer;"
                                        >
                                            <span style="display: flex; align-items: center; gap: 6px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                <input 
                                                    type="checkbox" 
                                                    value="{{ $iso }}" 
                                                    :checked="selectedCountries.includes('{{ $iso }}')"
                                                    @change="toggleCountry('{{ $iso }}')"
                                                    style="accent-color: var(--fi-primary, #4f46e5); width: 14px; height: 14px;"
                                                >
                                                <span style="overflow: hidden; text-overflow: ellipsis;" :style="selectedCountries.includes('{{ $iso }}') ? 'font-weight: 600;' : ''">{{ $countryName }}</span>
                                            </span>
                                            <span style="color: #94a3b8; font-family: monospace; font-size: 11px;">{{ $iso }}</span>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        {{-- SCOPE 4: TOÀN CẦU FALLBACK --}}
                        <div x-show="scopeType === 'worldwide_fallback'" x-transition style="padding: 16px; background: #fffbeb; border: 1px solid #fde68a; border-radius: var(--fi-radius-md, 8px);">
                            <div style="font-weight: 600; color: #92400e; font-size: 13.5px; margin-bottom: 4px;">
                                Áp dụng cho mọi quốc gia và vùng lãnh thổ quốc tế còn lại
                            </div>
                            <p style="font-size: 12.5px; color: #b45309; margin: 0; line-height: 1.5;">
                                Quy tắc dự phòng toàn cầu (ký hiệu '*') được áp dụng khi đơn hàng quốc tế không khớp với bất kỳ khu vực cụ thể nào đã khai báo.
                            </p>
                        </div>

                    </div>
                </div>

            </div>

            {{-- SIDEBAR COLUMN --}}
            <div class="show-sidebar" style="display: flex; flex-direction: column; gap: 20px;">
                
                {{-- CARD: TRẠNG THÁI & HÀNH ĐỘNG --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Trạng thái</h3>
                    </div>
                    <div class="card-body">
                        <div style="margin-bottom: 16px;">
                            <label for="is_active" style="margin-bottom: 6px; font-weight: 600; font-size: 12.5px; display: block;">
                                Hoạt động
                            </label>
                            <select id="is_active" name="is_active" class="form-control" style="font-size: 13px;">
                                <option value="1" @selected(old('is_active', $record->is_active ?? true))>Đang hoạt động</option>
                                <option value="0" @selected(!old('is_active', $record->is_active ?? true))>Tạm ngừng</option>
                            </select>
                        </div>

                        <button class="btn btn-primary" type="submit" form="shipping-zone-form" style="width: 100%; height: 38px; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 13.5px; font-weight: 600;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                            Lưu khu vực
                        </button>
                    </div>
                </div>

                {{-- CARD: PHƯƠNG THỨC VẬN CHUYỂN LIÊN KẾT --}}
                @if ($record)
                    <div class="card">
                        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                            <h3 class="card-title">Phương thức ({{ $record->methods->count() }})</h3>
                            <a href="{{ route('admin.shipping-methods.create') }}?shipping_zone_id={{ $record->id }}" class="link-action" style="font-size: 12px; color: var(--fi-primary); font-weight: 600;">
                                + Thêm
                            </a>
                        </div>
                        <div class="card-body">
                            @if ($record->methods->isEmpty())
                                <p style="font-size: 12.5px; color: var(--fi-muted); margin: 0; font-style: italic;">
                                    Chưa cấu hình phương thức nào cho khu vực này.
                                </p>
                            @else
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    @foreach ($record->methods as $m)
                                        <div style="padding: 8px 10px; background: #f8fafc; border: 1px solid var(--fi-border-subtle); border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                                            <a href="{{ route('admin.shipping-methods.edit', $m->id) }}" style="font-size: 12.5px; font-weight: 600; color: var(--fi-text);">
                                                {{ $m->name }}
                                            </a>
                                            <span style="font-size: 11px; font-weight: 600; color: {{ $m->is_enabled ? '#15803d' : '#94a3b8' }};">
                                                {{ $m->is_enabled ? 'Bật' : 'Tắt' }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- CARD: QUY TẮC KHỚP ĐỊA CHỈ --}}
                <div class="card" style="background: #f8fafc;">
                    <div class="card-header">
                        <h3 class="card-title" style="font-size: 12.5px;">Quy tắc khớp địa chỉ</h3>
                    </div>
                    <div class="card-body" style="padding-top: 10px;">
                        <ul style="margin: 0; padding-left: 16px; font-size: 12px; color: var(--fi-muted, #64748b); line-height: 1.6;">
                            <li>Khớp ưu tiên theo tỉnh/thành phố cụ thể.</li>
                            <li>Nếu không khớp tỉnh, dùng Toàn quốc Việt Nam.</li>
                            <li>Đơn quốc tế khớp theo danh sách quốc gia.</li>
                            <li>Toàn cầu (*) áp dụng cho các nước còn lại.</li>
                        </ul>
                    </div>
                </div>

            </div>

        </div>
    </form>
</div>

<script>
function shippingZoneManager(config) {
    return {
        scopeType: 'vietnam_provinces',
        selectedProvinces: [],
        provinceSearch: '',
        selectedCountries: [],
        countrySearch: '',
        expanded: {
            north: true,
            central: false,
            south: false
        },
        northList: config.northList || [],
        centralList: config.centralList || [],
        southList: config.southList || [],
        continentMap: config.continentMap || {},

        init() {
            const locs = config.initialLocations || [];
            if (!locs || locs.length === 0) {
                this.scopeType = 'vietnam_provinces';
                return;
            }

            const first = locs[0];
            const country = (first.country || '').toUpperCase();

            if (country === '*' || country === 'ALL') {
                this.scopeType = 'worldwide_fallback';
            } else if (country === 'VN') {
                const provinces = first.provinces || [];
                if (provinces.length > 0) {
                    this.scopeType = 'vietnam_provinces';
                    this.selectedProvinces = [...provinces];
                    
                    // Auto expand sections containing selected items
                    this.expanded.north = this.countSelectedInList(this.northList) > 0;
                    this.expanded.central = this.countSelectedInList(this.centralList) > 0;
                    this.expanded.south = this.countSelectedInList(this.southList) > 0;
                } else {
                    this.scopeType = 'vietnam_nationwide';
                }
            } else if (first.countries && Array.isArray(first.countries)) {
                this.scopeType = 'international';
                this.selectedCountries = [...first.countries];
            } else if (country && country !== 'VN') {
                this.scopeType = 'international';
                this.selectedCountries = [country];
            } else {
                this.scopeType = 'vietnam_provinces';
            }
        },

        toggleExpand(region) {
            this.expanded[region] = !this.expanded[region];
        },

        toggleProvince(name) {
            const index = this.selectedProvinces.indexOf(name);
            if (index > -1) {
                this.selectedProvinces.splice(index, 1);
            } else {
                this.selectedProvinces.push(name);
            }
        },

        isAllNorthSelected() {
            return this.northList.length > 0 && this.northList.every(p => this.selectedProvinces.includes(p));
        },

        toggleAllNorth() {
            if (this.isAllNorthSelected()) {
                this.selectedProvinces = this.selectedProvinces.filter(p => !this.northList.includes(p));
            } else {
                this.northList.forEach(p => {
                    if (!this.selectedProvinces.includes(p)) this.selectedProvinces.push(p);
                });
            }
        },

        isAllCentralSelected() {
            return this.centralList.length > 0 && this.centralList.every(p => this.selectedProvinces.includes(p));
        },

        toggleAllCentral() {
            if (this.isAllCentralSelected()) {
                this.selectedProvinces = this.selectedProvinces.filter(p => !this.centralList.includes(p));
            } else {
                this.centralList.forEach(p => {
                    if (!this.selectedProvinces.includes(p)) this.selectedProvinces.push(p);
                });
            }
        },

        isAllSouthSelected() {
            return this.southList.length > 0 && this.southList.every(p => this.selectedProvinces.includes(p));
        },

        toggleAllSouth() {
            if (this.isAllSouthSelected()) {
                this.selectedProvinces = this.selectedProvinces.filter(p => !this.southList.includes(p));
            } else {
                this.southList.forEach(p => {
                    if (!this.selectedProvinces.includes(p)) this.selectedProvinces.push(p);
                });
            }
        },

        selectAllProvinces() {
            const all = [...this.northList, ...this.centralList, ...this.southList];
            all.forEach(p => {
                if (!this.selectedProvinces.includes(p)) this.selectedProvinces.push(p);
            });
        },

        deselectAllProvinces() {
            this.selectedProvinces = [];
        },

        countSelectedInList(list) {
            return list.filter(p => this.selectedProvinces.includes(p)).length;
        },

        matchesSearch(name, code) {
            if (!this.provinceSearch) return true;
            const q = this.provinceSearch.toLowerCase().trim();
            return name.toLowerCase().includes(q) || code.toLowerCase().includes(q);
        },

        // Country methods
        toggleCountry(iso) {
            const index = this.selectedCountries.indexOf(iso);
            if (index > -1) {
                this.selectedCountries.splice(index, 1);
            } else {
                this.selectedCountries.push(iso);
            }
        },

        isContinentSelected(continentCode) {
            const continentCountries = (this.continentMap[continentCode] && this.continentMap[continentCode].countries) || [];
            if (continentCountries.length === 0) return false;
            return continentCountries.every(c => c === 'VN' || this.selectedCountries.includes(c));
        },

        toggleContinent(continentCode) {
            const continentCountries = (this.continentMap[continentCode] && this.continentMap[continentCode].countries) || [];
            const targets = continentCountries.filter(c => c !== 'VN');

            if (this.isContinentSelected(continentCode)) {
                this.selectedCountries = this.selectedCountries.filter(c => !targets.includes(c));
            } else {
                targets.forEach(c => {
                    if (!this.selectedCountries.includes(c)) this.selectedCountries.push(c);
                });
            }
        },

        matchesCountrySearch(iso, name) {
            if (!this.countrySearch) return true;
            const q = this.countrySearch.toLowerCase().trim();
            return iso.toLowerCase().includes(q) || name.toLowerCase().includes(q);
        },

        serializeLocations() {
            if (this.scopeType === 'worldwide_fallback') {
                return JSON.stringify([{ country: '*', provinces: [] }]);
            }
            if (this.scopeType === 'vietnam_nationwide') {
                return JSON.stringify([{ country: 'VN', provinces: [] }]);
            }
            if (this.scopeType === 'vietnam_provinces') {
                return JSON.stringify([{ country: 'VN', provinces: this.selectedProvinces }]);
            }
            if (this.scopeType === 'international') {
                return JSON.stringify([{ countries: this.selectedCountries, provinces: [] }]);
            }
            return JSON.stringify([]);
        }
    };
}
</script>
@endsection
