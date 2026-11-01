@extends('admin.workspaces.layout', [
    'title' => $title ?? __('workspace.logistics.title'),
    'description' => $description ?? __('workspace.logistics.description')
])

@section('kpis')
    @if(count($lowStockAlerts) > 0)
        <div style="grid-column: 1 / -1; padding: 12px 16px; background: #fef2f2; border: 1px solid #fecaca; border-radius: var(--fi-radius-md); color: #991b1b;">
            <h4 style="margin: 0 0 8px; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                {{ __('workspace.logistics.alerts.low_stock_title') }} {{ count($lowStockAlerts) }} {{ __('workspace.logistics.alerts.low_stock_desc') }}
            </h4>
            <ul style="margin: 0; padding-left: 18px; font-size: 12.5px;">
                @foreach($lowStockAlerts as $alert)
                    <li><strong>{{ $alert->title }}</strong> (<span class="mono">{{ __('workspace.logistics.tables.inventory.sku') }} {{ $alert->sku }}</span>) — {{ __('workspace.logistics.alerts.current') }} <strong class="mono">{{ $alert->stock_quantity }}</strong> ({{ __('workspace.logistics.alerts.threshold') }} {{ $alert->low_stock_threshold }})</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="stat">
        <div class="label">{{ __('workspace.logistics.kpis.total_stock') }}</div>
        <div class="value">{{ number_format($metrics['total_stock'] ?? 0) }}</div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.logistics.kpis.pending_pos') }}</div>
        <div class="value" style="color: #b45309;">{{ $metrics['pending_pos'] ?? 0 }}</div>
    </div>
    <div class="stat">
        <div class="label">Tỷ lệ đơn đang giao</div>
        <div class="value" style="color: var(--fi-primary);">{{ $metrics['po_rate'] ?? 'N/A' }}</div>
    </div>
@endsection

@section('tab_contents')
    <div class="split-grid-2col">
        <!-- Left: Inventory Stocks -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; min-height: 32px;">
                <h3 style="margin: 0; font-size: 14px; font-weight: 700;">{{ __('workspace.logistics.content.inventory_title') }}</h3>
            </div>
            <div class="table-wrap" style="max-height: 440px; overflow-y: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('workspace.logistics.tables.inventory.product') }}</th>
                            <th style="width: 160px;">{{ __('workspace.logistics.tables.inventory.warehouse') }}</th>
                            <th class="text-right" style="width: 110px;">{{ __('workspace.logistics.tables.inventory.qty') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventoryStocks as $stock)
                            <tr>
                                <td>
                                    <strong style="font-weight: 600;">{{ $stock->product->name ?? 'N/A' }}</strong><br>
                                    <span class="mono cell-muted" style="font-size: 11.5px;">{{ __('workspace.logistics.tables.inventory.sku') }} {{ $stock->product->sku ?? 'N/A' }}</span>
                                </td>
                                <td>{{ $stock->inventory->name ?? 'N/A' }}</td>
                                <td class="mono text-right" style="font-weight: 700;">{{ number_format($stock->quantity) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-state">{{ __('workspace.logistics.tables.empty_inventory') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Purchase Orders -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; gap: 12px; min-height: 32px;">
                <h3 style="margin: 0; font-size: 14px; font-weight: 700;">{{ __('workspace.logistics.content.pos_title') }}</h3>
                <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-primary btn-sm">+ {{ __('workspace.logistics.actions.create_po') }}</a>
            </div>
            <div class="table-wrap" style="max-height: 440px; overflow-y: auto;">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 130px;">{{ __('workspace.logistics.tables.pos.po_number') }}</th>
                            <th>{{ __('workspace.logistics.tables.pos.supplier') }}</th>
                            <th style="width: 130px;">{{ __('workspace.logistics.tables.pos.status') }}</th>
                            <th class="text-right" style="width: 170px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrders as $po)
                            @php
                                $poStatusVal = $po->status instanceof \BackedEnum ? $po->status->value : $po->status;
                            @endphp
                            <tr>
                                <td class="mono" style="font-weight: 700;">{{ $po->po_number }}</td>
                                <td style="font-weight: 600;">{{ $po->supplier_name }}</td>
                                <td>
                                    <x-admin.status-badge :status="$poStatusVal" :label="__('workspace.logistics.statuses.' . ($poStatusVal === 'shipping' ? 'shipping' : ($poStatusVal === 'partial' ? 'partial' : ($poStatusVal === 'completed' ? 'completed' : 'defective'))))" />
                                </td>
                                <td class="text-right">
                                    <x-admin.row-actions
                                        :viewUrl="route('admin.purchase-orders.show', $po->id)"
                                        :editUrl="route('admin.purchase-orders.edit', $po->id)"
                                        :deleteUrl="route('admin.purchase-orders.destroy', $po->id)"
                                    />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-state">{{ __('workspace.logistics.tables.empty_pos') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
