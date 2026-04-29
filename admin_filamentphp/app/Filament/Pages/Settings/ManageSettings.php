<?php

namespace App\Filament\Pages\Settings;

use Filament\Pages\SettingsPage;
use App\Settings\DBSettings;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Form;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;
use Filament\Forms;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use App\Traits\SidebarTrait;

class ManageSettings extends SettingsPage
{
    use SidebarTrait;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 8;

    protected static string $settings = DBSettings::class;

    public function getTitle(): string
    {
        return trans('admin.settings.setting.label');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                CuratorPicker::make('logo')
                    ->label(trans('admin.logo'))
                    ->default(fn() => DBSettings::load()->logo)
                    ->extraAttributes([
                        'style' => 'width: 20rem',
                    ])
                    ->columnSpanFull(),

                CuratorPicker::make('logo_favicon')
                    ->label(trans('admin.logo_favicon'))
                    ->default(fn() => DBSettings::load()->logo_favicon)
                    ->extraAttributes([
                        'style' => 'width: 20rem',
                    ])
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('name')
                    ->label(trans('admin.website_name'))
                    ->maxLength(255)
                    ->default(fn() => DBSettings::load()->name),

                Forms\Components\Select::make('new_user_role')
                    ->label(trans('admin.settings.setting.new_user_role'))
                    ->options(fn() => Role::pluck('name', 'name')->toArray())
                    ->searchable()
                    ->suffixIcon('heroicon-o-user-group')
                    ->default(fn() => DBSettings::load()->new_user_role),

                Forms\Components\MarkdownEditor::make('about')
                    ->label(trans('admin.website_description'))
                    ->columnSpanFull()
                    ->default(fn() => DBSettings::load()->about),

                Forms\Components\Select::make('timezone')
                    ->label(trans('admin.settings.setting.timezone'))
                    ->options(fn() => collect(timezone_identifiers_list())
                        ->mapToGroups(fn($tz) => [Str::before($tz, '/') => [$tz => $tz]])
                        ->map(fn($group) => $group->collapse()))
                    ->searchable()
                    ->suffixIcon('heroicon-o-globe-alt')
                    ->default(fn() => DBSettings::load()->timezone),

                Forms\Components\Select::make('default_language')
                    ->label(trans('admin.settings.setting.default_language'))
                    ->options(Config::get('app.available_locales', ['en' => 'English', 'vi' => 'Vietnamese']))
                    ->searchable()
                    ->suffixIcon('heroicon-o-globe-alt')
                    ->default(fn() => DBSettings::load()->default_language),

                Forms\Components\Select::make('currency')
                    ->label(trans('admin.settings.setting.currency'))
                    ->options([
                        'USD' => 'USD - US Dollar',
                        'EUR' => 'EUR - Euro',
                        'GBP' => 'GBP - British Pound',
                        'VND' => 'VND - Vietnamese Dong',
                        'JPY' => 'JPY - Japanese Yen',
                        'AUD' => 'AUD - Australian Dollar',
                        // Thêm các tiền tệ khác nếu cần
                    ])
                    ->searchable()
                    ->disabled()
                    ->suffixIcon('heroicon-o-currency-dollar')
                    ->default(fn() => DBSettings::load()->currency),

                Forms\Components\TextInput::make('currency_symbol')
                    ->label(trans('admin.settings.setting.currency_symbol'))
                    // ->suffixIcon('heroicon-o-cash')
                    ->default(fn() => [
                        'USD' => '$',
                        'EUR' => '€',
                        'GBP' => '£',
                        'VND' => 'đ',
                        'JPY' => '¥',
                        'AUD' => 'A$',
                    ][DBSettings::load()->currency_symbol] ?? '')
                    ->disabled()
                    ->dehydrated(false)
                    ->reactive()
                    ->afterStateUpdated(fn($set, $state) => $set('currency_symbol', [
                        'USD' => '$',
                        'EUR' => '€',
                        'GBP' => '£',
                        'VND' => 'đ',
                        'JPY' => '¥',
                        'AUD' => 'A$',
                    ][$state] ?? '')),

                Forms\Components\Toggle::make('send_welcome_email')
                    ->label(trans('admin.settings.setting.send_welcome_email'))
                    ->default(fn() => DBSettings::load()->send_welcome_email)
                    ->columnSpanFull(),
            ]),
        ]);
    }
}
