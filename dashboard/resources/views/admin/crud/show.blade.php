@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            <p class="page-description">Xem nhanh thông tin bản ghi #{{ $record->id }}.</p>
        </div>
        <div class="actions">
            <a class="btn btn-secondary" href="{{ route($routePrefix . '.index') }}">Quay lại</a>
            @if ($canEdit ?? true)
                <a class="btn" href="{{ route($routePrefix . '.edit', $record->id) }}">Sửa</a>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="details-table">
                <tbody>
                    <tr>
                        <th>ID</th>
                        <td>#{{ $record->id }}</td>
                    </tr>
                    @foreach ($fields as $name => $field)
                        @php($value = data_get($record, $name))
                        <tr>
                            <th>{{ $field['label'] ?? $name }}</th>
                            <td>{!! nl2br(e(\App\Support\AdminValue::format($value))) !!}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <th>Created</th>
                        <td>{{ $record->created_at }}</td>
                    </tr>
                    <tr>
                        <th>Updated</th>
                        <td>{{ $record->updated_at }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
