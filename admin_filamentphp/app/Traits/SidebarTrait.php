<?php

namespace App\Traits;

use Filament\Pages\Concerns\InteractsWithPageSidebar;
use Filament\Support\Enums\Alignment;
use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\Artisan;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Spatie\Sitemap\SitemapGenerator;
use Illuminate\Support\Facades\Storage;

trait SidebarTrait
{

    use HasPageSidebar;

    public static function sidebar(): FilamentPageSidebar
    {
        return FilamentPageSidebar::make()
            // ->setTitle(trans('admin.settings.navGroup'))
            ->setDescriptionCopyable()
            ->setNavigationItems([
                PageNavigationItem::make(trans('admin.settings.setting.label'))
                    ->url(\App\Filament\Pages\Settings\ManageSettings::getUrl())
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->isActiveWhen(fn() => request()->routeIs(\App\Filament\Pages\Settings\ManageSettings::getRouteName())),

                PageNavigationItem::make(trans('admin.settings.mail.label'))
                    ->url(\App\Filament\Pages\Settings\ManageMail::getUrl())
                    ->icon('heroicon-o-envelope')
                    ->isActiveWhen(fn() => request()->routeIs(\App\Filament\Pages\Settings\ManageMail::getRouteName())),

                PageNavigationItem::make(trans('admin.settings.maintenance.label'))
                    ->url(\App\Filament\Pages\Settings\ManageMaintenance::getUrl())
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->isActiveWhen(fn() => request()->routeIs(\App\Filament\Pages\Settings\ManageMaintenance::getRouteName())),

                PageNavigationItem::make(trans('admin.settings.footer.label'))
                    ->url(\App\Filament\Pages\Settings\ManageFooter::getUrl())
                    ->icon('heroicon-o-rectangle-group')
                    ->isActiveWhen(fn() => request()->routeIs(\App\Filament\Pages\Settings\ManageFooter::getRouteName())),

                PageNavigationItem::make(trans('admin.settings.custom.label'))
                    ->url(\App\Filament\Pages\Settings\ManageCustom::getUrl())
                    ->icon('heroicon-o-paint-brush')
                    ->isActiveWhen(fn() => request()->routeIs(\App\Filament\Pages\Settings\ManageCustom::getRouteName())),

                PageNavigationItem::make(trans('admin.shop.settings.label'))
                    ->url(\App\Filament\Pages\Settings\ShopSettings::getUrl())
                    ->icon('heroicon-o-cog-6-tooth')
                    ->isActiveWhen(fn() => request()->routeIs(\App\Filament\Pages\Settings\ShopSettings::getRouteName())),
            ]);
    }

    public function getActions(): array
    {
        return [
            // Nút Clear Cache
            Action::make('clearCache')
                ->label(trans('admin.settings.clearCache'))
                ->color('danger')
                // ->icon('heroicon-o-trash')
                ->action(fn() => self::clearCache())
                ->requiresConfirmation()
                ->modalHeading('Xác nhận xoá cache')
                ->modalDescription('Bạn có chắc muốn xóa cache không?')
                ->modalButton('Xóa'),

            // Nút Update Sitemap
            // Action::make('updateSiteMap')
            //     ->label(trans('admin.settings.updateSiteMap'))
            //     ->color('primary')
            //     // ->icon('heroicon-o-refresh')
            //     ->action(fn() => self::updateSiteMap())
            //     ->requiresConfirmation()
            //     ->modalHeading('Xác nhận cập nhật Sitemap')
            //     ->modalDescription('Bạn có chắc muốn cập nhật sitemap không?')
            //     ->modalButton('Cập nhật'),

            // Nút Import Dummy Data
            Action::make('importDummyData')
                ->label(trans('admin.settings.importDummyData'))
                ->color('warning')
                // ->icon('heroicon-o-database')
                ->action(fn() => self::importDummyData())
                ->requiresConfirmation()
                ->modalHeading('Xác nhận Import Data')
                ->modalDescription('Bạn có chắc muốn import dữ liệu mẫu không?')
                ->modalButton('Import'),



        ];
    }

    protected static function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');

        Notification::make()
            ->title('Cache đã được xoá thành công!')
            ->success()
            ->send();
    }

    protected static function importDummyData()
    {

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\ImportDatabaseSeeder'
        ]);
    }

    public static function updateSiteMap()
    {
        try {
            // Tạo và lưu Sitemap vào file 'sitemap.xml' trong thư mục public
            SitemapGenerator::create(config('app.url'))
                ->writeToFile(public_path('sitemap.xml')); // Lưu sitemap vào thư mục public

            return 'Sitemap đã được cập nhật thành công!';
        } catch (\Exception $e) {
            return 'Đã xảy ra lỗi khi cập nhật Sitemap: ' . $e->getMessage();
        }
    }
}
