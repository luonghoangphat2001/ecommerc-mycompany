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
    $donutColors = ['#004687', '#0284c7', '#059669', '#fbae17', '#475569', '#94a3b8'];

    $statusCollection = collect(!empty($orderStatusCounts) ? $orderStatusCounts : ($orderDistribution ?? []));
    $statusTotalCount = max(1, $statusCollection->sum());
@endphp

@section('content')
    @if (session('status'))
        <div class="status-message">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div style="background: var(--fi-danger-bg); border: 1px solid var(--fi-danger-border); color: #b91c1c; padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; font-weight: 600; font-size: 12.5px; display: flex; align-items: center; gap: 8px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- 1. EXECUTIVE PAGE HEADER -->
    <div class="page-header" style="margin-bottom: 18px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <h1 class="page-title">{{ __('admin.dashboard.title') }}</h1>
                <span class="badge badge-success">{{ __('admin.executive.active_departments') }}</span>
            </div>
            <p class="page-description">{{ __('admin.dashboard.description') }}</p>
        </div>

        <div class="actions tabular-nums">
            <a href="#exception-inbox" class="btn btn-secondary btn-sm">
                <span style="width: 6px; height: 6px; border-radius: 999px; background: {{ $pendingProposals->count() > 0 ? '#dc2626' : '#059669' }};"></span>
                <span>{{ __('admin.executive.pending_approvals') }}: <strong>{{ $pendingProposals->count() }}</strong></span>
            </a>
            <a href="{{ route('admin.workspace.show', 'ops') }}" class="btn btn-secondary btn-sm">
                <span style="width: 6px; height: 6px; border-radius: 999px; background: {{ $urgentIncidents->count() > 0 ? '#d97706' : '#059669' }};"></span>
                <span>{{ __('admin.executive.open_incidents') }}: <strong>{{ $urgentIncidents->count() }}</strong></span>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-primary btn-sm">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <span>{{ __('admin.executive.manage_orders') }}</span>
            </a>
        </div>
    </div>

    <!-- 2. PRIMARY 4-KPI COMMERCE & FINANCIAL STRIP (STRIPE / SHOPIFY STYLE) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 14px; margin-bottom: 14px;" class="tabular-nums">
        <!-- Total Revenue -->
        <div class="stat">
            <div class="label">
                <span>{{ __('admin.dashboard.stats.revenue') }}</span>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#004687" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="value" style="color: #091e34;">{{ number_format($totalRevenue) }} <span style="font-size: 12px; font-weight: 600; color: var(--fi-muted);">VND</span></div>
            <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--fi-border-subtle); display: flex; justify-content: space-between; align-items: center; font-size: 11.5px; color: var(--fi-muted);">
                <span>{{ __('admin.dashboard.stats.paid_payments') }}</span>
                <strong style="color: #047857;">{{ number_format($paidPayments) }} {{ __('admin.executive.transactions_unit') }}</strong>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="stat">
            <div class="label">
                <span>{{ __('admin.dashboard.stats.orders') }}</span>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </div>
            <div class="value">{{ number_format($totalOrders) }}</div>
            <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--fi-border-subtle); display: flex; justify-content: space-between; align-items: center; font-size: 11.5px; color: var(--fi-muted);">
                <span>{{ __('admin.dashboard.stats.pending_orders') }}</span>
                <span class="badge {{ $pendingOrders > 0 ? 'badge-warning' : 'badge-secondary' }}" style="padding: 1px 6px; font-size: 11px;">{{ number_format($pendingOrders) }} {{ __('admin.executive.pending_unit') }}</span>
            </div>
        </div>

        <!-- Products & Catalog -->
        <div class="stat">
            <div class="label">
                <span>{{ __('admin.dashboard.stats.products') }}</span>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
            </div>
            <div class="value">{{ number_format($totalProducts) }}</div>
            <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--fi-border-subtle); display: flex; justify-content: space-between; align-items: center; font-size: 11.5px; color: var(--fi-muted);">
                <span>{{ __('admin.executive.low_stock_alert') }}</span>
                <span class="badge {{ $lowStockProductsCount > 0 ? 'badge-danger' : 'badge-success' }}" style="padding: 1px 6px; font-size: 11px;">{{ number_format($lowStockProductsCount) }} SKU</span>
            </div>
        </div>

        <!-- Customers & System Content -->
        <div class="stat">
            <div class="label">
                <span>{{ __('admin.dashboard.stats.users') }}</span>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="value">{{ number_format($totalUsers) }}</div>
            <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--fi-border-subtle); display: flex; justify-content: space-between; align-items: center; font-size: 11.5px; color: var(--fi-muted);">
                <span>{{ __('admin.dashboard.stats.content') }} / {{ __('admin.dashboard.stats.media') }}</span>
                <strong style="color: var(--fi-text-secondary);">{{ number_format($totalPosts) }} {{ __('admin.executive.posts_unit') }} &bull; {{ number_format($totalMedia) }} {{ __('admin.executive.files_unit') }}</strong>
            </div>
        </div>
    </div>

    <!-- 3. DEPARTMENT WORKSPACES COMMAND GRID -->
    <div class="card" style="padding: 16px 18px; margin-bottom: 18px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 11.5px; font-weight: 700; color: var(--fi-text); text-transform: uppercase; letter-spacing: 0.04em;">
                    {{ __('admin.executive.command_grid_title') }}
                </span>
                <span style="color: #cbd5e1;">&bull;</span>
                <span style="font-size: 12px; color: var(--fi-muted);">{{ __('admin.executive.command_grid_subtitle') }}</span>
            </div>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <a href="{{ route('admin.financial-proposals.index') }}" class="link-action">{{ __('admin.executive.expense_inbox') }}</a>
                <a href="{{ route('admin.incidents.index') }}" class="link-action">{{ __('admin.executive.incident_mgmt') }}</a>
                <a href="{{ route('admin.department-audit-logs.index') }}" class="link-action">{{ __('admin.executive.audit_log_short') }}</a>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 10px;">
            @php
                $agentCards = [
                    ['code' => 'cfo', 'name' => __('admin.executive.dept_cfo_name'), 'role' => __('admin.executive.dept_cfo_role'), 'tag' => 'CFO'],
                    ['code' => 'logistics', 'name' => __('admin.executive.dept_logistics_name'), 'role' => __('admin.executive.dept_logistics_role'), 'tag' => 'LOG'],
                    ['code' => 'ops', 'name' => __('admin.executive.dept_ops_name'), 'role' => __('admin.executive.dept_ops_role'), 'tag' => 'OPS'],
                    ['code' => 'cskh', 'name' => __('admin.executive.dept_cskh_name'), 'role' => __('admin.executive.dept_cskh_role'), 'tag' => 'CSKH'],
                    ['code' => 'rnd', 'name' => __('admin.executive.dept_rnd_name'), 'role' => __('admin.executive.dept_rnd_role'), 'tag' => 'R&D'],
                    ['code' => 'hr', 'name' => __('admin.executive.dept_hr_name'), 'role' => __('admin.executive.dept_hr_role'), 'tag' => 'HR'],
                ];
            @endphp
            @foreach ($agentCards as $agent)
                <a href="{{ route('admin.workspace.show', $agent['code']) }}" style="background: #f8fafc; border: 1px solid var(--fi-border); border-radius: 8px; padding: 11px 13px; display: flex; flex-direction: column; justify-content: space-between; text-decoration: none; transition: border-color 0.14s ease, background 0.14s ease, box-shadow 0.14s ease;" onmouseover="this.style.background='#ffffff'; this.style.borderColor='#004687';" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='var(--fi-border)';">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                            <span class="mono" style="font-size: 10px; font-weight: 700; color: var(--fi-primary); background: var(--fi-primary-soft); border: 1px solid var(--fi-primary-border); padding: 1px 6px; border-radius: 4px;">{{ $agent['tag'] }}</span>
                            <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; color: #047857; font-weight: 600;">
                                <span style="width: 5px; height: 5px; border-radius: 999px; background: #059669;"></span> Online
                            </span>
                        </div>
                        <strong style="color: var(--fi-text); font-size: 12.5px; display: block;">{{ $agent['name'] }}</strong>
                        <span style="color: var(--fi-muted); font-size: 11.5px; display: block; margin-top: 2px;">{{ $agent['role'] }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- 4. EXCEPTION INBOX & INCIDENT RADAR -->
    <div id="exception-inbox" class="dashboard-grid" style="margin-top: 0; margin-bottom: 18px;">
        <!-- Left: CEO 1-Click Financial Proposals -->
        <div class="table-panel card" style="margin-bottom: 0;">
            <div style="padding: 14px 18px; border-bottom: 1px solid var(--fi-border); background: #fcfcfd; display: flex; justify-content: space-between; align-items: center; gap: 12px;">
                <div>
                    <h2 style="margin: 0; font-size: 14px; font-weight: 700; color: var(--fi-text);">
                        {{ __('admin.executive.proposals_title') }}
                    </h2>
                    <p style="margin: 2px 0 0; font-size: 11.5px; color: var(--fi-muted);">{{ __('admin.executive.proposals_subtitle') }}</p>
                </div>
                <span class="badge {{ $pendingProposals->count() > 0 ? 'badge-danger' : 'badge-success' }}">
                    {{ $pendingProposals->count() }} {{ __('admin.executive.awaiting_approval') }}
                </span>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('admin.executive.col_proposal') }}</th>
                            <th>{{ __('admin.executive.col_department') }}</th>
                            <th class="text-right">{{ __('admin.executive.col_amount') }}</th>
                            <th class="text-center">{{ __('admin.executive.col_priority') }}</th>
                            <th class="text-right">{{ __('admin.executive.col_decision') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingProposals as $proposal)
                            <tr>
                                <td>
                                    <strong style="color: var(--fi-text);">{{ $proposal->title }}</strong>
                                    <div class="cell-muted" style="margin-top: 1px;">
                                        {{ $proposal->user->name ?? 'N/A' }} &bull; <span class="mono">#{{ $proposal->id }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">{{ $proposal->department->name ?? 'N/A' }}</span>
                                </td>
                                <td class="text-right font-bold mono" style="color: #b91c1c;">
                                    {{ number_format($proposal->amount) }}
                                </td>
                                <td class="text-center">
                                    @if ($proposal->is_urgent)
                                        <span class="badge badge-danger">{{ __('admin.executive.priority_urgent') }}</span>
                                    @else
                                        <span class="badge badge-secondary">{{ __('admin.executive.priority_normal') }}</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div style="display: inline-flex; gap: 5px;">
                                        <form method="POST" action="{{ route('admin.financial-proposals.quick-status', $proposal->id) }}" style="margin: 0;">
                                            @csrf
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm" style="background: #059669;">{{ __('admin.executive.btn_approve') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.financial-proposals.quick-status', $proposal->id) }}" style="margin: 0;">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-secondary btn-sm">{{ __('admin.executive.btn_reject') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state" style="padding: 26px;">
                                    {{ __('admin.executive.no_pending_proposals') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Operational Incidents & Purchase Orders -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <h3 class="card-title">{{ __('admin.executive.radar_title') }}</h3>
                <a href="{{ route('admin.workspace.show', 'ops') }}" class="link-action">{{ __('admin.executive.open_ops') }} &rarr;</a>
            </div>

            <div style="display: grid; gap: 14px;" class="tabular-nums">
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: var(--fi-muted); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 8px;">
                        {{ __('admin.executive.open_ops_incidents') }} ({{ $urgentIncidents->count() }})
                    </div>
                    @forelse ($urgentIncidents as $incident)
                        <div style="background: var(--fi-danger-bg); border: 1px solid var(--fi-danger-border); border-radius: 7px; padding: 9px 12px; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                            <div style="min-width: 0;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <strong style="color: #991b1b; font-size: 12.5px;">{{ $incident->type_label }}</strong>
                                    @if (!empty($incident->order_id))
                                        <span class="mono" style="font-size: 11px; color: #64748b;">#{{ $incident->order_id }}</span>
                                    @endif
                                </div>
                                <p style="margin: 2px 0 0; color: #7f1d1d; font-size: 11.5px;">{{ Str::limit($incident->description, 58) }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.incidents.quick-status', $incident->id) }}" style="margin: 0; flex-shrink: 0;">
                                @csrf
                                <input type="hidden" name="status" value="resolved">
                                <button type="submit" class="btn btn-secondary btn-sm" style="border-color: #fca5a5; color: #b91c1c;">{{ __('admin.executive.btn_resolve') }}</button>
                            </form>
                        </div>
                    @empty
                        <div style="background: #f8fafc; border: 1px solid var(--fi-border); border-radius: 7px; padding: 10px 12px; font-size: 12px; color: #047857; font-weight: 500;">
                            {{ __('admin.executive.system_stable') }}
                        </div>
                    @endforelse
                </div>

                <div>
                    <div style="font-size: 11px; font-weight: 700; color: var(--fi-muted); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 8px;">
                        {{ __('admin.executive.active_po') }} ({{ $pendingPurchaseOrders->count() }})
                    </div>
                    @forelse ($pendingPurchaseOrders as $po)
                        <div style="background: #f8fafc; border: 1px solid var(--fi-border); border-radius: 7px; padding: 8px 12px; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                            <div>
                                <strong class="mono" style="color: var(--fi-text); font-size: 12px;">{{ $po->po_number }}</strong>
                                <span style="font-size: 12px; color: var(--fi-muted); margin-left: 6px;">{{ $po->supplier_name }}</span>
                                <div style="font-size: 11px; color: var(--fi-muted); margin-top: 1px;">
                                    {{ __('admin.executive.po_expected') }}: {{ optional($po->expected_delivery_date)->format('d/m/Y') ?? __('admin.executive.po_tbd') }}
                                </div>
                            </div>
                            <div class="text-right">
                                <strong class="mono" style="font-size: 12px; color: var(--fi-primary); display: block; margin-bottom: 2px;">{{ number_format($po->total_amount) }}</strong>
                                <span class="badge badge-info">{{ $po->status }}</span>
                            </div>
                        </div>
                    @empty
                        <div style="color: var(--fi-muted); font-size: 12px; padding: 6px 0;">{{ __('admin.executive.no_active_po') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- 5. REVENUE CHART & TOP PRODUCTS PERFORMANCE -->
    <div class="dashboard-grid" style="margin-top: 0; margin-bottom: 18px;">
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <div>
                    <h3 class="card-title">{{ __('admin.dashboard.line_revenue') }}</h3>
                </div>
                <span class="cell-muted mono" style="font-size: 11px;">{{ __('admin.executive.months_12') }}</span>
            </div>
            <svg viewBox="0 0 1000 245" width="100%" height="235" role="img" aria-label="{{ __('admin.dashboard.line_revenue') }}">
                <defs>
                    <linearGradient id="revenueFill" x1="0" x2="0" y1="0" y2="1">
                        <stop offset="0%" stop-color="#004687" stop-opacity="0.16" />
                        <stop offset="100%" stop-color="#004687" stop-opacity="0.0" />
                    </linearGradient>
                </defs>
                @for ($i = 0; $i < 5; $i++)
                    <line x1="36" y1="{{ 20 + $i * 46 }}" x2="964" y2="{{ 20 + $i * 46 }}" stroke="#f1f5f9" stroke-dasharray="3 3" />
                @endfor
                @if ($revenue !== [])
                    @php
                        $points = [];
                        $areaPoints = ['36,204'];
                        foreach ($revenue as $index => $amount) {
                            $x = 36 + ($index * 84);
                            $y = 204 - (($amount / $revenueMax) * 168);
                            $points[] = $x . ',' . $y;
                            $areaPoints[] = $x . ',' . $y;
                        }
                        $areaPoints[] = (36 + (count($revenue) - 1) * 84) . ',204';
                    @endphp
                    <polygon points="{{ implode(' ', $areaPoints) }}" fill="url(#revenueFill)" />
                    <polyline points="{{ implode(' ', $points) }}" fill="none" stroke="#004687" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                    @foreach ($revenue as $index => $amount)
                        @php
                            $x = 36 + ($index * 84);
                            $y = 204 - (($amount / $revenueMax) * 168);
                        @endphp
                        <circle cx="{{ $x }}" cy="{{ $y }}" r="4" fill="#ffffff" stroke="#004687" stroke-width="2.25" />
                        <text x="{{ $x }}" y="230" text-anchor="middle" fill="#64748b" font-size="11" font-weight="600">{{ $months[$index] ?? ($index + 1) }}</text>
                    @endforeach
                @endif
            </svg>
        </div>

        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <h3 class="card-title">{{ __('admin.dashboard.column_products') }}</h3>
                <a href="{{ route('admin.products.index') }}" class="link-action">{{ __('admin.executive.all_products') }} &rarr;</a>
            </div>
            <div style="display: grid; gap: 11px;" class="tabular-nums">
                @forelse ($topProductsList as $idx => $item)
                    @php
                        $revenuePercent = $topProductsMax > 0 ? max(6, (($item->revenue ?? 0) / $topProductsMax) * 100) : 6;
                    @endphp
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 4px;">
                            <span style="display: flex; align-items: center; gap: 7px; min-width: 0;">
                                <span class="mono" style="font-size: 10.5px; font-weight: 700; color: var(--fi-muted); width: 18px;">#{{ $idx + 1 }}</span>
                                <strong style="font-size: 12.5px; color: var(--fi-text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $item->product_name ?? __('admin.dashboard.labels.name') }}</strong>
                            </span>
                            <span class="mono" style="font-size: 12px; font-weight: 700; color: var(--fi-text-secondary);">{{ number_format($item->revenue ?? 0) }}</span>
                        </div>
                        <div style="height: 6px; border-radius: 999px; background: #f1f5f9; overflow: hidden;">
                            <div style="height: 100%; width: {{ min(100, $revenuePercent) }}%; background: var(--fi-primary); border-radius: 999px;"></div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">{{ __('admin.messages.empty_state') }}</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 6. LATEST ORDERS TABLE & ORDER STATUS DISTRIBUTION -->
    <div class="dashboard-grid" style="margin-top: 0; margin-bottom: 18px;">
        <div class="table-panel card" style="margin-bottom: 0;">
            <div style="padding: 13px 18px; border-bottom: 1px solid var(--fi-border); background: #fcfcfd; display: flex; justify-content: space-between; align-items: center;">
                <h3 class="card-title">{{ __('admin.dashboard.latest_orders') }}</h3>
                <a href="{{ route('admin.orders.index') }}" class="link-action">{{ __('admin.executive.view_all') }} &rarr;</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('admin.dashboard.labels.id') }}</th>
                            <th>{{ __('admin.dashboard.labels.code') }}</th>
                            <th>{{ __('admin.dashboard.labels.status') }}</th>
                            <th class="text-right">{{ __('admin.dashboard.labels.total') }}</th>
                            <th class="text-right">{{ __('admin.dashboard.labels.created_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($latestOrders as $order)
                            @php
                                $status = $order->status instanceof \BackedEnum ? $order->status->value : $order->status;
                            @endphp
                            <tr>
                                <td class="cell-muted mono">#{{ $order->id }}</td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="mono" style="font-weight: 700; color: var(--fi-primary);">
                                        {{ $order->number ?: ('#' . $order->id) }}
                                    </a>
                                </td>
                                <td><span class="badge badge-{{ $status ?: 'secondary' }}">{{ \App\Support\AdminLabel::orderStatus($status ?: 'pending') }}</span></td>
                                <td class="text-right font-bold mono">{{ number_format($order->total) }} {{ $order->currency }}</td>
                                <td class="text-right cell-muted mono">{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
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

        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <h3 class="card-title">{{ __('admin.dashboard.order_status') }} &amp; {{ __('admin.dashboard.donut_distribution') }}</h3>
            </div>
            <div style="display: grid; gap: 16px;" class="tabular-nums">
                <div class="metric-list">
                    @forelse ($statusCollection as $status => $total)
                        @php
                            $pct = round(($total / $statusTotalCount) * 100, 1);
                        @endphp
                        <div class="metric-row" style="flex-direction: column; align-items: stretch; gap: 6px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span class="badge badge-{{ $status ?: 'secondary' }}">{{ \App\Support\AdminLabel::orderStatus($status ?: 'unknown') }}</span>
                                <span class="mono" style="font-size: 12px;">
                                    <strong>{{ number_format($total) }}</strong>
                                    <span class="cell-muted">({{ $pct }}%)</span>
                                </span>
                            </div>
                            <div style="height: 5px; background: #f1f5f9; border-radius: 999px; overflow: hidden;">
                                <div style="height: 100%; width: {{ min(100, max(4, $pct)) }}%; background: var(--fi-primary); border-radius: 999px;"></div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">{{ __('admin.messages.empty_state') }}</div>
                    @endforelse
                </div>

                @if ($topProductsDonutList->isNotEmpty())
                    <div style="border-top: 1px solid var(--fi-border); padding-top: 12px;">
                        <div style="font-size: 11px; font-weight: 700; color: var(--fi-muted); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 8px;">
                            {{ __('admin.executive.top_selling_share') }}
                        </div>
                        <div class="metric-list">
                            @foreach ($topProductsDonutList->take(4) as $index => $item)
                                @php
                                    $percent = round((($item->total_sold ?? 0) / $donutTotal) * 100, 1);
                                @endphp
                                <div class="metric-row">
                                    <span style="display: flex; align-items: center; gap: 8px; font-size: 12.5px; font-weight: 500; min-width: 0;">
                                        <span style="width: 8px; height: 8px; border-radius: 2px; background: {{ $donutColors[$index % count($donutColors)] }}; flex-shrink: 0;"></span>
                                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $item->product_name ?? '-' }}</span>
                                    </span>
                                    <strong class="mono" style="font-size: 12px;">{{ $percent }}%</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 7. STRATEGIC MATERIAL PRICE TICKER -->
    @if ($materialPrices->isNotEmpty())
        <div class="card" style="padding: 14px 18px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="font-size: 12px; font-weight: 700; color: var(--fi-text); text-transform: uppercase; letter-spacing: 0.04em;">
                    {{ __('admin.executive.material_ticker_title') }}
                </span>
                <a href="{{ route('admin.workspace.show', 'cfo') }}" class="link-action">{{ __('admin.executive.detailed_chart') }} &rarr;</a>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px;" class="tabular-nums">
                @foreach ($materialPrices as $price)
                    <div style="background: #f8fafc; border: 1px solid var(--fi-border); border-radius: 7px; padding: 9px 12px;">
                        <div style="font-size: 11px; color: var(--fi-muted); font-weight: 600;">{{ $price->material_name }}</div>
                        <div class="mono" style="font-size: 14px; font-weight: 700; color: var(--fi-text); margin-top: 2px;">
                            {{ number_format($price->price) }} <span style="font-size: 10.5px; font-weight: 500; color: var(--fi-muted);">VND</span>
                        </div>
                        <div style="font-size: 10.5px; color: #94a3b8; margin-top: 2px;">
                            {{ optional($price->recorded_at)->format('d/m/Y') ?? __('admin.executive.today') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
