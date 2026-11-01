<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class DemoDatabaseSeeder extends Seeder
{
    /**
     * Run the complete production-realistic Club Store demo data seeding.
     */
    public function run(): void
    {
        // 1. Mandatory Environment Safety Gate
        if (App::environment('production')) {
            $this->command?->error('CRITICAL SAFETY STOP: Demo data seeding is forbidden in production environment!');
            return;
        }

        $this->command?->warn('================================================================');
        $this->command?->info('  STARTING PRODUCTION-REALISTIC DEMO SEEDING: NOVA CLUB STORE  ');
        $this->command?->warn('  Target: Local / Development / Staging Test Environment        ');
        $this->command?->warn('================================================================');

        $startTime = microtime(true);

        DB::beginTransaction();
        try {
            // Ensure core roles exist
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Thành viên đăng ký', 'guard_name' => 'web']);

            // 2. Admin & Store Settings
            $this->command?->info(PHP_EOL . '[1/7] Seeding Admin & Club Store Settings...');
            $this->call(AdminSettingSeeder::class);

            // 3. CMS Pages, Blocks, Menus & Media
            $this->command?->info(PHP_EOL . '[2/7] Seeding CMS Pages, Navigation Menus & Media Assets...');
            $this->call(CmsSeeder::class);

            // 4. Customers, Addresses, Loyalty Data & User Profiles
            $this->command?->info(PHP_EOL . '[3/7] Seeding Customers, Addresses & Loyalty Accounts...');
            $this->call(CustomerSeeder::class);

            // 5. Brands, Categories, Products, Combos, Upsells & Coupons
            $this->command?->info(PHP_EOL . '[4/7] Seeding Catalog (Brands, Categories, Products, Combos, Coupons)...');
            $this->call(CatalogSeeder::class);

            // 6. Warehouses, Stocks, Inventory Records & Stock Movements
            $this->command?->info(PHP_EOL . '[5/7] Seeding Multi-Warehouse Inventory & Movements...');
            $this->call(InventorySeeder::class);

            // 7. Realistic Orders, Addresses, Items, Payments & Refunds
            $this->command?->info(PHP_EOL . '[6/7] Seeding Realistic Orders, Payments & Service Recalculations...');
            $this->call(OrderSeeder::class);

            // 8. AI Agent Swarm, Departments, Proposals, POs, Incidents, Reviews & Audit Logs
            $this->command?->info(PHP_EOL . '[7/7] Seeding AI Agent Departments, Workflows & Audit Logs...');
            $this->call(AiAgentSeeder::class);

            DB::commit();

            $elapsed = round(microtime(true) - $startTime, 2);
            $this->command?->info(PHP_EOL . '================================================================');
            $this->command?->info("  DEMO DATA SEEDING COMPLETED SUCCESSFULLY IN {$elapsed}s!       ");
            $this->command?->info('================================================================');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command?->error(PHP_EOL . 'Demo Seeding Failed: ' . $e->getMessage());
            $this->command?->error($e->getTraceAsString());
            throw $e;
        }
    }
}
