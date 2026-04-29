<?php

namespace App\Filament\Pages\Settings;

use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Settings\GeneralSettings;
use App\Settings\ProductSettings;
use App\Settings\CheckoutSettings;
use App\Settings\EmailSettings;
use App\Settings\AdvancedSettings;
use App\Settings\ApiSettings;
use App\Settings\WebhookSettings;
use App\Settings\CouponSettings;
use Filament\Forms\Components\Tabs;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use App\Ecommerce\Settings\Contracts\ShopSettingServiceInterface;
use App\Filament\Pages\Settings\Tabs\GeneralTab;
use App\Filament\Pages\Settings\Tabs\ProductTab;
use App\Filament\Pages\Settings\Tabs\CheckoutTab;
use App\Filament\Pages\Settings\Tabs\ShippingTab;
use App\Filament\Pages\Settings\Tabs\CouponTab;
use App\Filament\Pages\Settings\Tabs\EmailTab;
use App\Filament\Pages\Settings\Tabs\AdvancedTab;
use Exception;

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
            'coupon' => app(CouponSettings::class)->toArray(),
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
                Tabs::make(trans('admin.shop.settings.label'))
                    ->label(trans('admin.shop.settings.label'))
                    ->tabs([
                        GeneralTab::make(),
                        ProductTab::make(),
                        CheckoutTab::make(),
                        ShippingTab::make(),
                        CouponTab::make(),
                        EmailTab::make(),
                        AdvancedTab::make(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('admin.save'))
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            if (isset($data['general']['decimal_places'])) {
                $data['general']['decimal_places'] = (int) $data['general']['decimal_places'];
            }

            $service = app(ShopSettingServiceInterface::class);
            $service->updateAllSettings($data);

            Notification::make()
                ->title(trans('admin.settings.setting.settings_saved'))
                ->success()
                ->send();
        } catch (Exception $e) {
            Notification::make()
                ->title('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
