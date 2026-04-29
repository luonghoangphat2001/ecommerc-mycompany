<?php

namespace App\Filament\Pages\Settings\Tabs;

use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;

class CheckoutTab
{
    public static function make(): Tab
    {
        return Tab::make(trans('admin.order.checkout'))
            ->label(trans('admin.order.checkout'))
            ->icon('heroicon-o-credit-card')
            ->schema([
                Section::make(trans('admin.shop.settings.checkout_options'))
                    ->schema([
                        Toggle::make('checkout.enable_guest_checkout')
                            ->label(trans('admin.shop.settings.allow_guest_checkout')),
                    ]),

                Section::make(trans('admin.shop.settings.tax_options'))
                    ->schema([
                        Select::make('checkout.tax_calculation_address')
                            ->label(trans('admin.shop.settings.tax_based_on'))
                            ->options([
                                'shipping' => trans('admin.order.shipping_address'),
                                'billing' => trans('admin.order.billing_address'),
                                'base' => trans('admin.shop.settings.store_address'),
                            ]),
                        Toggle::make('checkout.prices_include_tax')
                            ->label(trans('admin.shop.settings.prices_entered_with_tax')),
                    ]),

                Section::make(trans('admin.shop.settings.payment_gateways'))
                    ->description(trans('admin.shop.settings.payment_gateways_desc'))
                    ->schema([
                        CheckboxList::make('checkout.enabled_payment_gateways')
                            ->label(trans('admin.shop.settings.active_gateways'))
                            ->options([
                                'cod' => trans('admin.shop.settings.cod'),
                                'bank_transfer' => trans('admin.shop.settings.bank_transfer'),
                                'paypal' => trans('admin.shop.settings.paypal'),
                                'stripe' => trans('admin.shop.settings.stripe'),
                                'vnpay' => trans('admin.shop.settings.vnpay'),
                                'momo' => trans('admin.shop.settings.momo'),
                            ])
                            ->columns(3)
                            ->reactive(),

                        // Stripe Configuration
                        Fieldset::make(trans('admin.shop.settings.stripe'))
                            ->schema([
                                TextInput::make('checkout.stripe_public_key')
                                    ->label(trans('admin.shop.settings.publishable_key')),
                                TextInput::make('checkout.stripe_secret_key')
                                    ->label(trans('admin.shop.settings.secret_key'))
                                    ->password(),
                                TextInput::make('checkout.stripe_webhook_secret')
                                    ->label(trans('admin.shop.settings.webhook_secret'))
                                    ->password(),
                            ])
                            ->visible(fn($get) => collect($get('checkout.enabled_payment_gateways'))->contains('stripe')),

                        // PayPal Configuration
                        Fieldset::make(trans('admin.shop.settings.paypal'))
                            ->schema([
                                TextInput::make('checkout.paypal_client_id')
                                    ->label(trans('admin.shop.settings.client_id')),
                                TextInput::make('checkout.paypal_secret')
                                    ->label(trans('admin.shop.settings.secret'))
                                    ->password(),
                                Select::make('checkout.paypal_mode')
                                    ->label(trans('admin.shop.settings.environment'))
                                    ->options([
                                        'sandbox' => trans('admin.shop.settings.sandbox'),
                                        'live' => trans('admin.shop.settings.live'),
                                    ]),
                            ])
                            ->visible(fn($get) => collect($get('checkout.enabled_payment_gateways'))->contains('paypal')),

                        // VNPay Configuration
                        Fieldset::make(trans('admin.shop.settings.vnpay'))
                            ->schema([
                                TextInput::make('checkout.vnpay_tmn_code')
                                    ->label(trans('admin.shop.settings.tmn_code')),
                                TextInput::make('checkout.vnpay_hash_secret')
                                    ->label(trans('admin.shop.settings.hash_secret'))
                                    ->password(),
                            ])
                            ->visible(fn($get) => collect($get('checkout.enabled_payment_gateways'))->contains('vnpay')),

                        // Momo Configuration
                        Fieldset::make(trans('admin.shop.settings.momo'))
                            ->schema([
                                TextInput::make('checkout.momo_partner_code')
                                    ->label(trans('admin.shop.settings.partner_code')),
                                TextInput::make('checkout.momo_access_key')
                                    ->label(trans('admin.shop.settings.access_key')),
                                TextInput::make('checkout.momo_secret_key')
                                    ->label(trans('admin.shop.settings.secret_key'))
                                    ->password(),
                            ])
                            ->visible(fn($get) => collect($get('checkout.enabled_payment_gateways'))->contains('momo')),
                    ]),
            ]);
    }
}
