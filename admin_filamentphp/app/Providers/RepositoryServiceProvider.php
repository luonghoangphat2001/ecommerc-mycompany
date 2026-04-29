<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Ecommerce\Loyalty\Contracts\LoyaltyRepositoryInterface::class,
            \App\Ecommerce\Loyalty\Repositories\EloquentLoyaltyRepository::class
        );

        $this->app->bind(

            \App\Ecommerce\Product\Contracts\ProductRepositoryInterface::class,
            \App\Ecommerce\Product\Repositories\EloquentProductRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Product\Contracts\BrandRepositoryInterface::class,
            \App\Ecommerce\Product\Repositories\EloquentBrandRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Product\Contracts\ProductCategoryRepositoryInterface::class,
            \App\Ecommerce\Product\Repositories\EloquentProductCategoryRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Product\Contracts\TaxClassRepositoryInterface::class,
            \App\Ecommerce\Product\Repositories\EloquentTaxClassRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Product\Contracts\TaxRateRepositoryInterface::class,
            \App\Ecommerce\Product\Repositories\EloquentTaxRateRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Shipping\Contracts\ShippingZoneRepositoryInterface::class,
            \App\Ecommerce\Shipping\Repositories\EloquentShippingZoneRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Shipping\Contracts\ShippingMethodRepositoryInterface::class,
            \App\Ecommerce\Shipping\Repositories\EloquentShippingMethodRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Order\Contracts\OrderRepositoryInterface::class,
            \App\Ecommerce\Order\Repositories\EloquentOrderRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Customer\Contracts\CustomerRepositoryInterface::class,
            \App\Ecommerce\Customer\Repositories\EloquentCustomerRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Post\Contracts\PostRepositoryInterface::class,
            \App\Ecommerce\Post\Repositories\EloquentPostRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Page\Contracts\PageRepositoryInterface::class,
            \App\Ecommerce\Page\Repositories\EloquentPageRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Menu\Contracts\MenuRepositoryInterface::class,
            \App\Ecommerce\Menu\Repositories\EloquentMenuRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\User\Contracts\UserRepositoryInterface::class,
            \App\Ecommerce\User\Repositories\EloquentUserRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Payment\Contracts\PaymentRepositoryInterface::class,
            \App\Ecommerce\Payment\Repositories\EloquentPaymentRepository::class
        );


        $this->app->bind(
            \App\Ecommerce\Order\Contracts\OrderRefundRepositoryInterface::class,
            \App\Ecommerce\Order\Repositories\EloquentOrderRefundRepository::class
        );


        $this->app->bind(
            \App\Ecommerce\Analytics\Contracts\WebhookLogRepositoryInterface::class,
            \App\Ecommerce\Analytics\Repositories\EloquentWebhookLogRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Analytics\Contracts\AnalyticsRepositoryInterface::class,
            \App\Ecommerce\Analytics\Repositories\EloquentAnalyticsRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Analytics\Contracts\WebhookAnalyticsRepositoryInterface::class,
            \App\Ecommerce\Analytics\Repositories\EloquentWebhookAnalyticsRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Post\Contracts\PostCategoryRepositoryInterface::class,
            \App\Ecommerce\Post\Repositories\EloquentPostCategoryRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Address\Contracts\AddressRepositoryInterface::class,
            \App\Ecommerce\Address\Repositories\EloquentAddressRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Location\Contracts\CountryRepositoryInterface::class,
            \App\Ecommerce\Location\Repositories\EloquentCountryRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Coupon\Contracts\CouponRepositoryInterface::class,
            \App\Ecommerce\Coupon\Repositories\EloquentCouponRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Inventory\Contracts\InventoryRepositoryInterface::class,
            \App\Ecommerce\Inventory\Repositories\EloquentInventoryRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Upsell\Contracts\UpsellRepositoryInterface::class,
            \App\Ecommerce\Upsell\Repositories\EloquentUpsellRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\CrossSell\Contracts\CrossSellRepositoryInterface::class,
            \App\Ecommerce\CrossSell\Repositories\EloquentCrossSellRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Combo\Contracts\ComboRepositoryInterface::class,
            \App\Ecommerce\Combo\Repositories\EloquentComboRepository::class
        );

        $this->app->bind(
            \App\Ecommerce\Analytics\Contracts\WebhookRepositoryInterface::class,
            \App\Ecommerce\Analytics\Repositories\EloquentWebhookRepository::class
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
