<?php

namespace App\Http\Controllers\Admin;

use App\Ecommerce\Inventory\Services\InventoryService;
use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\Traits\HasInventoryTabs;
use App\Models\Inventory;
use App\Models\InventoryRecord;
use App\Models\InventoryRecordItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InventoryRecordController extends BaseCrudController
{
    use HasInventoryTabs;

    protected function activeTab(): ?string
    {
        return 'inventory-records';
    }

    protected function modelClass(): string
    {
        return InventoryRecord::class;
    }

    protected function title(): string
    {
        return app()->getLocale() === 'en' ? 'Inventory Records' : 'Phiếu xuất nhập kho';
    }

    protected function routePrefix(): string
    {
        return 'admin.inventory-records';
    }

    protected function formView(): string
    {
        return 'admin.inventory.records.form';
    }

    protected function searchable(): array
    {
        return ['type', 'status', 'notes'];
    }

    protected function fields(): array
    {
        $isEn = app()->getLocale() === 'en';

        return [
            'type' => [
                'label' => $isEn ? 'Record Type' : 'Loại phiếu',
                'type' => 'select',
                'rules' => ['required', 'string'],
                'options' => [
                    'IN' => $isEn ? 'Stock In' : 'Nhập kho',
                    'OUT' => $isEn ? 'Stock Out' : 'Xuất kho',
                    'TRANSFER' => $isEn ? 'Transfer' : 'Điều chuyển kho',
                ],
            ],
            'status' => [
                'label' => $isEn ? 'Status' : 'Trạng thái',
                'type' => 'select',
                'rules' => ['required', 'string'],
                'options' => [
                    'DRAFT' => $isEn ? 'Draft' : 'Bản nháp',
                    'COMPLETED' => $isEn ? 'Completed' : 'Hoàn thành',
                ],
            ],
            'notes' => [
                'label' => $isEn ? 'Notes' : 'Ghi chú',
                'type' => 'textarea',
                'rules' => ['nullable', 'string'],
            ],
            'items_json' => [
                'label' => $isEn ? 'Items' : 'Chi tiết sản phẩm',
                'type' => 'hidden',
                'rules' => ['nullable'],
                'virtual' => true,
                'formOnly' => true,
            ],
        ];
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        unset($data['items_json']);
        $data['status'] = $data['status'] ?? 'DRAFT';

        return $data;
    }

    protected function afterSave(Model $record, Request $request): void
    {
        $raw = $request->input('items_json');
        $items = is_array($raw) ? $raw : json_decode((string) $raw, true);
        if (! is_array($items)) {
            return;
        }

        $record->items()->delete();

        foreach ($items as $item) {
            if (! isset($item['shop_product_id'], $item['warehouse_id'], $item['quantity']) || empty($item['shop_product_id'])) {
                continue;
            }

            InventoryRecordItem::create([
                'warehouse_record_id' => $record->id,
                'shop_product_id' => (int) $item['shop_product_id'],
                'warehouse_id' => (int) $item['warehouse_id'],
                'target_warehouse_id' => !empty($item['target_warehouse_id']) ? (int) $item['target_warehouse_id'] : null,
                'quantity' => max(1, (int) $item['quantity']),
            ]);
        }
    }

    protected function formData(?Model $record = null): array
    {
        $products = Product::select('id', 'name', 'sku')->orderBy('name')->get();
        $warehouses = Inventory::select('id', 'name', 'code')->orderBy('name')->get();

        $items = [];
        if ($record) {
            foreach ($record->items()->get(['shop_product_id', 'warehouse_id', 'target_warehouse_id', 'quantity']) as $item) {
                $items[] = [
                    'shop_product_id' => (int) $item->shop_product_id,
                    'warehouse_id' => (int) $item->warehouse_id,
                    'target_warehouse_id' => $item->target_warehouse_id ? (int) $item->target_warehouse_id : null,
                    'quantity' => (int) $item->quantity,
                ];
            }
        }

        if (empty($items)) {
            $firstProduct = $products->first();
            $firstWh = $warehouses->first();
            $items[] = [
                'shop_product_id' => $firstProduct ? $firstProduct->id : null,
                'warehouse_id' => $firstWh ? $firstWh->id : null,
                'target_warehouse_id' => null,
                'quantity' => 1,
            ];
        }

        $type = 'IN';
        if ($record && $record->type) {
            $type = $record->type instanceof \BackedEnum ? $record->type->value : (string) $record->type;
        }

        $status = 'DRAFT';
        if ($record && $record->status) {
            $status = $record->status instanceof \BackedEnum ? $record->status->value : (string) $record->status;
        }

        return [
            'products' => $products,
            'warehouses' => $warehouses,
            'initialItems' => $items,
            'currentType' => $type,
            'currentStatus' => $status,
            'items_json' => json_encode($items, JSON_UNESCAPED_UNICODE),
        ];
    }

    public function process(int $id, InventoryService $inventoryService): RedirectResponse
    {
        $inventoryService->processRecord($id);

        return redirect()->route('admin.inventory-records.index')->with('status', 'Đã xử lý phiếu kho');
    }
}
