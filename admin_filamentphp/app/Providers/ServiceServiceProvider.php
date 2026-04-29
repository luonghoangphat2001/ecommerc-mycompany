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
        $this->app->bind(
            \App\Ecommerce\Menu\Contracts\MenuServiceInterface::class,
            \App\Ecommerce\Menu\Services\MenuService::class
        );
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
        $this->app->bind(
            \App\Ecommerce\Loyalty\Contracts\LoyaltyServiceInterface::class,
            \App\Ecommerce\Loyalty\Services\LoyaltyService::class
        );
        $this->app->bind(
            \App\Ecommerce\Inventory\Contracts\InventoryServiceInterface::class,
            \App\Ecommerce\Inventory\Services\InventoryService::class
        );
        $this->app->bind(
            \App\Ecommerce\Payment\Contracts\PaymentServiceInterface::class,
            \App\Ecommerce\Payment\Services\PaymentService::class
        );
        $this->app->bind(
            \App\Ecommerce\Cart\Contracts\CartServiceInterface::class,
            \App\Ecommerce\Cart\Services\CartService::class
        );
        $this->app->bind(
            \App\Ecommerce\Upsell\Contracts\UpsellServiceInterface::class,
            \App\Ecommerce\Upsell\Services\UpsellService::class
        );
        $this->app->bind(
            \App\Ecommerce\CrossSell\Contracts\CrossSellServiceInterface::class,
            \App\Ecommerce\CrossSell\Services\CrossSellService::class
        );
        $this->app->bind(
            \App\Ecommerce\Combo\Contracts\ComboServiceInterface::class,
            \App\Ecommerce\Combo\Services\ComboService::class
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
