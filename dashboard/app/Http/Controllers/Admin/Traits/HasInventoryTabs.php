<?php

namespace App\Http\Controllers\Admin\Traits;

trait HasInventoryTabs
{
    protected function tabs(): array
    {
        $tabs = [
            [
                'key' => 'inventories',
                'label' => __('admin.sidebar.inventories'),
                'url' => route('admin.inventories.index'),
                'permission' => 'view_inventories',
            ],
            [
                'key' => 'inventory-records',
                'label' => __('admin.sidebar.inventory_records'),
                'url' => route('admin.inventory-records.index'),
                'permission' => 'view_inventory-records',
            ],
            [
                'key' => 'inventory-movements',
                'label' => __('admin.sidebar.inventory_movements'),
                'url' => route('admin.inventory-movements.index'),
                'permission' => 'view_inventory-movements',
            ],
        ];

        if (auth()->check() && !auth()->user()->hasRole('super_admin')) {
            $tabs = array_values(array_filter($tabs, function ($tab) {
                try {
                    return auth()->user()->hasPermissionTo($tab['permission']);
                } catch (\Throwable $e) {
                    return false;
                }
            }));
        }

        return $tabs;
    }
}
