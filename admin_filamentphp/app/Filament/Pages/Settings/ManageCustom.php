<?php

namespace App\Filament\Pages\Settings;

use App\Settings\CustomSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Filament\Forms\Components\TextInput;
use App\Traits\SidebarTrait;
use Filament\Forms\Components\Textarea;

class ManageCustom extends SettingsPage
{
    use SidebarTrait;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static string $settings = CustomSettings::class;

    public static function getNavigationLabel(): string
    {
        return trans('admin.settings.custom.label'); // hoặc 'Settings' nếu không dùng đa ngôn ngữ
    }

    public function getTitle(): string
    {
        return trans('admin.settings.custom.label');
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
                    Textarea::make('custom_js')
                        ->label(trans('admin.settings.custom.js'))
                        ->rows(8)
                        ->columnSpanFull()
                        ->default(fn() => CustomSettings::load()->custom_js),
                    Textarea::make('custom_css')
                        ->label(trans('admin.settings.custom.css'))
                        ->columnSpanFull()
                        ->rows(8)
                        ->default(fn() => CustomSettings::load()->custom_css),
                ])
            ]);
    }
}
