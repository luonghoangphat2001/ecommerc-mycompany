<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

return new class extends SettingsMigration
{
    public function up(): void
    {

        $this->migrator->add('settings.logo', '');
        $this->migrator->add('settings.logo_favicon', '');
        $this->migrator->add('settings.name', 'Admin');
        $this->migrator->add('settings.about', '');
        $this->migrator->add('settings.timezone', 'Asia/Ho_Chi_Minh');
        $this->migrator->add('settings.default_language', 'Vietnamese');
        $this->migrator->add('settings.currency', 'VND');
        $this->migrator->add('settings.currency_symbol', 'đ');
        $this->migrator->add('settings.new_user_role', 'User');
        $this->migrator->add('settings.send_welcome_email', '');
    }
    public function down(): void
    {

        $this->migrator->delete('settings.logo');
        $this->migrator->delete('settings.logo_favicon');
        $this->migrator->delete('settings.name');
        $this->migrator->delete('settings.about');
        $this->migrator->delete('settings.timezone');
        $this->migrator->delete('settings.currency');
        $this->migrator->delete('settings.currency_symbol');
        $this->migrator->delete('settings.default_language');
        $this->migrator->delete('settings.new_user_role');
        $this->migrator->delete('settings.send_welcome_email');
    }


    private function createMedia(string $path): ?int
    {
        if (!file_exists($path)) {
            Log::error('File not found: ' . $path);
            return null;
        }

        $image = file_get_contents($path);
        $filename = Str::uuid() . '.jpg';
        Storage::disk('public')->put('media/' . $filename, $image);

        return DB::table('media')->insertGetId([
            'disk' => 'public',
            'directory' => 'media',
            'visibility' => 'public',
            'name' => pathinfo($path, PATHINFO_FILENAME),
            'path' => 'media/' . $filename,
            'width' => 800,
            'height' => 600,
            'size' => filesize($path),
            'type' => 'image/jpeg',
            'ext' => pathinfo($path, PATHINFO_EXTENSION),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
