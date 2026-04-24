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
            \App\Contracts\Repositories\ProductRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentProductRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\BrandRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentBrandRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\ProductCategoryRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentProductCategoryRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\TaxClassRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentTaxClassRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\TaxRateRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentTaxRateRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\ShippingZoneRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentShippingZoneRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\ShippingMethodRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentShippingMethodRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\OrderRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentOrderRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\SettingRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentSettingRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\CustomerRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentCustomerRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\PostRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentPostRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\PageRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentPageRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\MenuRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentMenuRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\UserRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentUserRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\PaymentRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentPaymentRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\WebhookRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentWebhookRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\WebhookLogRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentWebhookLogRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\AnalyticsRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentAnalyticsRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\WebhookAnalyticsRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentWebhookAnalyticsRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\PostCategoryRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentPostCategoryRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\CountryRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentCountryRepository::class
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
