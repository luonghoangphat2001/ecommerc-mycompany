@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <style>
        .show-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 24px;
        }
        @media (min-width: 1024px) {
            .show-layout {
                grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
                align-items: start;
            }
        }
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            margin-bottom: 24px;
        }
        .card-header {
            padding: 16px 24px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        .card-title {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
        }
        
        .field-list {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 0;
        }
        @media (min-width: 640px) {
            .field-list {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        .show-main .field-list {
            @media (min-width: 1280px) {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        .field-group {
            padding: 16px 24px;
            border-bottom: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
        }
        .field-group:last-child {
            border-bottom: none;
        }
        .field-label {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .field-value {
            font-size: 15px;
            color: #1e293b;
            line-height: 1.5;
            word-break: break-word;
        }
        
        .preview-img { max-height: 160px; border-radius: 8px; object-fit: contain; background: #f8fafc; padding: 4px; border: 1px solid #e2e8f0; max-width: 100%; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .val-empty { color: #94a3b8; font-style: italic; font-size: 14px; }
        .val-html { background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0; overflow-x: auto; margin-top: 8px; font-size: 14px; }
        .badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; }
        .bg-green { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .bg-red { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .page-title { margin: 0; font-size: 24px; font-weight: 700; color: #0f172a; }
    </style>

    <div class="actions-bar">
        <h1 class="page-title">{{ $title }}</h1>
        <div class="actions" style="display: flex; gap: 12px;">
            <a class="btn btn-secondary" href="{{ route($routePrefix . '.index') }}" style="background: #fff; border: 1px solid #cbd5e1; color: #475569;">{{ __('admin.actions.back') }}</a>
            @if ($canEdit ?? true)
                <a class="btn btn-primary" href="{{ route($routePrefix . '.edit', $record->id) }}" style="background: #3b82f6; color: #fff; border: none;">{{ __('admin.actions.edit') }}</a>
            @endif
        </div>
    </div>

    @php
        $mainGroups = [];
        $sideGroups = [];
        foreach ($groups as $key => $group) {
            if (in_array($key, ['media', 'taxonomy', 'seo'])) {
                $sideGroups[$key] = $group;
            } else {
                $mainGroups[$key] = $group;
            }
        }
    @endphp

    <div class="show-layout">
        <div class="show-main">
            @foreach ($mainGroups as $key => $group)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __($group['label']) }}</h3>
                    </div>
                    <div class="field-list">
                        @foreach ($group['fields'] as $name)
                            @php
                                $field = $fields[$name] ?? null;
                                if (!$field) continue;
                                $type = $field['type'] ?? 'text';
                                $value = data_get($record, $name);
                                $isFullWidth = in_array($type, ['editor', 'textarea', 'image']);
                            @endphp
                            <div class="field-group" style="{{ $isFullWidth ? 'grid-column: 1 / -1; border-right: none;' : '' }}">
                                <div class="field-label">{{ $field['label'] ?? $name }}</div>
                                <div class="field-value">
                                    @include('admin.crud.show_field', ['type' => $type, 'value' => $value, 'record' => $record])
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        <div class="show-sidebar">
            @foreach ($sideGroups as $key => $group)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __($group['label']) }}</h3>
                    </div>
                    <div class="field-list" style="grid-template-columns: 1fr;">
                        @foreach ($group['fields'] as $name)
                            @php
                                $field = $fields[$name] ?? null;
                                if (!$field) continue;
                                $type = $field['type'] ?? 'text';
                                $value = data_get($record, $name);
                            @endphp
                            <div class="field-group" style="grid-column: 1 / -1; border-right: none; padding: 12px 20px;">
                                <div class="field-label">{{ $field['label'] ?? $name }}</div>
                                <div class="field-value">
                                    @include('admin.crud.show_field', ['type' => $type, 'value' => $value, 'record' => $record])
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
