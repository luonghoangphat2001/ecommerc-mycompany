<?php

namespace App\Ecommerce\Shipping\Support; // Or App\Http\Controllers\Admin\Traits

namespace App\Http\Controllers\Admin\Traits;

trait HasShippingTabs
{
    protected function tabs(): array
    {
        $tabs = [
            [
                'key' => 'shipping-zones',
                'label' => 'Khu vực vận chuyển (Shipping Zones)',
                'url' => route('admin.shipping-zones.index'),
                'permission' => 'view_shipping-zones',
            ],
            [
                'key' => 'shipping-methods',
                'label' => 'Phương thức vận chuyển (Manual Rates)',
                'url' => route('admin.shipping-methods.index'),
                'permission' => 'view_shipping-methods',
            ],
            [
                'key' => 'shipping-carriers',
                'label' => 'Hãng vận chuyển (Carrier APIs)',
                'url' => route('admin.shipping-carriers.index'),
                'permission' => 'view_shipping-zones',
            ],
            [
                'key' => 'shipping-orders',
                'label' => 'Đơn vận chuyển (Shipments)',
                'url' => route('admin.shipping-orders.index'),
                'permission' => 'view_orders',
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
