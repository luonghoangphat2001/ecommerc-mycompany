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
            \App\Ecommerce\Product\Contracts\ProductServiceInterface::class,
            \App\Ecommerce\Product\Services\ProductService::class
        );

        $this->app->bind(
            \App\Ecommerce\Order\Contracts\OrderServiceInterface::class,
            \App\Ecommerce\Order\Services\OrderService::class
        );

        $this->app->bind(
            \App\Ecommerce\Settings\Contracts\SettingServiceInterface::class,
            \App\Ecommerce\Settings\Services\SettingService::class
        );

        $this->app->bind(
            \App\Ecommerce\Product\Contracts\ProductCategoryServiceInterface::class,
            \App\Ecommerce\Product\Services\ProductCategoryService::class
        );

        $this->app->bind(
            \App\Ecommerce\Customer\Contracts\CustomerServiceInterface::class,
            \App\Ecommerce\Customer\Services\CustomerService::class
        );

        $this->app->bind(
            \App\Ecommerce\Order\Contracts\OrderExportServiceInterface::class,
            \App\Ecommerce\Order\Services\OrderExportService::class
        );

        $this->app->bind(
            \App\Ecommerce\Post\Contracts\PostServiceInterface::class,
            \App\Ecommerce\Post\Services\PostService::class
        );
        $this->app->bind(
            \App\Ecommerce\Post\Contracts\PostCategoryServiceInterface::class,
            \App\Ecommerce\Post\Services\PostCategoryService::class
        );
        $this->app->bind(
            \App\Ecommerce\Page\Contracts\PageServiceInterface::class,
            \App\Ecommerce\Page\Services\PageService::class
        );
        $this->app->singleton(\App\Ecommerce\Menu\Services\MenuService::class);
        $this->app->bind(
            \App\Ecommerce\Product\Contracts\BrandServiceInterface::class,
            \App\Ecommerce\Product\Services\BrandService::class
        );
        $this->app->singleton(\App\Ecommerce\Core\Services\AuthService::class);
        $this->app->bind(
            \App\Ecommerce\Analytics\Contracts\AnalyticsServiceInterface::class,
            \App\Ecommerce\Analytics\Services\AnalyticsService::class
        );
        $this->app->bind(
            \App\Ecommerce\Core\Contracts\PanelServiceInterface::class,
            \App\Ecommerce\Core\Services\PanelService::class
        );
        $this->app->bind(
            \App\Ecommerce\Analytics\Contracts\WebhookAnalyticsServiceInterface::class,
            \App\Ecommerce\Analytics\Services\WebhookAnalyticsService::class
        );
        $this->app->bind(
            \App\Ecommerce\Core\Contracts\CurrencyServiceInterface::class,
            \App\Ecommerce\Core\Services\CurrencyService::class
        );
        $this->app->bind(
            \App\Ecommerce\Settings\Contracts\ShopSettingServiceInterface::class,
            \App\Ecommerce\Settings\Services\ShopSettingService::class
        );
        $this->app->bind(
            \App\Ecommerce\Location\Contracts\LocationServiceInterface::class,
            \App\Ecommerce\Location\Services\LocationService::class
        );
        $this->app->bind(
            \App\Ecommerce\Shipping\Contracts\ShippingServiceInterface::class,
            \App\Ecommerce\Shipping\Services\ShippingService::class
        );
        $this->app->bind(
            \App\Ecommerce\Product\Contracts\TaxServiceInterface::class,
            \App\Ecommerce\Product\Services\TaxService::class
        );
        $this->app->bind(
            \App\Ecommerce\Settings\Contracts\StorefrontSettingsServiceInterface::class,
            \App\Ecommerce\Settings\Services\StorefrontSettingsService::class
        );
        $this->app->bind(
            \App\Ecommerce\Address\Services\Contracts\AddressServiceInterface::class,
            \App\Ecommerce\Address\Services\AddressService::class
        );
        $this->app->bind(
            \App\Ecommerce\Customer\Contracts\CustomerResolverServiceInterface::class,
            \App\Ecommerce\Customer\Services\CustomerResolverService::class
        );
        $this->app->bind(
            \App\Ecommerce\Address\Contracts\AddressBookServiceInterface::class,
            \App\Ecommerce\Address\Services\AddressBookService::class
        );
        $this->app->bind(
            \App\Ecommerce\Coupon\Contracts\CouponServiceInterface::class,
            \App\Ecommerce\Coupon\Services\CouponService::class
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
