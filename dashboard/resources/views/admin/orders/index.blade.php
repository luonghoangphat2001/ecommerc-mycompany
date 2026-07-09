@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            <p class="page-description">{{ __('admin.list.description') }}</p>
        </div>
        <div class="actions">
            @foreach (($headerActions ?? []) as $action)
                @if (($action['method'] ?? 'get') === 'post')
                    <form method="post" action="{{ $action['url'] }}">
                        @csrf
                        <button class="btn {{ $action['class'] ?? 'btn-secondary' }}" type="submit">{{ $action['label'] }}</button>
                    </form>
                @else
                    <a class="btn {{ $action['class'] ?? 'btn-secondary' }}" href="{{ $action['url'] }}">{{ $action['label'] }}</a>
                @endif
            @endforeach
            @if ($canImportExport ?? false)
                <a class="btn btn-secondary" href="{{ route($routePrefix . '.export', request()->query()) }}">{{ __('admin.actions.export_csv') }}</a>
            @endif
            @if ($canCreate ?? true)
                <a class="btn" href="{{ route($routePrefix . '.create') }}">{{ __('admin.actions.create') }}</a>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="status-message">{{ session('status') }}</div>
    @endif

    <div class="table-panel card">
        <div class="toolbar-row" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 15px;">
            <form method="get" class="searchbar" style="display: flex; flex-wrap: wrap; gap: 10px; width: 100%; align-items: center;">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('admin.actions.search') }} mã đơn hàng...">
                
                <select name="status">
                    <option value="">-- Trạng thái --</option>
                    @foreach(['pending', 'new', 'processing', 'delivering', 'completed', 'cancelled', 'refunded'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>

                <select name="payment_status">
                    <option value="">-- Thanh toán --</option>
                    @foreach(['pending', 'paid', 'failed', 'refunded'] as $ps)
                        <option value="{{ $ps }}" @selected(request('payment_status') === $ps)>{{ ucfirst($ps) }}</option>
                    @endforeach
                </select>

                <input type="text" name="customer" value="{{ request('customer') }}" placeholder="Tên/Email/SĐT KH">

                <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="Từ ngày">
                <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="Đến ngày">

                <button class="btn btn-secondary" type="submit">Lọc</button>
                <a href="{{ route($routePrefix . '.index') }}" class="btn" style="background: transparent; color: var(--text-color); border: 1px solid var(--border-color);">Xóa</a>
            </form>

            @if ($canImportExport ?? false)
                <form method="post" action="{{ route($routePrefix . '.import') }}" class="import-form" enctype="multipart/form-data" style="margin-left: auto;">
                    @csrf
                    <input type="file" name="file" accept=".csv,text/csv">
                    <button class="btn btn-secondary" type="submit">{{ __('admin.actions.import_csv') }}</button>
                </form>
            @endif
        </div>

        @error('file')
            <div class="error import-error">{{ $message }}</div>
        @enderror

        <div class="table-wrap">
            <table style="min-width: 980px;">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Trạng thái</th>
                        <th>Thanh toán</th>
                        <th>Tổng tiền</th>
                        <th>Ngày tạo</th>
                        <th>{{ __('admin.table_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $currency = app(\App\Ecommerce\Core\Contracts\CurrencyServiceInterface::class);
                    @endphp
                    @forelse ($items as $item)
                        <tr>
                            <td><strong>#{{ $item->number }}</strong></td>
                            <td>
                                <div>{{ $item->customer_display_name }}</div>
                                @if($item->user_id)
                                    <div class="cell-muted" style="font-size:0.85em;">Member</div>
                                @else
                                    <div class="cell-muted" style="font-size:0.85em;">Guest</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $item->status instanceof \BackedEnum ? $item->status->value : $item->status }}">{{ $item->status instanceof \BackedEnum ? $item->status->value : $item->status }}</span>
                            </td>
                            <td>
                                @if($item->payments->count() > 0)
                                    @php
                                        $latestPayment = $item->payments->last();
                                    @endphp
                                    <span class="badge badge-{{ $latestPayment->status }}">{{ $latestPayment->status }}</span>
                                    <div class="cell-muted" style="font-size:0.85em;">{{ $latestPayment->method }}</div>
                                @else
                                    <span class="badge badge-pending">pending</span>
                                @endif
                            </td>
                            <td>{{ $currency->format($item->total, true) }}</td>
                            <td>{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="actions">
                                    <a class="link-action" href="{{ route($routePrefix . '.show', $item->id) }}">{{ __('admin.actions.view') }}</a>
                                    @if ($canEdit ?? true)
                                        <a class="link-action" href="{{ route($routePrefix . '.edit', $item->id) }}">{{ __('admin.actions.edit') }}</a>
                                    @endif
                                    @if ($canDelete ?? true)
                                        <form method="post" action="{{ route($routePrefix . '.destroy', $item->id) }}">
                                            @csrf
                                            @method('delete')
                                            <button class="link-action link-danger" type="submit" onclick="return confirm('{{ __('admin.messages.confirm_delete') }}')">{{ __('admin.actions.delete') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%" class="empty-state">{{ __('admin.messages.empty_state') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $items->links('vendor.pagination.admin') }}</div>
    </div>
@endsection
