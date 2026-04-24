<?php

namespace App\Filament\Pages\Settings;

use App\Settings\FooterSettings;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use App\Traits\SidebarTrait;


class ManageFooter extends SettingsPage
{
    use SidebarTrait;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static string $settings = FooterSettings::class;

    public static function getNavigationLabel(): string
    {
        return trans('admin.settings.footer.label'); // hoặc 'Settings' nếu không dùng đa ngôn ngữ
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return trans('admin.settings.footer.label');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('copyright')
                    ->label(trans('admin.settings.footer.copyright'))
                    ->default(fn() => FooterSettings::load()->copyright)
                    ->required(),
                Repeater::make('links')
                    ->schema([
                        TextInput::make('label')
                            ->default(fn() => FooterSettings::load()->label)
                            ->required(),
                        TextInput::make('url')
                            ->default(fn() => FooterSettings::load()->url)
                            ->url()
                            ->required(),
                    ]),
            ]);
    }
}
