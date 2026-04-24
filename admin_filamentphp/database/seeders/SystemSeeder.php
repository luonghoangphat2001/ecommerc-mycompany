<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\MailLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $admin = User::where('email', 'admin@admin.com')->first() ?? $users->first();

        // 1. Database Notifications
        if ($admin && $users->isNotEmpty()) {
            $this->seedNotifications($users, $admin);
        }

        // 2. Mail Logs (Using Factory)
        if (class_exists(MailLog::class)) {
            MailLog::factory()->count(15)->create();
        }
    }

    protected function seedNotifications($users, $admin): void
    {
        foreach ($users->random(min(5, $users->count())) as $user) {
            for ($i = 0; $i < 3; $i++) {
                DB::table('notifications')->insert([
                    'id' => (string) Str::uuid(),
                    'type' => 'App\Notifications\SystemAlert',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => json_encode([
                        'title' => 'Thông báo hệ thống ' . ($i + 1),
                        'message' => 'Dữ liệu mẫu dành cho ' . $user->name,
                        'action_url' => '/admin',
                    ]),
                    'read_at' => rand(0, 1) ? now() : null,
                    'created_at' => now()->subHours(rand(1, 48)),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
