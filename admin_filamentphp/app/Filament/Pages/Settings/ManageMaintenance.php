<?php

namespace App\Filament\Pages\Settings;

use App\Settings\MaintenanceSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use App\Traits\SidebarTrait;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;

class ManageMaintenance extends SettingsPage
{
    use SidebarTrait;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = MaintenanceSettings::class;

    public static function getNavigationLabel(): string
    {
        return trans('admin.settings.maintenance.label'); // hoặc 'Settings' nếu không dùng đa ngôn ngữ
    }

    public function getTitle(): string
    {
        return trans('admin.settings.maintenance.label');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->columns(2)->schema([
                    Toggle::make('maintenance_mode')
                        ->label(trans('admin.settings.maintenance.mode'))
                        ->default(fn() => ManageCustom::load()->maintenance_mode),
                    Textarea::make('allowed_ips')
                        ->label(trans('admin.settings.maintenance.allowed_ips'))
                        ->rows(5)
                        ->columnSpanFull()
                        ->default(fn() => ManageCustom::load()->allowed_ips),
                ])
            ]);
    }
}
