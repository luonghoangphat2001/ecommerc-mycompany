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
        <form method="get" style="background: #f8fafc; border-bottom: 1px solid var(--fi-border); padding: 20px; border-radius: 8px 8px 0 0; margin: -22px -22px 22px -22px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <!-- Search -->
                <div>
                    <label style="font-size: 12px; color: #64748b; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; display: block;">{{ __('admin.order.number') }}</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="VD: 10001...">
                </div>

                <!-- Customer -->
                <div>
                    <label style="font-size: 12px; color: #64748b; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; display: block;">{{ __('admin.order.customer') }}</label>
                    <input type="text" name="customer" value="{{ request('customer') }}" placeholder="{{ __('admin.order.customer_search_placeholder') }}">
                </div>
                
                <!-- Status -->
                <div>
                    <label style="font-size: 12px; color: #64748b; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; display: block;">{{ __('admin.order.order_status') }}</label>
                    <select name="status">
                        <option value="">{{ __('admin.order.all') }}</option>
                        @foreach(['pending', 'new', 'processing', 'delivering', 'completed', 'cancelled', 'refunded'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ \App\Support\AdminLabel::orderStatus($s) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Payment Status -->
                <div>
                    <label style="font-size: 12px; color: #64748b; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; display: block;">{{ __('admin.order.payment') }}</label>
                    <select name="payment_status">
                        <option value="">{{ __('admin.order.all') }}</option>
                        @foreach(['pending', 'paid', 'failed', 'refunded'] as $ps)
                            <option value="{{ $ps }}" @selected(request('payment_status') === $ps)>{{ \App\Support\AdminLabel::paymentStatus($ps) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date range -->
                <div>
                    <label style="font-size: 12px; color: #64748b; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; display: block;">{{ __('admin.order.date_from') }}</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div>
                    <label style="font-size: 12px; color: #64748b; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; display: block;">{{ __('admin.order.date_to') }}</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}">
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 16px;">
                <a href="{{ route($routePrefix . '.index') }}" class="btn btn-secondary" style="padding: 8px 16px; font-weight: 600; min-height: 38px; color: #475569;">{{ __('admin.actions.reset') }}</a>
                <button class="btn btn-primary" type="submit" style="padding: 8px 24px; font-weight: 600; min-height: 38px;">{{ __('admin.actions.filter_data') }}</button>
            </div>
        </form>

        @if ($canImportExport ?? false)
        <div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
            <form method="post" action="{{ route($routePrefix . '.import') }}" class="import-form" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" accept=".csv,text/csv" style="height: 38px; max-width: 250px;">
                <button class="btn btn-secondary" type="submit">{{ __('admin.actions.import_csv') }}</button>
            </form>
        </div>
        @endif

        @error('file')
            <div class="error import-error">{{ $message }}</div>
        @enderror

        <div class="table-wrap">
            <table style="min-width: 980px;">
                <thead>
                    <tr>
                        <th>{{ __('admin.order.order_code') }}</th>
                        <th>{{ __('admin.order.customer') }}</th>
                        <th>{{ __('admin.order.status') }}</th>
                        <th>{{ __('admin.order.payment') }}</th>
                        <th>{{ __('admin.order.total_price') }}</th>
                        <th>{{ __('admin.order.created_at') }}</th>
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
                                    <div class="cell-muted" style="font-size:0.85em;">{{ __('admin.order.member') }}</div>
                                @else
                                    <div class="cell-muted" style="font-size:0.85em;">{{ __('admin.order.guest') }}</div>
                                @endif
                            </td>
                            <td>
                                    @php
                                        $orderStatus = $item->status instanceof \BackedEnum ? $item->status->value : $item->status;
                                    @endphp
                                    <span class="badge badge-{{ $orderStatus }}">{{ \App\Support\AdminLabel::orderStatus($orderStatus) }}</span>
                            </td>
                            <td>
                                @if($item->payments->count() > 0)
                                    @php
                                        $latestPayment = $item->payments->last();
                                    @endphp
                                    <span class="badge badge-{{ $latestPayment->status }}">{{ \App\Support\AdminLabel::paymentStatus($latestPayment->status) }}</span>
                                    <div class="cell-muted" style="font-size:0.85em;">{{ $latestPayment->method }}</div>
                                @else
                                    <span class="badge badge-pending">{{ \App\Support\AdminLabel::paymentStatus('pending') }}</span>
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
