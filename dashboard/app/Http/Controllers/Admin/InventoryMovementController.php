<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\InventoryMovement;

class InventoryMovementController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return InventoryMovement::class;
    }

    protected function title(): string
    {
        return 'admin.settings.view_inventory_movements';
    }

    protected function routePrefix(): string
    {
        return 'admin.inventory-movements';
    }

    protected function searchable(): array
    {
        return ['reference_type'];
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
        return [
            'shop_product_id' => ['label' => 'Product ID', 'type' => 'number', 'rules' => ['nullable'], 'hideOnForm' => true],
            'warehouse_id' => ['label' => 'Warehouse ID', 'type' => 'number', 'rules' => ['nullable'], 'hideOnForm' => true],
            'reference_type' => ['label' => 'Reference', 'rules' => ['nullable'], 'hideOnForm' => true],
            'prev_stock' => ['label' => 'Prev stock', 'type' => 'number', 'rules' => ['nullable'], 'hideOnForm' => true],
            'quantity_changed' => ['label' => 'Qty changed', 'type' => 'number', 'rules' => ['nullable'], 'hideOnForm' => true],
            'new_stock' => ['label' => 'New stock', 'type' => 'number', 'rules' => ['nullable'], 'hideOnForm' => true],
        ];
    }
}
