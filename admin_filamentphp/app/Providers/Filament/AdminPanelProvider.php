<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use App\Filament\Pages\Dashboard;
use App\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\SpatieLaravelTranslatablePlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Z3d0X\FilamentFabricator\FilamentFabricatorPlugin;
use Filament\Facades\Filament;
use Kenepa\TranslationManager\TranslationManagerPlugin;
use Filament\Navigation\UserMenuItem;
use Illuminate\Support\Facades\Auth;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use App\Ecommerce\Core\Contracts\PanelServiceInterface;
use Biostate\FilamentMenuBuilder\FilamentMenuBuilderPlugin;
use Awcodes\Curator\CuratorPlugin;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Awcodes\Curator\Models\Media;
use Filament\Navigation\NavigationItem;
use Filament\Enums\ThemeMode;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;

class AdminPanelProvider extends PanelProvider
{
    protected $logoUrl = null;

    public function boot(): void
    {

        FilamentView::registerRenderHook(
            PanelsRenderHook::SIDEBAR_NAV_START,
            fn(): string => Blade::render('<livewire:logo-cuts />'),
        );


        Filament::serving(function () {
            Filament::registerUserMenuItems([
                'profile' => UserMenuItem::make()
                    ->url(url('/user/users/' . Auth::id() . '/edit'))
                    ->icon(null),

                'settings' => UserMenuItem::make()
                    ->label('Cài đặt')
                    ->url(url('/user/users/' . Auth::id() . '/edit'))
                    ->icon('heroicon-o-cog')
                    ->sort(-1), // Đặt trên phần chuyển đổi theme

                'document' => UserMenuItem::make()
                    ->label('Tài liệu')
                    ->url(url('https://drive.google.com/file/d/1AS2vOao4F9rkdbqIMDOS7BkBADMRmeue/view'))
                    ->icon('heroicon-o-document-text')
                    ->sort(-3), // Đặt trên phần chuyển đổi theme,
            ]);
        });

        Filament::registerNavigationGroups([
            // Groups removed to flatten sidebar
        ]);
    }
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()

            ->id('admin')

            ->defaultThemeMode(ThemeMode::Dark)

            ->colors([
                'danger' => Color::Rose,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'primary' => Color::Fuchsia,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])

            ->maxContentWidth('full') // Giảm khoảng trắng

            ->login(Login::class)

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')

            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')

            ->pages([
                Dashboard::class,
            ])

            ->renderHook(PanelsRenderHook::GLOBAL_SEARCH_START, function () {
                $timezone = app(PanelServiceInterface::class)->getTimezone();
                return Blade::render("
                    <div id='current-time' style='color: white; margin-right:1rem; font-weight: bold;'></div>
                    <script>
                        function updateTime() {
                            let now = new Date().toLocaleTimeString('vi-VN', { timeZone: '$timezone', hour12: false });
                            document.getElementById('current-time').innerText = now;
                        }
                        setInterval(updateTime, 1000);
                        updateTime();
                    </script>
                ");
            })

            ->viteTheme('resources/css/filament/admin/theme.css')

            ->sidebarCollapsibleOnDesktop()

            ->sidebarWidth('16rem')

            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            // ->widgets([])
            ->unsavedChangesAlerts()


            ->brandName(fn() => app(PanelServiceInterface::class)->getBrandName())
            ->brandLogoHeight('1.25rem')

            ->databaseNotifications()

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                SpatieLaravelTranslatablePlugin::make()
                    ->defaultLocales(['vi', 'en']),
                FilamentShieldPlugin::make(),
                FilamentFabricatorPlugin::make(),
                // FilamentMenuBuilderPlugin::make(), // Temporarily disabled due to error
                CuratorPlugin::make()
                    ->label('Media')
                    ->pluralLabel('Media')
                    ->navigationIcon('heroicon-o-photo')
                    ->navigationSort(3)
                    ->defaultListView('grid'),
                TranslationManagerPlugin::make(),
                FilamentApexChartsPlugin::make(),
                // \Rupadana\FilamentApiService\FilamentApiServicePlugin::make(),
            ]);
    }
}
