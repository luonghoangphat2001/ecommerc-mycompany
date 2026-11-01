@extends('admin.workspaces.layout', [
    'title' => $title ?? __('workspace.ops.title'),
    'description' => $description ?? __('workspace.ops.description')
])

@section('kpis')
    <div class="stat">
        <div class="label">{{ __('workspace.ops.kpis.active_orders') }}</div>
        <div class="value" style="color: #047857;">{{ number_format($metrics['active_orders'] ?? 0) }}</div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.ops.kpis.processing_time') }}</div>
        <div class="value" style="color: var(--fi-primary);">{{ $metrics['processing_time'] ?? '0' }}</div>
    </div>
    <div class="stat" style="{{ ($metrics['open_issues'] ?? 0) > 0 ? 'border-color: #fecaca; background: #fef2f2;' : '' }}">
        <div class="label">{{ __('workspace.ops.kpis.open_issues') }}</div>
        <div class="value" style="color: {{ ($metrics['open_issues'] ?? 0) > 0 ? '#b91c1c' : '#b45309' }};">
            {{ number_format($metrics['open_issues'] ?? 0) }}
        </div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.ops.kpis.ops_health') }}</div>
        <div class="value" style="color: {{ (int)($metrics['ops_health'] ?? 100) < 90 ? '#b91c1c' : '#047857' }};">
            {{ $metrics['ops_health'] ?? '100%' }}
        </div>
    </div>
@endsection

@section('tab_contents')
    <div class="split-grid-2col">
        <!-- Left: Live Orders -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; min-height: 36px;">
                <div>
                    <h3 style="margin: 0; font-size: 14px; font-weight: 700;">{{ __('workspace.ops.content.live_orders_title') }}</h3>
                    <p style="color: var(--fi-muted); font-size: 12.5px; margin: 3px 0 0;">{{ __('workspace.ops.content.live_orders_desc') }}</p>
                </div>
            </div>

            <div class="table-wrap" style="max-height: 480px; overflow-y: auto;">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 115px;">{{ __('workspace.ops.tables.orders.order_id') }}</th>
                            <th>{{ __('workspace.ops.tables.orders.customer') }}</th>
                            <th class="text-right" style="width: 130px;">{{ __('workspace.ops.tables.orders.total') }}</th>
                            <th style="width: 130px;">{{ __('workspace.ops.tables.orders.status') }}</th>
                            <th class="text-right" style="width: 90px;">{{ __('admin.table_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($liveOrders as $order)
                            <tr>
                                <td>
                                    <span class="mono" style="font-weight: 700;">#{{ $order->id }}</span><br>
                                    <span class="mono cell-muted" style="font-size: 11px;">{{ $order->created_at->format('H:i d/m/Y') }}</span>
                                </td>
                                <td style="font-weight: 600;">{{ $order->customer_display_name ?? 'N/A' }}</td>
                                <td class="mono text-right" style="font-weight: 600;">{{ number_format($order->total ?? 0) }}</td>
                                <td>
                                    @php
                                        $statusVal = $order->status instanceof \BackedEnum ? $order->status->value : $order->status;
                                    @endphp
                                    <x-admin.status-badge
                                        :status="$statusVal"
                                        :label="__('workspace.ops.statuses.' . ($statusVal === 'new' ? 'processing' : ($statusVal === 'delivering' ? 'shipping' : $statusVal)))"
                                    />
                                </td>
                                <td class="text-right">
                                    <x-admin.row-actions :viewUrl="route('admin.orders.show', $order->id)" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">{{ __('workspace.ops.tables.empty_orders') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Incidents -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; gap: 12px; min-height: 36px;">
                <div>
                    <h3 style="margin: 0; font-size: 14px; font-weight: 700;">{{ __('workspace.ops.content.issues_title') }}</h3>
                    <p style="color: var(--fi-muted); font-size: 12.5px; margin: 3px 0 0;">{{ __('workspace.ops.content.issues_desc') }}</p>
                </div>
                <a href="{{ route('admin.incidents.create') }}" class="btn btn-primary btn-sm">+ Thêm Sự cố</a>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px; max-height: 480px; overflow-y: auto;">
                @forelse($incidents as $incident)
                    @php
                        $incidentStatusVal = $incident->status instanceof \BackedEnum ? $incident->status->value : $incident->status;
                    @endphp
                    <div style="background: #fff; border: 1px solid {{ $incidentStatusVal === 'open' ? '#fecaca' : ($incidentStatusVal === 'in_progress' ? '#fde68a' : 'var(--fi-border-strong)') }}; border-radius: var(--fi-radius-md); padding: 13px 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; gap: 8px;">
                            <span style="font-weight: 700; font-size: 13px; color: var(--fi-text); display: inline-flex; align-items: center; gap: 6px;">
                                <span style="width: 7px; height: 7px; border-radius: 50%; background: {{ $incidentStatusVal === 'open' ? '#ef4444' : ($incidentStatusVal === 'in_progress' ? '#f59e0b' : '#10b981') }}; display: inline-block;"></span>
                                {{ $incident->type_label }}
                            </span>
                            <x-admin.status-badge :status="$incidentStatusVal" :label="__('workspace.ops.statuses.' . $incidentStatusVal)" />
                        </div>
                        <p style="margin: 0 0 10px 0; font-size: 12.5px; color: var(--fi-text-secondary);">
                            {{ $incident->description }}
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: var(--fi-muted); flex-wrap: wrap; gap: 8px;">
                            <span>Đơn hàng: <strong class="mono">#{{ $incident->order_id ?? 'N/A' }}</strong></span>
                            @if($incidentStatusVal === 'open')
                                <span style="font-size: 11.5px; color: var(--fi-muted);">Chưa có người xử lý</span>
                            @else
                                <span style="color: #047857; font-weight: 600;">
                                    {{ $incident->assignee->name ?? 'N/A' }}
                                </span>
                            @endif
                            <x-admin.row-actions
                                :viewUrl="route('admin.incidents.show', $incident->id)"
                                :editUrl="route('admin.incidents.edit', $incident->id)"
                                :deleteUrl="route('admin.incidents.destroy', $incident->id)"
                            />
                        </div>
                    </div>
                @empty
                    <div class="empty-state" style="border: 1px dashed var(--fi-border-strong); border-radius: var(--fi-radius-md);">
                        {{ __('workspace.ops.tables.empty_incidents') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
