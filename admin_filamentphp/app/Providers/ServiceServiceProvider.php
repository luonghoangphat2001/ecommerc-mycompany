<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Contracts\Services\ProductServiceInterface::class,
            \App\Services\ProductService::class
        );

        $this->app->bind(
            \App\Contracts\Services\OrderServiceInterface::class,
            \App\Services\OrderService::class
        );

        $this->app->bind(
            \App\Contracts\Services\SettingServiceInterface::class,
            \App\Services\SettingService::class
        );

        $this->app->bind(
            \App\Contracts\Services\ProductCategoryServiceInterface::class,
            \App\Services\ProductCategoryService::class
        );

        $this->app->bind(
            \App\Contracts\Services\CustomerServiceInterface::class,
            \App\Services\CustomerService::class
        );

        $this->app->bind(
            \App\Contracts\Services\OrderExportServiceInterface::class,
            \App\Services\OrderExportService::class
        );

        $this->app->bind(
            \App\Contracts\Services\PostServiceInterface::class,
            \App\Services\PostService::class
        );
        $this->app->bind(
            \App\Contracts\Services\PostCategoryServiceInterface::class,
            \App\Services\PostCategoryService::class
        );
        $this->app->bind(
            \App\Contracts\Services\PageServiceInterface::class,
            \App\Services\PageService::class
        );
        $this->app->singleton(\App\Services\MenuService::class);
        $this->app->bind(
            \App\Contracts\Services\BrandServiceInterface::class,
            \App\Services\BrandService::class
        );
        $this->app->singleton(\App\Services\AuthService::class);
        $this->app->bind(
            \App\Contracts\Services\AnalyticsServiceInterface::class,
            \App\Services\AnalyticsService::class
        );
        $this->app->bind(
            \App\Contracts\Services\PanelServiceInterface::class,
            \App\Services\PanelService::class
        );
        $this->app->bind(
            \App\Contracts\Services\WebhookAnalyticsServiceInterface::class,
            \App\Services\WebhookAnalyticsService::class
        );
        $this->app->bind(
            \App\Contracts\Services\CurrencyServiceInterface::class,
            \App\Services\CurrencyService::class
        );
        $this->app->bind(
            \App\Contracts\Services\ShopSettingServiceInterface::class,
            \App\Services\ShopSettingService::class
        );
        $this->app->bind(
            \App\Contracts\Services\LocationServiceInterface::class,
            \App\Services\LocationService::class
        );
        $this->app->bind(
            \App\Contracts\Services\ShippingServiceInterface::class,
            \App\Services\ShippingService::class
        );
        $this->app->bind(
            \App\Contracts\Services\TaxServiceInterface::class,
            \App\Services\TaxService::class
        );
        $this->app->bind(
            \App\Contracts\Services\StorefrontSettingsServiceInterface::class,
            \App\Services\StorefrontSettingsService::class
        );
        $this->app->bind(
            \App\Services\Address\Contracts\AddressServiceInterface::class,
            \App\Services\Address\AddressService::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
