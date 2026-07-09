@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <style>
        .show-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 20px;
        }
        @media (min-width: 1024px) {
            .show-layout {
                grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
                align-items: start;
            }
        }
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--fi-border);
            background: #f8fafc;
            border-radius: 12px 12px 0 0;
        }
        .card-title {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: var(--fi-text);
        }
        .card.mb-4 { margin-bottom: 20px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .details-table { width: 100%; border-collapse: collapse; }
        .details-table th { width: 30%; background: #fdfdfd; font-weight: 600; color: #475569; }
        .details-table th, .details-table td {
            padding: 12px 20px;
            border-bottom: 1px solid var(--fi-border);
            text-align: left;
            vertical-align: top;
        }
        .details-table tr:last-child th, .details-table tr:last-child td { border-bottom: none; }
        .preview-img { max-height: 160px; border-radius: 8px; object-fit: contain; background: #f1f5f9; padding: 4px; border: 1px solid var(--fi-border); max-width: 100%; }
        .val-empty { color: var(--fi-muted); font-style: italic; }
        .val-html { background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid var(--fi-border); overflow-x: auto; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .bg-green { background: #dcfce7; color: #166534; }
        .bg-red { background: #fee2e2; color: #991b1b; }
    </style>

    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
        </div>
        <div class="actions">
            <a class="btn btn-secondary" href="{{ route($routePrefix . '.index') }}">{{ __('admin.actions.back') }}</a>
            @if ($canEdit ?? true)
                <a class="btn" href="{{ route($routePrefix . '.edit', $record->id) }}">{{ __('admin.actions.edit') }}</a>
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
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">{{ __($group['label']) }}</h3>
                    </div>
                    <div class="table-wrap" style="border-radius: 0 0 12px 12px;">
                        <table class="details-table">
                            <tbody>
                                @foreach ($group['fields'] as $name)
                                    @php
                                        $field = $fields[$name] ?? null;
                                        if (!$field) continue;
                                        $type = $field['type'] ?? 'text';
                                        $value = data_get($record, $name);
                                    @endphp
                                    <tr>
                                        <th>{{ $field['label'] ?? $name }}</th>
                                        <td>
                                            @include('admin.crud.show_field', ['type' => $type, 'value' => $value, 'record' => $record])
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="show-sidebar">
            @foreach ($sideGroups as $key => $group)
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">{{ __($group['label']) }}</h3>
                    </div>
                    <div class="table-wrap" style="border-radius: 0 0 12px 12px;">
                        <table class="details-table" style="table-layout: fixed; width: 100%;">
                            <tbody>
                                @foreach ($group['fields'] as $name)
                                    @php
                                        $field = $fields[$name] ?? null;
                                        if (!$field) continue;
                                        $type = $field['type'] ?? 'text';
                                        $value = data_get($record, $name);
                                    @endphp
                                    <tr>
                                        <td style="display: block; border-bottom: none; padding-bottom: 4px; font-weight: 600; color: var(--fi-muted);">{{ $field['label'] ?? $name }}</td>
                                    </tr>
                                    <tr>
                                        <td style="display: block; padding-top: 0;">
                                            @include('admin.crud.show_field', ['type' => $type, 'value' => $value, 'record' => $record])
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
