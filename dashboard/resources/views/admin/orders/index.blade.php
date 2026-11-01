@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <x-admin.page-header
        :title="$title"
        :description="__('admin.list.description')"
        :exportUrl="($canImportExport ?? false) ? route($routePrefix . '.export', request()->query()) : null"
        :importUrl="($canImportExport ?? false) ? route($routePrefix . '.import') : null"
        :createUrl="($canCreate ?? true) ? route($routePrefix . '.create') : null"
    >
        @foreach (($headerActions ?? []) as $action)
            @if (($action['method'] ?? 'get') === 'post')
                <form method="post" action="{{ $action['url'] }}" style="margin: 0;">
                    @csrf
                    <button class="btn {{ $action['class'] ?? 'btn-secondary' }}" type="submit">{{ $action['label'] }}</button>
                </form>
            @else
                <a class="btn {{ $action['class'] ?? 'btn-secondary' }}" href="{{ $action['url'] }}">{{ $action['label'] }}</a>
            @endif
        @endforeach
    </x-admin.page-header>

    @if (session('status'))
        <div class="status-message">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @php
        $orderStatusOptions = [];
        foreach (['pending', 'new', 'processing', 'delivering', 'completed', 'cancelled', 'refunded'] as $s) {
            $orderStatusOptions[$s] = \App\Support\AdminLabel::orderStatus($s);
        }
        $paymentStatusOptions = [];
        foreach (['pending', 'paid', 'failed', 'refunded'] as $ps) {
            $paymentStatusOptions[$ps] = \App\Support\AdminLabel::paymentStatus($ps);
        }
        $currency = app(\App\Ecommerce\Core\Contracts\CurrencyServiceInterface::class);
    @endphp

    <x-admin.data-table :paginator="$items">
        <x-slot:toolbar>
            <x-admin.table-toolbar :resetUrl="route($routePrefix . '.index')">
                <x-admin.search-input
                    name="q"
                    :label="__('admin.order.number')"
                    placeholder="VD: 10001..."
                    width="190px"
                />

                <x-admin.search-input
                    name="customer"
                    :label="__('admin.order.customer')"
                    :placeholder="__('admin.order.customer_search_placeholder')"
                    width="220px"
                />

                <x-admin.filter-select
                    name="status"
                    :label="__('admin.order.order_status')"
                    :options="$orderStatusOptions"
                    :placeholder="__('admin.order.all')"
                    width="170px"
                />

                <x-admin.filter-select
                    name="payment_status"
                    :label="__('admin.order.payment')"
                    :options="$paymentStatusOptions"
                    :placeholder="__('admin.order.all')"
                    width="170px"
                />

                <div class="admin-filter-item" style="min-width: 148px; max-width: 165px;">
                    <label for="order-date-from">{{ __('admin.order.date_from') }}</label>
                    <input id="order-date-from" type="date" name="date_from" value="{{ request('date_from') }}">
                </div>

                <div class="admin-filter-item" style="min-width: 148px; max-width: 165px;">
                    <label for="order-date-to">{{ __('admin.order.date_to') }}</label>
                    <input id="order-date-to" type="date" name="date_to" value="{{ request('date_to') }}">
                </div>
            </x-admin.table-toolbar>
        </x-slot:toolbar>

        <x-slot:head>
            <tr>
                <th style="width: 130px;">{{ __('admin.order.order_code') }}</th>
                <th>{{ __('admin.order.customer') }}</th>
                <th style="width: 150px;">{{ __('admin.order.status') }}</th>
                <th style="width: 170px;">{{ __('admin.order.payment') }}</th>
                <th class="text-right" style="width: 150px;">{{ __('admin.order.total_price') }}</th>
                <th style="width: 155px;">{{ __('admin.order.created_at') }}</th>
                <th class="text-right" style="width: 180px;">{{ __('admin.table_actions') }}</th>
            </tr>
        </x-slot:head>

        @forelse ($items as $item)
            <tr>
                <td>
                    <a href="{{ route($routePrefix . '.show', $item->id) }}" class="mono" style="font-weight: 700; color: var(--fi-primary);">
                        #{{ $item->number }}
                    </a>
                </td>
                <td>
                    <div style="font-weight: 600; color: var(--fi-text);">{{ $item->customer_display_name }}</div>
                    <div class="cell-muted">
                        {{ $item->user_id ? __('admin.order.member') : __('admin.order.guest') }}
                    </div>
                </td>
                <td>
                    @php
                        $orderStatus = $item->status instanceof \BackedEnum ? $item->status->value : $item->status;
                    @endphp
                    <x-admin.status-badge :status="$orderStatus" :label="\App\Support\AdminLabel::orderStatus($orderStatus)" />
                </td>
                <td>
                    @if($item->payments->count() > 0)
                        @php($latestPayment = $item->payments->last())
                        <x-admin.status-badge :status="$latestPayment->status" :label="\App\Support\AdminLabel::paymentStatus($latestPayment->status)" />
                        <span class="cell-muted mono" style="margin-left: 4px; text-transform: uppercase;">{{ $latestPayment->method }}</span>
                    @else
                        <x-admin.status-badge status="pending" :label="\App\Support\AdminLabel::paymentStatus('pending')" />
                    @endif
                </td>
                <td class="text-right font-bold mono">{{ $currency->format($item->total, true) }}</td>
                <td class="cell-muted mono">{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                <td class="text-right">
                    <x-admin.row-actions
                        :viewUrl="route($routePrefix . '.show', $item->id)"
                        :editUrl="($canEdit ?? true) ? route($routePrefix . '.edit', $item->id) : null"
                        :deleteUrl="($canDelete ?? true) ? route($routePrefix . '.destroy', $item->id) : null"
                    />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="empty-state">{{ __('admin.messages.empty_state') }}</td>
            </tr>
        @endforelse
    </x-admin.data-table>
@endsection
