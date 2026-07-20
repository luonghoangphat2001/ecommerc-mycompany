@extends('admin.workspaces.layout', [
    'title' => $title ?? __('workspace.ops.title'),
    'description' => $description ?? __('workspace.ops.description')
])

@section('kpis')
    <div class="stat">
        <div class="label">{{ __('workspace.ops.kpis.active_orders') }}</div>
        <div class="value" style="color: #10b981;">{{ number_format($metrics['active_orders'] ?? 0) }}</div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.ops.kpis.processing_time') }}</div>
        <div class="value" style="color: #3b82f6;">{{ $metrics['processing_time'] ?? '0' }}</div>
    </div>
    <div class="stat" style="{{ ($metrics['open_issues'] ?? 0) > 0 ? 'border-color: #ef4444; background: #fef2f2;' : '' }}">
        <div class="label">{{ __('workspace.ops.kpis.open_issues') }}</div>
        <div class="value" style="color: {{ ($metrics['open_issues'] ?? 0) > 0 ? '#ef4444' : '#f59e0b' }};">
            {{ number_format($metrics['open_issues'] ?? 0) }}
        </div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.ops.kpis.ops_health') }}</div>
        <div class="value" style="color: {{ (int)$metrics['ops_health'] < 90 ? '#ef4444' : '#10b981' }};">
            {{ $metrics['ops_health'] ?? '100%' }}
        </div>
    </div>
@endsection

@section('tab_contents')
    <!-- 2 Column Parallel Layout for Ops -->
    <div style="display: grid; grid-template-columns: 60% 38%; gap: 2%;">
        
        <!-- Left: Live Orders -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h3 style="margin: 0;">{{ __('workspace.ops.content.live_orders_title') }}</h3>
            </div>
            <p style="color: #64748b; font-size: 13px; margin-top: 0;">{{ __('workspace.ops.content.live_orders_desc') }}</p>
            
            <div class="table-wrap" style="max-height: 500px; overflow-y: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('workspace.ops.tables.orders.order_id') }}</th>
                            <th>{{ __('workspace.ops.tables.orders.customer') }}</th>
                            <th>{{ __('workspace.ops.tables.orders.total') }}</th>
                            <th>{{ __('workspace.ops.tables.orders.status') }}</th>
                            <th>{{ __('admin.table_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($liveOrders as $order)
                        <tr>
                            <td><strong>#{{ $order->id }}</strong><br><span style="font-size: 11px; color: #94a3b8;">{{ $order->created_at->format('H:i d/m/Y') }}</span></td>
                            <td>{{ $order->customer_display_name ?? 'N/A' }}</td>
                            <td>{{ number_format($order->total ?? 0) }}</td>
                            <td>
                                @php
                                    $statusVal = $order->status instanceof \BackedEnum ? $order->status->value : $order->status;
                                @endphp
                                @if($statusVal === 'pending')
                                    <span class="badge badge-secondary">{{ __('workspace.ops.statuses.pending') }}</span>
                                @elseif($statusVal === 'processing' || $statusVal === 'new')
                                    <span class="badge badge-warning">{{ __('workspace.ops.statuses.processing') }}</span>
                                @elseif($statusVal === 'shipping' || $statusVal === 'delivering')
                                    <span class="badge badge-info">{{ __('workspace.ops.statuses.shipping') }}</span>
                                @else
                                    <span class="badge badge-{{ $statusVal }}">{{ __('workspace.ops.statuses.' . $statusVal) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="link-action">{{ __('admin.actions.view') }}</a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #94a3b8;">{{ __('workspace.ops.tables.empty_orders') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Incidents -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h3 style="margin: 0;">{{ __('workspace.ops.content.issues_title') }}</h3>
                <a href="{{ route('admin.incidents.create') }}" class="btn btn-primary" style="padding: 6px 12px; font-size: 13px;">+ Thêm Sự cố</a>
            </div>
            <p style="color: #64748b; font-size: 13px; margin-top: 0;">{{ __('workspace.ops.content.issues_desc') }}</p>

            <div style="display: flex; flex-direction: column; gap: 10px; max-height: 500px; overflow-y: auto; padding-right: 5px;">
                @forelse($incidents as $incident)
                    <div style="background: #fff; border: 1px solid #e2e8f0; border-left: 4px solid {{ $incident->status === 'open' ? '#ef4444' : ($incident->status === 'in_progress' ? '#f59e0b' : '#10b981') }}; border-radius: 6px; padding: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="font-weight: bold; font-size: 14px; color: #0f172a;">
                                {{ __('workspace.ops.incident_types.' . $incident->type) }}
                            </span>
                            <span style="font-size: 11px; padding: 2px 6px; border-radius: 10px; background: #f1f5f9; color: #64748b;">
                                {{ __('workspace.ops.statuses.' . $incident->status) }}
                            </span>
                        </div>
                        <p style="margin: 0 0 10px 0; font-size: 13px; color: #475569;">
                            {{ $incident->description }}
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #94a3b8;">
                            <span>Đơn hàng: <strong>#{{ $incident->order_id ?? 'N/A' }}</strong></span>
                            @if($incident->status === 'open')
                                <span style="font-size: 11px; color: #64748b;">Chưa có người xử lý</span>
                            @else
                                <span style="color: #10b981;">
                                    <svg style="width: 14px; height: 14px; display: inline; vertical-align: text-bottom;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    {{ $incident->assignee->name ?? 'N/A' }}
                                </span>
                            @endif
                            <div class="actions">
                                <a href="{{ route('admin.incidents.show', $incident->id) }}" class="link-action">{{ __('admin.actions.view') }}</a>
                                <a href="{{ route('admin.incidents.edit', $incident->id) }}" class="link-action">Sửa</a>
                                <form action="{{ route('admin.incidents.destroy', $incident->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link-action link-danger">Xóa</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; color: #94a3b8; padding: 20px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
                        {{ __('workspace.ops.tables.empty_incidents') }}
                    </div>
                @endforelse
            </div>
        </div>

    </div>
@endsection
