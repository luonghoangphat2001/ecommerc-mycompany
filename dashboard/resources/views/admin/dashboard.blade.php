@extends('admin.layouts.app', ['title' => $title ?? __('admin.dashboard.title')])

@php
    $months = [
        __('Jan'), __('Feb'), __('Mar'), __('Apr'), __('May'), __('Jun'),
        __('Jul'), __('Aug'), __('Sep'), __('Oct'), __('Nov'), __('Dec'),
    ];
    $revenue = array_values((array) ($revenueSeries ?? []));
    $revenueMax = max(array_merge([1], $revenue));
    $topProductsList = collect($topProducts ?? []);
    $topProductsMax = max(array_merge([1], $topProductsList->pluck('revenue')->map(fn ($value) => (float) $value)->all()));
    $topProductsDonutList = collect($topProductsDonut ?? []);
    $donutTotal = max(1, $topProductsDonutList->sum('total_sold'));
    $donutColors = ['#d97706', '#f59e0b', '#fbbf24', '#f97316', '#84cc16', '#14b8a6'];
    $donutSegments = [];
    $offset = 0;
    foreach ($topProductsDonutList as $index => $item) {
        $percentage = ($item->total_sold / $donutTotal) * 100;
        $donutSegments[] = $donutColors[$index % count($donutColors)] . ' ' . $offset . '% ' . ($offset + $percentage) . '%';
        $offset += $percentage;
    }
    $donutBackground = $donutSegments !== [] ? 'conic-gradient(' . implode(', ', $donutSegments) . ')' : '#e2e8f0';
@endphp

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('admin.dashboard.title') }}</h1>
            <p class="page-description">{{ __('admin.dashboard.description') }}</p>
        </div>
    </div>

    <div class="stats">
        <div class="stat">
            <div class="label">{{ __('admin.dashboard.stats.users') }}</div>
            <div class="value">{{ number_format($totalUsers) }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('admin.dashboard.stats.products') }}</div>
            <div class="value">{{ number_format($totalProducts) }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('admin.dashboard.stats.orders') }}</div>
            <div class="value">{{ number_format($totalOrders) }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('admin.dashboard.stats.revenue') }}</div>
            <div class="value">{{ number_format($totalRevenue) }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('admin.dashboard.stats.pending_orders') }}</div>
            <div class="value">{{ number_format($pendingOrders) }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('admin.dashboard.stats.paid_payments') }}</div>
            <div class="value">{{ number_format($paidPayments) }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('admin.dashboard.stats.content') }}</div>
            <div class="value">{{ number_format($totalPosts) }} / {{ number_format($totalPages) }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('admin.dashboard.stats.media') }}</div>
            <div class="value">{{ number_format($totalMedia) }} / {{ number_format($totalTaxClasses) }} / {{ number_format($totalWebhooks) }}</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <div class="panel-heading">
                <div>
                    <h2>{{ __('admin.dashboard.line_revenue') }}</h2>
                    <p>{{ __('admin.dashboard.labels.value') }}</p>
                </div>
            </div>
            <svg viewBox="0 0 1000 260" width="100%" height="260" role="img" aria-label="{{ __('admin.dashboard.line_revenue') }}">
                <defs>
                    <linearGradient id="revenueFill" x1="0" x2="0" y1="0" y2="1">
                        <stop offset="0%" stop-color="#f59e0b" stop-opacity="0.32" />
                        <stop offset="100%" stop-color="#f59e0b" stop-opacity="0" />
                    </linearGradient>
                </defs>
                @for ($i = 0; $i < 11; $i++)
                    <line x1="{{ 40 + $i * 88 }}" y1="20" x2="{{ 40 + $i * 88 }}" y2="220" stroke="#f1f5f9" />
                @endfor
                @for ($i = 0; $i < 5; $i++)
                    <line x1="40" y1="{{ 20 + $i * 50 }}" x2="960" y2="{{ 20 + $i * 50 }}" stroke="#f1f5f9" />
                @endfor
                @if ($revenue !== [])
                    @php
                        $points = [];
                        $areaPoints = ['40,220'];
                        foreach ($revenue as $index => $amount) {
                            $x = 40 + ($index * 80);
                            $y = 220 - (($amount / $revenueMax) * 180);
                            $points[] = $x . ',' . $y;
                            $areaPoints[] = $x . ',' . $y;
                        }
                        $areaPoints[] = (40 + (count($revenue) - 1) * 80) . ',220';
                    @endphp
                    <polygon points="{{ implode(' ', $areaPoints) }}" fill="url(#revenueFill)" />
                    <polyline points="{{ implode(' ', $points) }}" fill="none" stroke="#d97706" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                    @foreach ($revenue as $index => $amount)
                        @php
                            $x = 40 + ($index * 80);
                            $y = 220 - (($amount / $revenueMax) * 180);
                        @endphp
                        <circle cx="{{ $x }}" cy="{{ $y }}" r="5" fill="#d97706" />
                        <text x="{{ $x }}" y="245" text-anchor="middle" fill="#64748b" font-size="11">{{ $months[$index] ?? ($index + 1) }}</text>
                    @endforeach
                @endif
            </svg>
        </div>

        <div class="card">
            <div class="panel-heading">
                <div>
                    <h2>{{ __('admin.dashboard.column_products') }}</h2>
                    <p>{{ __('admin.dashboard.labels.name') }}</p>
                </div>
            </div>
            <div style="display:grid; gap:10px;">
                @forelse ($topProductsList as $item)
                    @php
                        $revenuePercent = $topProductsMax > 0 ? max(6, (($item->revenue ?? 0) / $topProductsMax) * 100) : 6;
                    @endphp
                    <div>
                        <div style="display:flex; justify-content:space-between; gap:12px; margin-bottom:6px;">
                            <strong style="font-size:13px;">{{ $item->product_name ?? __('admin.dashboard.labels.name') }}</strong>
                            <span class="cell-muted">{{ number_format($item->revenue ?? 0) }}</span>
                        </div>
                        <div style="height:12px; border-radius:999px; background:#e2e8f0; overflow:hidden;">
                            <div style="height:100%; width:{{ min(100, $revenuePercent) }}%; background:linear-gradient(90deg, #d97706, #f59e0b); border-radius:999px;"></div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">{{ __('admin.messages.empty_state') }}</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="dashboard-grid" style="margin-top:16px;">
        <div class="card">
            <div class="panel-heading">
                <div>
                    <h2>{{ __('admin.dashboard.donut_distribution') }}</h2>
                    <p>{{ __('admin.dashboard.labels.percentage') }}</p>
                </div>
            </div>
            <div style="display:grid; grid-template-columns: 240px minmax(0, 1fr); gap:18px; align-items:center;">
                <div style="display:grid; place-items:center;">
                    <div style="width:220px; height:220px; border-radius:999px; background: {{ $donutBackground }}; display:grid; place-items:center; box-shadow: inset 0 0 0 18px #fff;">
                        <div style="width:118px; height:118px; border-radius:999px; background:#fff; display:grid; place-items:center; text-align:center; box-shadow: 0 0 0 1px #e2e8f0;">
                            <div>
                                <div style="font-size:22px; font-weight:900;">{{ count($topProductsDonutList) }}</div>
                                <div class="cell-muted" style="font-size:12px;">{{ __('admin.dashboard.labels.name') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="metric-list">
                    @forelse ($topProductsDonutList as $index => $item)
                        @php
                            $percent = round((($item->total_sold ?? 0) / $donutTotal) * 100, 1);
                        @endphp
                        <div class="metric-row">
                            <span style="display:flex; align-items:center; gap:10px;">
                                <span style="width:12px; height:12px; border-radius:999px; background: {{ $donutColors[$index % count($donutColors)] }};"></span>
                                {{ $item->product_name ?? '-' }}
                            </span>
                            <strong>{{ $percent }}%</strong>
                        </div>
                    @empty
                        <div class="empty-state">{{ __('admin.messages.empty_state') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card">
            <div class="panel-heading">
                <div>
                    <h2>{{ __('admin.dashboard.order_status') }}</h2>
                    <p>{{ __('admin.dashboard.labels.status') }}</p>
                </div>
            </div>
            <div class="metric-list">
                @forelse ($orderDistribution as $status => $total)
                    <div class="metric-row">
                        <span>{{ $status ?: 'unknown' }}</span>
                        <strong>{{ number_format($total) }}</strong>
                    </div>
                @empty
                    <div class="empty-state">{{ __('admin.messages.empty_state') }}</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="dashboard-grid" style="margin-top:16px;">
        <div class="card">
            <h2 class="section-title">{{ __('admin.dashboard.latest_orders') }}</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('admin.dashboard.labels.id') }}</th>
                            <th>{{ __('admin.dashboard.labels.code') }}</th>
                            <th>{{ __('admin.dashboard.labels.status') }}</th>
                            <th>{{ __('admin.dashboard.labels.total') }}</th>
                            <th>{{ __('admin.dashboard.labels.created_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($latestOrders as $order)
                            @php
                                $status = $order->status instanceof \BackedEnum ? $order->status->value : $order->status;
                            @endphp
                            <tr>
                                <td class="cell-muted">#{{ $order->id }}</td>
                                <td>{{ $order->number ?: '-' }}</td>
                                <td>{{ $status ?: '-' }}</td>
                                <td>{{ number_format($order->total) }} {{ $order->currency }}</td>
                                <td>{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">{{ __('admin.messages.empty_state') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h2 class="section-title">{{ __('admin.dashboard.order_status') }}</h2>
            <div class="metric-list">
                @forelse ($orderStatusCounts as $status => $total)
                    <div class="metric-row">
                        <span>{{ $status ?: 'unknown' }}</span>
                        <strong>{{ number_format($total) }}</strong>
                    </div>
                @empty
                    <div class="empty-state">{{ __('admin.messages.empty_state') }}</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
