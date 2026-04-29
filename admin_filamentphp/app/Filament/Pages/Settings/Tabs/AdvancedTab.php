<?php

namespace App\Filament\Pages\Settings\Tabs;

use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Components\Grid;
use App\Filament\Resources\WebhookResource;
use App\Filament\Resources\WebhookLogResource;
use Z3d0X\FilamentFabricator\Models\Page;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Settings\WebhookManager;
use App\Livewire\Settings\WebhookLogViewer;
use App\Livewire\Settings\SystemLogViewer;

class AdvancedTab
{
    public static function make(): Tab
    {
        return Tab::make(trans('admin.settings.advanced'))
            ->label(trans('admin.settings.advanced'))
            ->icon('heroicon-o-command-line')
            ->schema([
                Section::make(trans('admin.shop.settings.page_setup'))
                    ->description(trans('admin.shop.settings.page_setup_desc'))
                    ->schema([
                        Select::make('advanced.cart_page_id')
                            ->label(trans('admin.shop.settings.cart_page'))
                            ->options(Page::pluck('title', 'id'))
                            ->searchable(),
                        Select::make('advanced.checkout_page_id')
                            ->label(trans('admin.shop.settings.checkout_page'))
                            ->options(Page::pluck('title', 'id'))
                            ->searchable(),
                        Select::make('advanced.account_page_id')
                            ->label(trans('admin.shop.settings.account_page'))
                            ->options(Page::pluck('title', 'id'))
                            ->searchable(),
                    ]),

                Tabs::make(trans('admin.settings.advanced'))
                    ->label(trans('admin.settings.advanced'))
                    ->tabs([
                        Tab::make(trans('admin.webhooks.label'))
                            ->label(trans('admin.webhooks.label'))
                            ->icon('heroicon-o-rss')
                            ->visible(fn() => WebhookResource::canViewAny())
                            ->schema([
                                Section::make(trans('admin.webhooks.label'))
                                    ->schema([
                                        Toggle::make('webhook.enabled')
                                            ->label(trans('admin.webhooks.enabled')),
                                        TextInput::make('webhook.log_retention_days')
                                            ->label(trans('admin.webhooks.log_retention_days'))
                                            ->numeric()
                                            ->default(30),
                                    ]),
                                Section::make(trans('admin.webhooks.documentation'))
                                    ->schema([
                                        Placeholder::make('webhook_docs')
                                            ->label(trans('admin.webhooks.documentation'))
                                            ->content(new HtmlString('
                                                <a href="/docs/webhook" target="_blank" class="text-primary-600 font-bold underline hover:text-primary-500">
                                                    ' . trans('admin.webhooks.view_documentation') . '
                                                </a>
                                            ')),
                                    ]),
                                Livewire::make(WebhookManager::class)
                                    ->key('webhook-manager')
                                    ->lazy(),
                            ]),

                        Tab::make(trans('admin.webhooks.log_label'))
                            ->label(trans('admin.webhooks.log_label'))
                            ->icon('heroicon-o-list-bullet')
                            ->visible(fn() => WebhookLogResource::canViewAny())
                            ->schema([
                                Livewire::make(WebhookLogViewer::class)
                                    ->key('webhook-log-viewer')
                                    ->lazy(),
                            ]),

                        Tab::make(trans('admin.api.label'))
                            ->label(trans('admin.api.label'))
                            ->icon('heroicon-o-key')
                            ->visible(fn() => Auth::user()?->can('view_api_settings') || Auth::user()?->is_admin) // Fallback check
                            ->schema([
                                Section::make(trans('admin.api.settings'))
                                    ->schema([
                                        Placeholder::make('api_docs')
                                            ->label(trans('admin.api.documentation'))
                                            ->content(new HtmlString('
                                                <a href="/docs/api" target="_blank" class="text-primary-600 font-bold underline hover:text-primary-500">
                                                    ' . trans('admin.api.view_documentation') . '
                                                </a>
                                            ')),
                                        Grid::make(2)
                                            ->schema([
                                                Toggle::make('api.enabled')
                                                    ->label(trans('admin.api.enabled')),
                                                TextInput::make('api.idempotency_ttl')
                                                    ->label(trans('admin.api.idempotency_ttl'))
                                                    ->numeric()
                                                    ->default(86400),
                                                TextInput::make('api.hmac_secret')
                                                    ->label(trans('admin.webhooks.secret'))
                                                    ->password()
                                                    ->revealable(),
                                            ]),
                                    ]),
                            ]),

                        Tab::make(trans('admin.logs.label'))
                            ->label(trans('admin.logs.label'))
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Livewire::make(SystemLogViewer::class)
                                    ->key('system-log-viewer')
                                    ->lazy(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
