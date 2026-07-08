<?php

namespace App\Http\Controllers\Admin;

use App\Ecommerce\Inventory\Services\InventoryService;
use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Inventory;
use App\Models\InventoryRecord;
use App\Models\InventoryRecordItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InventoryRecordController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return InventoryRecord::class;
    }

    protected function title(): string
    {
        return 'Inventory Records';
    }

    protected function routePrefix(): string
    {
        return 'admin.inventory-records';
    }

    protected function searchable(): array
    {
        return ['type', 'status', 'notes'];
    }

    protected function fields(): array
    {
        return [
            'type' => ['label' => 'Type', 'type' => 'select', 'rules' => ['required', 'string'], 'options' => ['IN' => 'IN', 'OUT' => 'OUT', 'TRANSFER' => 'TRANSFER']],
            'status' => ['label' => 'Status', 'type' => 'select', 'rules' => ['required', 'string'], 'options' => ['DRAFT' => 'DRAFT', 'COMPLETED' => 'COMPLETED']],
            'notes' => ['label' => 'Notes', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
            'items_json' => ['label' => 'Items JSON', 'type' => 'textarea', 'rules' => ['nullable', 'string'], 'virtual' => true],
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
        $items = json_decode((string) $request->input('items_json', '[]'), true);
        if (! is_array($items)) {
            return;
        }

        $record->items()->delete();

        foreach ($items as $item) {
            if (! isset($item['shop_product_id'], $item['warehouse_id'], $item['quantity'])) {
                continue;
            }

            InventoryRecordItem::create([
                'warehouse_record_id' => $record->id,
                'shop_product_id' => (int) $item['shop_product_id'],
                'warehouse_id' => (int) $item['warehouse_id'],
                'target_warehouse_id' => isset($item['target_warehouse_id']) ? (int) $item['target_warehouse_id'] : null,
                'quantity' => (int) $item['quantity'],
            ]);
        }
    }

    protected function formData(?Model $record = null): array
    {
        if (! $record) {
            return [];
        }

        return [
            'items_json' => json_encode($record->items()->get(['shop_product_id', 'warehouse_id', 'target_warehouse_id', 'quantity'])->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ];
    }

    public function process(int $id, InventoryService $inventoryService): RedirectResponse
    {
        $inventoryService->processRecord($id);

        return redirect()->route('admin.inventory-records.index')->with('status', 'Đã xử lý phiếu kho');
    }
}
