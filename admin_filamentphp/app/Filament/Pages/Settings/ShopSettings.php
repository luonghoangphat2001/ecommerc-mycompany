<?php

namespace App\Filament\Pages\Settings;

use Filament\Forms\Form;
use Filament\Forms;
use Filament\Pages\Page;
use App\Settings\GeneralSettings;
use App\Settings\ProductSettings;
use App\Settings\CheckoutSettings;
use App\Settings\EmailSettings;
use App\Settings\AdvancedSettings;
use App\Settings\ApiSettings;
use App\Settings\WebhookSettings;
use App\Filament\Resources\WebhookResource;
use App\Filament\Resources\WebhookLogResource;
use Filament\Forms\Components\Tabs;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Livewire;

class ShopSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $slug = 'shop-settings';

    protected static string $view = 'admin.pages.settings.shop-settings';

    public ?array $data = [];

    public static function getNavigationSort(): ?int
    {
        return 9;
    }

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $this->data = [
            'general' => app(GeneralSettings::class)->toArray(),
            'products' => app(ProductSettings::class)->toArray(),
            'checkout' => app(CheckoutSettings::class)->toArray(),
            'emails' => app(EmailSettings::class)->toArray(),
            'advanced' => app(AdvancedSettings::class)->toArray(),
            'api' => app(ApiSettings::class)->toArray(),
            'webhook' => app(WebhookSettings::class)->toArray(),

        ];

        $this->form->fill($this->data);
    }

    public function getTitle(): string
    {
        return trans('admin.shop.settings.label');
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin.shop.settings.label');
    }

    protected static ?int $navigationSort = 100;

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Tabs::make(trans('admin.shop.settings.label'))
                    ->label(trans('admin.shop.settings.label'))
                    ->tabs([
                        // 1. GENERAL
                        Tabs\Tab::make(trans('admin.shop.settings.general'))
                            ->label(trans('admin.shop.settings.general'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Section::make(trans('admin.shop.settings.store_info'))
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('general.store_name')
                                                    ->label(trans('admin.name'))
                                                    ->required(),
                                                Forms\Components\TextInput::make('general.store_email')
                                                    ->label(trans('admin.email'))
                                                    ->email(),
                                                Forms\Components\TextInput::make('general.store_phone')
                                                    ->label(trans('admin.phone')),
                                                Forms\Components\FileUpload::make('general.logo')
                                                    ->label(trans('admin.logo'))
                                                    ->image()
                                                    ->directory('settings'),
                                                Forms\Components\FileUpload::make('general.favicon')
                                                    ->label(trans('admin.logo_favicon'))
                                                    ->image()
                                                    ->directory('settings'),
                                            ]),
                                    ]),

                                Forms\Components\Section::make(trans('admin.shop.settings.localization'))
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Select::make('general.store_country')
                                                    ->label(trans('admin.country'))
                                                    ->options(config('countries'))
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                        if (!$state) return;
                                                        $info = app(\App\Ecommerce\Settings\Contracts\ShopSettingServiceInterface::class)->getLocalizationInfo($state);
                                                        if ($info) {
                                                            $set('general.default_currency', $info['default_currency']);
                                                            $set('general.currency_symbol', $info['currency_symbol']);
                                                            $set('general.currency_position', $info['currency_position']);
                                                            $set('general.thousand_separator', $info['thousand_separator']);
                                                            $set('general.decimal_separator', $info['decimal_separator']);
                                                            $set('general.decimal_places', $info['decimal_places']);
                                                        }
                                                    }),
                                                Forms\Components\Select::make('general.default_currency')
                                                    ->label(trans('admin.currency'))
                                                    ->options(config('locale-info.currencies'))
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(),
                                            ]),
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('general.currency_symbol')
                                                    ->label(trans('admin.shop.settings.currency_symbol'))
                                                    ->required(),
                                                Forms\Components\Select::make('general.currency_position')
                                                    ->label(trans('admin.shop.settings.currency_position'))
                                                    ->options(trans('admin.shop.settings.position_options'))
                                                    ->required(),
                                                Forms\Components\TextInput::make('general.decimal_places')
                                                    ->label(trans('admin.shop.settings.decimal_places'))
                                                    ->numeric()
                                                    ->required(),
                                            ]),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('general.thousand_separator')
                                                    ->label(trans('admin.shop.settings.thousand_separator'))
                                                    ->required(),
                                                Forms\Components\TextInput::make('general.decimal_separator')
                                                    ->label(trans('admin.shop.settings.decimal_separator'))
                                                    ->required(),
                                            ]),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Select::make('general.weight_unit')
                                                    ->label(trans('admin.settings.weight_unit'))
                                                    ->options(config('units.weight', ['kg' => 'Kilogram (kg)', 'g' => 'Gram (g)', 'lb' => 'Pound (lb)'])),
                                                Forms\Components\Select::make('general.dimension_unit')
                                                    ->label(trans('admin.settings.dimension_unit'))
                                                    ->options(config('units.dimension', ['cm' => 'Centimeter (cm)', 'm' => 'Meter (m)', 'in' => 'Inch (in)'])),
                                            ]),
                                    ]),
                            ]),

                        // 2. PRODUCTS
                        Tabs\Tab::make(trans('admin.product.label'))
                            ->label(trans('admin.product.label'))
                            ->icon('heroicon-o-shopping-bag')
                            ->schema([
                                Forms\Components\Section::make(trans('admin.shop.settings.cart_behavior'))
                                    ->schema([
                                        Forms\Components\Radio::make('products.add_to_cart_behavior')
                                            ->label(trans('admin.shop.settings.after_add_to_cart'))
                                            ->options([
                                                'ajax' => trans('admin.shop.settings.stay_on_page'),
                                                'redirect' => trans('admin.shop.settings.redirect_to_cart'),
                                            ])
                                            ->inline(),
                                    ]),

                                Forms\Components\Section::make(trans('admin.shop.settings.reviews'))
                                    ->schema([
                                        Forms\Components\Toggle::make('products.enable_reviews')
                                            ->label(trans('admin.shop.settings.enable_product_reviews'))
                                            ->reactive(),
                                        Forms\Components\Group::make([
                                            Forms\Components\Toggle::make('products.guest_reviews_allowed')
                                                ->label(trans('admin.shop.settings.allow_guest_reviews')),
                                            Forms\Components\Toggle::make('products.review_stars_required')
                                                ->label(trans('admin.shop.settings.star_rating_required')),
                                        ])->visible(fn($get) => $get('products.enable_reviews')),
                                    ]),
                            ]),

                        // 3. CHECKOUT & PAYMENTS
                        Tabs\Tab::make(trans('admin.order.checkout'))
                            ->label(trans('admin.order.checkout'))
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Forms\Components\Section::make(trans('admin.shop.settings.checkout_options'))
                                    ->schema([
                                        Forms\Components\Toggle::make('checkout.enable_guest_checkout')
                                            ->label(trans('admin.shop.settings.allow_guest_checkout')),
                                    ]),

                                Forms\Components\Section::make(trans('admin.shop.settings.tax_options'))
                                    ->schema([
                                        Forms\Components\Select::make('checkout.tax_calculation_address')
                                            ->label(trans('admin.shop.settings.tax_based_on'))
                                            ->options([
                                                'shipping' => trans('admin.order.shipping_address'),
                                                'billing' => trans('admin.order.billing_address'),
                                                'base' => trans('admin.shop.settings.store_address'),
                                            ]),
                                        Forms\Components\Toggle::make('checkout.prices_include_tax')
                                            ->label(trans('admin.shop.settings.prices_entered_with_tax')),
                                    ]),

                                Forms\Components\Section::make(trans('admin.shop.settings.payment_gateways'))
                                    ->description(trans('admin.shop.settings.payment_gateways_desc'))
                                    ->schema([
                                        Forms\Components\CheckboxList::make('checkout.enabled_payment_gateways')
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
                                        Forms\Components\Fieldset::make(trans('admin.shop.settings.stripe'))
                                            ->schema([
                                                Forms\Components\TextInput::make('checkout.stripe_public_key')
                                                    ->label(trans('admin.shop.settings.publishable_key')),
                                                Forms\Components\TextInput::make('checkout.stripe_secret_key')
                                                    ->label(trans('admin.shop.settings.secret_key'))
                                                    ->password(),
                                                Forms\Components\TextInput::make('checkout.stripe_webhook_secret')
                                                    ->label(trans('admin.shop.settings.webhook_secret'))
                                                    ->password(),
                                            ])
                                            ->visible(fn($get) => collect($get('checkout.enabled_payment_gateways'))->contains('stripe')),

                                        // PayPal Configuration
                                        Forms\Components\Fieldset::make(trans('admin.shop.settings.paypal'))
                                            ->schema([
                                                Forms\Components\TextInput::make('checkout.paypal_client_id')
                                                    ->label(trans('admin.shop.settings.client_id')),
                                                Forms\Components\TextInput::make('checkout.paypal_secret')
                                                    ->label(trans('admin.shop.settings.secret'))
                                                    ->password(),
                                                Forms\Components\Select::make('checkout.paypal_mode')
                                                    ->label(trans('admin.shop.settings.environment'))
                                                    ->options([
                                                        'sandbox' => trans('admin.shop.settings.sandbox'),
                                                        'live' => trans('admin.shop.settings.live'),
                                                    ]),
                                            ])
                                            ->visible(fn($get) => collect($get('checkout.enabled_payment_gateways'))->contains('paypal')),

                                        // VNPay Configuration
                                        Forms\Components\Fieldset::make(trans('admin.shop.settings.vnpay'))
                                            ->schema([
                                                Forms\Components\TextInput::make('checkout.vnpay_tmn_code')
                                                    ->label(trans('admin.shop.settings.tmn_code')),
                                                Forms\Components\TextInput::make('checkout.vnpay_hash_secret')
                                                    ->label(trans('admin.shop.settings.hash_secret'))
                                                    ->password(),
                                            ])
                                            ->visible(fn($get) => collect($get('checkout.enabled_payment_gateways'))->contains('vnpay')),

                                        // Momo Configuration
                                        Forms\Components\Fieldset::make(trans('admin.shop.settings.momo'))
                                            ->schema([
                                                Forms\Components\TextInput::make('checkout.momo_partner_code')
                                                    ->label(trans('admin.shop.settings.partner_code')),
                                                Forms\Components\TextInput::make('checkout.momo_access_key')
                                                    ->label(trans('admin.shop.settings.access_key')),
                                                Forms\Components\TextInput::make('checkout.momo_secret_key')
                                                    ->label(trans('admin.shop.settings.secret_key'))
                                                    ->password(),
                                            ])
                                            ->visible(fn($get) => collect($get('checkout.enabled_payment_gateways'))->contains('momo')),
                                    ]),
                            ]),

                        // 4. SHIPPING
                        Tabs\Tab::make(trans('admin.shop.settings.shipping'))
                            ->label(trans('admin.shop.settings.shipping'))
                            ->icon('heroicon-o-truck')
                            ->schema([
                                Forms\Components\Placeholder::make('shipping_link')
                                    ->label(trans('admin.shop.settings.shipping_zone_management'))
                                    ->content(trans('admin.shop.settings.manage_shipping_zones_desc'))
                                    ->hintAction(
                                        Forms\Components\Actions\Action::make('go_to_shipping')
                                            ->label(trans('admin.shop.settings.go_to_shipping'))
                                            ->url(fn() => \App\Filament\Resources\ShippingZoneResource::getUrl('index'))
                                            ->icon('heroicon-m-arrow-top-right-on-square')
                                    ),
                            ]),

                        // 5. EMAILS
                        Tabs\Tab::make(trans('admin.email'))
                            ->label(trans('admin.email'))
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                Forms\Components\Section::make(trans('admin.shop.settings.sender_options'))
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('emails.sender_name')
                                                    ->label(trans('admin.name')),
                                                Forms\Components\TextInput::make('emails.sender_email')
                                                    ->label(trans('admin.email'))
                                                    ->email(),
                                                Forms\Components\ColorPicker::make('emails.base_color')
                                                    ->label(trans('admin.shop.settings.email_base_color')),
                                            ]),
                                    ]),

                                Forms\Components\Section::make(trans('admin.shop.settings.order_notifications'))
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\Fieldset::make(trans('admin.shop.settings.order_created'))
                                                    ->schema([
                                                        Forms\Components\Toggle::make('emails.notifications.new_order.enabled')
                                                            ->label(trans('admin.shop.settings.enable_notifications'))
                                                            ->reactive(),
                                                        Forms\Components\TagsInput::make('emails.notifications.new_order.recipients')
                                                            ->label(trans('admin.email'))
                                                            ->placeholder('admin@example.com')
                                                            ->visible(fn($get) => $get('emails.notifications.new_order.enabled')),
                                                    ]),
                                                Forms\Components\Fieldset::make(trans('admin.shop.settings.order_cancelled'))
                                                    ->schema([
                                                        Forms\Components\Toggle::make('emails.notifications.cancelled_order.enabled')
                                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                                    ]),
                                                Forms\Components\Fieldset::make(trans('admin.shop.settings.order_failed'))
                                                    ->schema([
                                                        Forms\Components\Toggle::make('emails.notifications.failed_order.enabled')
                                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                                    ]),
                                                Forms\Components\Fieldset::make(trans('admin.shop.settings.order_on_hold'))
                                                    ->schema([
                                                        Forms\Components\Toggle::make('emails.notifications.order_on_hold.enabled')
                                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                                    ]),
                                                Forms\Components\Fieldset::make(trans('admin.shop.settings.order_processing'))
                                                    ->schema([
                                                        Forms\Components\Toggle::make('emails.notifications.processing_order.enabled')
                                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                                    ]),
                                                Forms\Components\Fieldset::make(trans('admin.shop.settings.order_completed'))
                                                    ->schema([
                                                        Forms\Components\Toggle::make('emails.notifications.completed_order.enabled')
                                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                                    ]),
                                                Forms\Components\Fieldset::make(trans('admin.shop.settings.order_refunded'))
                                                    ->schema([
                                                        Forms\Components\Toggle::make('emails.notifications.refunded_order.enabled')
                                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                                    ]),
                                                Forms\Components\Fieldset::make(trans('admin.shop.settings.order_details'))
                                                    ->schema([
                                                        Forms\Components\Toggle::make('emails.notifications.order_details.enabled')
                                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                                    ]),
                                                Forms\Components\Fieldset::make(trans('admin.shop.settings.customer_note'))
                                                    ->schema([
                                                        Forms\Components\Toggle::make('emails.notifications.customer_note.enabled')
                                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                                    ]),
                                            ]),
                                    ]),

                                Forms\Components\Section::make(trans('admin.shop.settings.account_and_system'))
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\Fieldset::make(trans('admin.shop.settings.reset_password'))
                                                    ->schema([
                                                        Forms\Components\Toggle::make('emails.notifications.reset_password.enabled')
                                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                                    ]),
                                                Forms\Components\Fieldset::make(trans('admin.shop.settings.new_account'))
                                                    ->schema([
                                                        Forms\Components\Toggle::make('emails.notifications.new_account.enabled')
                                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                                    ]),
                                                Forms\Components\Fieldset::make(trans('admin.shop.settings.store_credit'))
                                                    ->schema([
                                                        Forms\Components\Toggle::make('emails.notifications.store_credit.enabled')
                                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                                    ]),
                                                Forms\Components\Fieldset::make(trans('admin.shop.settings.shipping_fulfillment'))
                                                    ->schema([
                                                        Forms\Components\Toggle::make('emails.notifications.shipping_fulfillment.enabled')
                                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                                    ]),
                                                Forms\Components\Fieldset::make(trans('admin.shop.settings.payment_retry_customer'))
                                                    ->schema([
                                                        Forms\Components\Toggle::make('emails.notifications.payment_retry_customer.enabled')
                                                            ->label(trans('admin.shop.settings.enable_notifications')),
                                                    ]),
                                                Forms\Components\Fieldset::make(trans('admin.shop.settings.payment_retry_admin'))
                                                    ->schema([
                                                        Forms\Components\Toggle::make('emails.notifications.payment_retry_admin.enabled')
                                                            ->label(trans('admin.shop.settings.enable_notifications'))
                                                            ->reactive(),
                                                        Forms\Components\TagsInput::make('emails.notifications.payment_retry_admin.recipients')
                                                            ->label(trans('admin.email'))
                                                            ->placeholder('admin@example.com')
                                                            ->visible(fn($get) => $get('emails.notifications.payment_retry_admin.enabled')),
                                                    ]),
                                            ]),
                                    ]),
                            ]),

                        // 6. ADVANCED
                        Tabs\Tab::make(trans('admin.settings.advanced'))
                            ->label(trans('admin.settings.advanced'))
                            ->icon('heroicon-o-command-line')
                            ->schema([
                                Forms\Components\Section::make(trans('admin.shop.settings.page_setup'))
                                    ->description(trans('admin.shop.settings.page_setup_desc'))
                                    ->schema([
                                        Forms\Components\Select::make('advanced.cart_page_id')
                                            ->label(trans('admin.shop.settings.cart_page'))
                                            ->options(\Z3d0X\FilamentFabricator\Models\Page::pluck('title', 'id'))
                                            ->searchable(),
                                        Forms\Components\Select::make('advanced.checkout_page_id')
                                            ->label(trans('admin.shop.settings.checkout_page'))
                                            ->options(\Z3d0X\FilamentFabricator\Models\Page::pluck('title', 'id'))
                                            ->searchable(),
                                        Forms\Components\Select::make('advanced.account_page_id')
                                            ->label(trans('admin.shop.settings.account_page'))
                                            ->options(\Z3d0X\FilamentFabricator\Models\Page::pluck('title', 'id'))
                                            ->searchable(),
                                    ]),

                                Forms\Components\Tabs::make(trans('admin.settings.advanced'))
                                    ->label(trans('admin.settings.advanced'))
                                    ->tabs([
                                        Tabs\Tab::make(trans('admin.webhooks.label'))
                                            ->label(trans('admin.webhooks.label'))
                                            ->icon('heroicon-o-rss')
                                            ->visible(fn() => WebhookResource::canViewAny())
                                            ->schema([
                                                Forms\Components\Section::make(trans('admin.webhooks.label'))
                                                    ->schema([
                                                        Forms\Components\Toggle::make('webhook.enabled')
                                                            ->label(trans('admin.webhooks.enabled')),
                                                        Forms\Components\TextInput::make('webhook.log_retention_days')
                                                            ->label(trans('admin.webhooks.log_retention_days'))
                                                            ->numeric()
                                                            ->default(30),
                                                    ]),
                                                Forms\Components\Section::make(trans('admin.webhooks.documentation'))
                                                    ->schema([
                                                        Forms\Components\Placeholder::make('webhook_docs')
                                                            ->label(trans('admin.webhooks.documentation'))
                                                            ->content(new \Illuminate\Support\HtmlString('
                                                                <a href="/docs/webhook" target="_blank" class="text-primary-600 font-bold underline hover:text-primary-500">
                                                                    ' . trans('admin.webhooks.view_documentation') . '
                                                                </a>
                                                            ')),
                                                    ]),
                                                Livewire::make(\App\Livewire\Settings\WebhookManager::class)
                                                    ->key('webhook-manager')
                                                    ->lazy(),
                                            ]),

                                        Tabs\Tab::make(trans('admin.webhooks.log_label'))
                                            ->label(trans('admin.webhooks.log_label'))
                                            ->icon('heroicon-o-list-bullet')
                                            ->visible(fn() => WebhookLogResource::canViewAny())
                                            ->schema([
                                                Livewire::make(\App\Livewire\Settings\WebhookLogViewer::class)
                                                    ->key('webhook-log-viewer')
                                                    ->lazy(),
                                            ]),

                                        Tabs\Tab::make(trans('admin.api.label'))
                                            ->label(trans('admin.api.label'))
                                            ->icon('heroicon-o-key')
                                            ->visible(fn() => \Illuminate\Support\Facades\Auth::user()?->can('view_api_settings') || \Illuminate\Support\Facades\Auth::user()?->is_admin) // Fallback check
                                            ->schema([
                                                Forms\Components\Section::make(trans('admin.api.settings'))
                                                    ->schema([
                                                        Forms\Components\Placeholder::make('api_docs')
                                                            ->label(trans('admin.api.documentation'))
                                                            ->content(new \Illuminate\Support\HtmlString('
                                                                <a href="/docs/api" target="_blank" class="text-primary-600 font-bold underline hover:text-primary-500">
                                                                    ' . trans('admin.api.view_documentation') . '
                                                                </a>
                                                            ')),
                                                        Forms\Components\Grid::make(2)
                                                            ->schema([
                                                                Forms\Components\Toggle::make('api.enabled')
                                                                    ->label(trans('admin.api.enabled')),
                                                                Forms\Components\TextInput::make('api.idempotency_ttl')
                                                                    ->label(trans('admin.api.idempotency_ttl'))
                                                                    ->numeric()
                                                                    ->default(86400),
                                                                Forms\Components\TextInput::make('api.hmac_secret')
                                                                    ->label(trans('admin.webhooks.secret'))
                                                                    ->password()
                                                                    ->revealable(),
                                                            ]),
                                                    ]),
                                            ]),

                                        Tabs\Tab::make(trans('admin.logs.label'))
                                            ->label(trans('admin.logs.label'))
                                            ->icon('heroicon-o-document-text')
                                            ->schema([
                                                Livewire::make(\App\Livewire\Settings\SystemLogViewer::class)
                                                    ->key('system-log-viewer')
                                                    ->lazy(),
                                            ]),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label(trans('admin.save'))
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            // Ensure decimal_places is cast to int
            if (isset($data['general']['decimal_places'])) {
                $data['general']['decimal_places'] = (int) $data['general']['decimal_places'];
            }

            $service = app(\App\Ecommerce\Settings\Contracts\ShopSettingServiceInterface::class);
            $service->updateAllSettings($data);

            Notification::make()
                ->title(trans('admin.settings.setting.settings_saved'))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
