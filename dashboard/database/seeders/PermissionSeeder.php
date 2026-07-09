<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            'users',
            'roles',
            'permissions',
            'products',
            'brands',
            'product-categories',
            'tax-classes',
            'tax-rates',
            'shipping-zones',
            'shipping-methods',
            'inventories',
            'inventory-records',
            'inventory-movements',
            'upsell-products',
            'cross-sell-products',
            'combo-products',
            'coupons',
            'loyalty-points',
            'orders',
            'payments',
            'refunds',
            'posts',
            'post-categories',
            'comments',
            'pages',
            'menus',
            'menu-items',
            'language-lines',
            'media',
            'settings',
            'webhooks'
        ];

        $actions = ['view', 'create', 'update', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action}_{$module}", 'guard_name' => 'web']);
            }
        }

        // Dashboard specific
        Permission::firstOrCreate(['name' => "view_dashboard", 'guard_name' => 'web']);

        $this->command->info('Default Permissions seeded successfully.');

        // Assign all permissions to super_admin
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());
        $this->command->info('All permissions granted to super_admin.');
    }
}
