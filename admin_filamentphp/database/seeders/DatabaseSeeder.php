<?php

namespace Database\Seeders;

use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Helper\ProgressBar;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::raw('SET time_zone=\'+00:00\'');

        // Clear images
        Storage::deleteDirectory('public');

        // 1. Create Admin
        $this->command->warn(PHP_EOL . 'Creating admin user...');
        $admin = $this->withProgressBar(1, fn() => User::factory(1)->create([
            'name' => 'Admin System',
            'email' => 'admin@admin.com',
        ]))->first();
        $this->command->info('Admin user created.');

        // 2. Shield Logic
        $this->command->warn("Generating Filament Shield permissions...");
        try {
            Artisan::call('shield:generate --all --panel=admin');
            $this->command->info("Filament Shield permissions generated successfully.");
        } catch (\Exception $e) {
            $this->command->error("Error generating permissions: " . $e->getMessage());
        }

        // 3. Create Default Role
        $this->command->warn("Creating default User role...");
        try {
            $role = Role::firstOrCreate(['name' => 'Thành viên đăng ký', 'guard_name' => 'web']);
            $role->revokePermissionTo(Permission::all()); 
            $this->command->info("Default User role created successfully.");
        } catch (\Exception $e) {
            $this->command->error("Error creating User role: " . $e->getMessage());
        }

        // 4. Assign Super Admin
        if ($admin) {
            $admin->assignRole('super_admin');
            $this->command->info("Super Admin role assigned to admin user.");
            
            $admin->updateQuietly([
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            ]);
            $this->command->info("Admin password updated.");
        }

        // 5. Regular Users Pool (Must be AFTER Role creation)
        $this->command->warn(PHP_EOL . 'Creating regular users pool...');
        $users = User::factory(10)->create();
        foreach ($users as $u) {
            $u->assignRole('Thành viên đăng ký');
        }
        $this->command->info('10 regular users created and role assigned.');

        // 6. Call Content/Shop Seeders
        $this->call([
            ContentSeeder::class,
            UnifiedShopSeeder::class,
            EngagementSeeder::class,
            SystemSeeder::class,
        ]);
    }

    protected function withProgressBar(int $amount, Closure $createCollectionOfOne): Collection
    {
        $progressBar = new ProgressBar($this->command->getOutput(), $amount);
        $progressBar->start();
        $items = new Collection;
        foreach (range(1, $amount) as $i) {
            $items = $items->merge(
                $createCollectionOfOne()
            );
            $progressBar->advance();
        }
        $progressBar->finish();
        $this->command->getOutput()->writeln('');
        return $items;
    }
}
