<?php

namespace App\Filament\Pages\Settings\Tabs;

use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TagsInput;

class EmailTab
{
    public static function make(): Tab
    {
        return Tab::make(trans('admin.email'))
            ->label(trans('admin.email'))
            ->icon('heroicon-o-envelope')
            ->schema([
                Section::make(trans('admin.shop.settings.sender_options'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('emails.sender_name')
                                    ->label(trans('admin.name')),
                                TextInput::make('emails.sender_email')
                                    ->label(trans('admin.email'))
                                    ->email(),
                                ColorPicker::make('emails.base_color')
                                    ->label(trans('admin.shop.settings.email_base_color')),
                            ]),
                    ]),

                Section::make(trans('admin.shop.settings.order_notifications'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Fieldset::make(trans('admin.shop.settings.order_created'))
                                    ->schema([
                                        Toggle::make('emails.notifications.new_order.enabled')
                                            ->label(trans('admin.shop.settings.enable_notifications'))
                                            ->reactive(),
                                        TagsInput::make('emails.notifications.new_order.recipients')
                                            ->label(trans('admin.email'))
                                            ->placeholder('admin@example.com')
                                            ->visible(fn($get) => $get('emails.notifications.new_order.enabled')),
                                    ]),
                                Fieldset::make(trans('admin.shop.settings.order_cancelled'))
                                    ->schema([
                                        Toggle::make('emails.notifications.cancelled_order.enabled')
                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                    ]),
                                Fieldset::make(trans('admin.shop.settings.order_failed'))
                                    ->schema([
                                        Toggle::make('emails.notifications.failed_order.enabled')
                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                    ]),
                                Fieldset::make(trans('admin.shop.settings.order_on_hold'))
                                    ->schema([
                                        Toggle::make('emails.notifications.order_on_hold.enabled')
                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                    ]),
                                Fieldset::make(trans('admin.shop.settings.order_processing'))
                                    ->schema([
                                        Toggle::make('emails.notifications.processing_order.enabled')
                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                    ]),
                                Fieldset::make(trans('admin.shop.settings.order_completed'))
                                    ->schema([
                                        Toggle::make('emails.notifications.completed_order.enabled')
                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                    ]),
                                Fieldset::make(trans('admin.shop.settings.order_refunded'))
                                    ->schema([
                                        Toggle::make('emails.notifications.refunded_order.enabled')
                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                    ]),
                                Fieldset::make(trans('admin.shop.settings.order_details'))
                                    ->schema([
                                        Toggle::make('emails.notifications.order_details.enabled')
                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                    ]),
                                Fieldset::make(trans('admin.shop.settings.customer_note'))
                                    ->schema([
                                        Toggle::make('emails.notifications.customer_note.enabled')
                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                    ]),
                            ]),
                    ]),

                Section::make(trans('admin.shop.settings.account_and_system'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Fieldset::make(trans('admin.shop.settings.reset_password'))
                                    ->schema([
                                        Toggle::make('emails.notifications.reset_password.enabled')
                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                    ]),
                                Fieldset::make(trans('admin.shop.settings.new_account'))
                                    ->schema([
                                        Toggle::make('emails.notifications.new_account.enabled')
                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                    ]),
                                Fieldset::make(trans('admin.shop.settings.store_credit'))
                                    ->schema([
                                        Toggle::make('emails.notifications.store_credit.enabled')
                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                    ]),
                                Fieldset::make(trans('admin.shop.settings.shipping_fulfillment'))
                                    ->schema([
                                        Toggle::make('emails.notifications.shipping_fulfillment.enabled')
                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                    ]),
                                Fieldset::make(trans('admin.shop.settings.payment_retry_customer'))
                                    ->schema([
                                        Toggle::make('emails.notifications.payment_retry_customer.enabled')
                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                    ]),
                                Fieldset::make(trans('admin.shop.settings.payment_retry_admin'))
                                    ->schema([
                                        Toggle::make('emails.notifications.payment_retry_admin.enabled')
                                            ->label(trans('admin.shop.settings.enable_notifications'))
                                            ->reactive(),
                                        TagsInput::make('emails.notifications.payment_retry_admin.recipients')
                                            ->label(trans('admin.email'))
                                            ->placeholder('admin@example.com')
                                            ->visible(fn($get) => $get('emails.notifications.payment_retry_admin.enabled')),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
