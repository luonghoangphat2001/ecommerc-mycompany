@extends('admin.workspaces.layout', [
    'title' => $title ?? __('workspace.logistics.title'),
    'description' => $description ?? __('workspace.logistics.description')
])

@section('kpis')
    <!-- Alerts -->
    @if(count($lowStockAlerts) > 0)
    <div style="grid-column: 1 / -1; margin-bottom: 15px; padding: 15px; background: #fef2f2; border: 1px solid #ef4444; border-radius: 8px; color: #b91c1c;">
        <h4 style="margin: 0 0 10px; font-weight: bold; display: flex; align-items: center; gap: 8px;">
            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            {{ __('workspace.logistics.alerts.low_stock_title') }} {{ count($lowStockAlerts) }} {{ __('workspace.logistics.alerts.low_stock_desc') }}
        </h4>
        <ul style="margin: 0; padding-left: 20px; font-size: 13px;">
            @foreach($lowStockAlerts as $alert)
                <li><strong>{{ $alert->title }}</strong> ({{ __('workspace.logistics.tables.inventory.sku') }} {{ $alert->sku }}) - {{ __('workspace.logistics.alerts.current') }} {{ $alert->stock_quantity }} ({{ __('workspace.logistics.alerts.threshold') }} {{ $alert->low_stock_threshold }})</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="stat">
        <div class="label">{{ __('workspace.logistics.kpis.total_stock') }}</div>
        <div class="value" style="color: #0f172a;">{{ number_format($metrics['total_stock'] ?? 0) }}</div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.logistics.kpis.pending_pos') }}</div>
        <div class="value" style="color: #f59e0b;">{{ $metrics['pending_pos'] ?? 0 }}</div>
    </div>
    <div class="stat">
        <div class="label">Tỷ lệ đơn đang giao</div>
        <div class="value" style="color: #3b82f6;">{{ $metrics['po_rate'] ?? 'N/A' }}</div>
    </div>
@endsection

@section('tab_contents')
    <!-- We will use a custom layout for Logistics, so activeTab isn't strictly needed for a parallel view, but we can wrap it -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        
        <!-- Left: Inventory Stocks -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h3 style="margin: 0;">{{ __('workspace.logistics.content.inventory_title') }}</h3>
            </div>
            <div class="table-wrap" style="max-height: 400px; overflow-y: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('workspace.logistics.tables.inventory.product') }}</th>
                            <th>{{ __('workspace.logistics.tables.inventory.warehouse') }}</th>
                            <th>{{ __('workspace.logistics.tables.inventory.qty') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventoryStocks as $stock)
                        <tr>
                            <td>
                                <strong>{{ $stock->product->name ?? 'N/A' }}</strong><br>
                                <span style="font-size: 12px; color: #64748b;">{{ __('workspace.logistics.tables.inventory.sku') }} {{ $stock->product->sku ?? 'N/A' }}</span>
                            </td>
                            <td>{{ $stock->inventory->name ?? 'N/A' }}</td>
                            <td style="font-weight: bold;">{{ number_format($stock->quantity) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #94a3b8;">{{ __('workspace.logistics.tables.empty_inventory') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Purchase Orders -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h3 style="margin: 0;">{{ __('workspace.logistics.content.pos_title') }}</h3>
                <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-primary" style="padding: 6px 12px; font-size: 13px;">+ {{ __('workspace.logistics.actions.create_po') }}</a>
            </div>
            <div class="table-wrap" style="max-height: 400px; overflow-y: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('workspace.logistics.tables.pos.po_number') }}</th>
                            <th>{{ __('workspace.logistics.tables.pos.supplier') }}</th>
                            <th>{{ __('workspace.logistics.tables.pos.status') }}</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrders as $po)
                        <tr>
                            <td><strong>{{ $po->po_number }}</strong></td>
                            <td>{{ $po->supplier_name }}</td>
                            <td>
                                @if($po->status === 'shipping')
                                    <span class="badge badge-info">{{ __('workspace.logistics.statuses.shipping') }}</span>
                                @elseif($po->status === 'partial')
                                    <span class="badge badge-warning">{{ __('workspace.logistics.statuses.partial') }}</span>
                                @elseif($po->status === 'completed')
                                    <span class="badge badge-success">{{ __('workspace.logistics.statuses.completed') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ __('workspace.logistics.statuses.defective') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="link-action">{{ __('admin.actions.view') }}</a>
                                    <a href="{{ route('admin.purchase-orders.edit', $po->id) }}" class="link-action">Sửa</a>
                                    <form action="{{ route('admin.purchase-orders.destroy', $po->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="link-action link-danger">Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #94a3b8;">{{ __('workspace.logistics.tables.empty_pos') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
