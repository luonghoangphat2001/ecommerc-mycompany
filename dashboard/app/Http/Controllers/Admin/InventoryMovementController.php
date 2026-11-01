<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\Traits\HasInventoryTabs;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class InventoryMovementController extends BaseCrudController
{
    use HasInventoryTabs;

    protected function activeTab(): ?string
    {
        return 'inventory-movements';
    }

    protected function modelClass(): string
    {
        return InventoryMovement::class;
    }

    protected function title(): string
    {
        return app()->getLocale() === 'en' ? 'Inventory Movements' : 'Lịch sử biến động kho';
    }

    protected function routePrefix(): string
    {
        return 'admin.inventory-movements';
    }

    protected function searchable(): array
    {
        return ['reference_type'];
    }

    protected function indexQuery(Builder $query, Request $request): Builder
    {
        if ($warehouseId = $request->input('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
        }
        if ($refType = $request->input('reference_type')) {
            $query->where('reference_type', $refType);
        }

        return $query->with(['product', 'warehouse'])->latest('id');
    }

    protected function filters(): array
    {
        $isEn = app()->getLocale() === 'en';

        return [
            'warehouse_id' => [
                'label' => $isEn ? 'Warehouse' : 'Kho hàng',
                'type' => 'select',
                'options' => Inventory::orderBy('name')->pluck('name', 'id')->toArray(),
            ],
            'reference_type' => [
                'label' => $isEn ? 'Movement Type' : 'Loại biến động',
                'type' => 'select',
                'options' => $this->referenceTypeOptions(),
            ],
        ];
    }

    protected function canCreate(): bool
    {
        return false;
    }

    protected function canEdit(): bool
    {
        return false;
    }

    protected function canDelete(): bool
    {
        return false;
    }

    protected function fields(): array
    {
        $isEn = app()->getLocale() === 'en';

        return [
            'product_display' => [
                'label' => $isEn ? 'Product' : 'Sản phẩm',
                'tableOnly' => true,
                'virtual' => true,
            ],
            'warehouse_display' => [
                'label' => $isEn ? 'Warehouse' : 'Kho hàng',
                'tableOnly' => true,
                'virtual' => true,
            ],
            'reference_type' => [
                'label' => $isEn ? 'Movement Type' : 'Loại biến động',
                'type' => 'select',
                'options' => $this->referenceTypeOptions(),
            ],
            'prev_stock' => [
                'label' => $isEn ? 'Prev Stock' : 'Tồn trước',
                'type' => 'number',
            ],
            'formatted_quantity' => [
                'label' => $isEn ? 'Qty Changed' : 'Biến động',
                'tableOnly' => true,
                'virtual' => true,
            ],
            'new_stock' => [
                'label' => $isEn ? 'New Stock' : 'Tồn sau',
                'type' => 'number',
            ],
            'created_at' => [
                'label' => $isEn ? 'Date & Time' : 'Thời gian',
                'type' => 'datetime',
            ],
        ];
    }

    private function referenceTypeOptions(): array
    {
        if (app()->getLocale() === 'en') {
            return [
                'inbound_shipment' => 'Inbound Shipment',
                'transfer_out' => 'Transfer Out',
                'transfer_in' => 'Transfer In',
                'order_fulfillment' => 'Order Fulfillment',
                'order_cancel' => 'Order Cancelled',
                'manual_adjustment' => 'Manual Adjustment',
                'inventory_record' => 'Inventory Record',
                'return' => 'Customer Return',
            ];
        }

        return [
            'inbound_shipment' => 'Nhập kho',
            'transfer_out' => 'Xuất chuyển kho',
            'transfer_in' => 'Nhập chuyển kho',
            'order_fulfillment' => 'Xuất đơn hàng',
            'order_cancel' => 'Hủy đơn hoàn kho',
            'manual_adjustment' => 'Điều chỉnh kiểm kê',
            'inventory_record' => 'Phiếu xuất nhập kho',
            'return' => 'Khách trả hàng',
        ];
    }
}
