<?php

namespace App\Providers;

use App\Ecommerce\Agent\Contracts\AgentDashboardRepositoryInterface;
use App\Ecommerce\Agent\Contracts\AgentDashboardServiceInterface;
use App\Ecommerce\Agent\Repositories\EloquentAgentDashboardRepository;
use App\Ecommerce\Agent\Services\AgentDashboardService;
use App\Ecommerce\Audit\Contracts\FinancialAuditServiceInterface;
use App\Ecommerce\Audit\Services\FinancialAuditService;
use App\Ecommerce\Location\Services\Location\LocationManager;
use App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface;
use App\Ecommerce\Settings\Services\FeaturePolicyService;
use App\Listeners\LogSentMessage;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\OrderMeta;
use App\Models\OrderShipping;
use App\Models\OrderTax;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\User;
use App\Observers\PriceChangeAuditObserver;
use App\Settings\MailSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            AgentDashboardRepositoryInterface::class,
            EloquentAgentDashboardRepository::class,
        );
        $this->app->bind(
            AgentDashboardServiceInterface::class,
            AgentDashboardService::class,
        );

        $this->app->singleton(LocationManager::class, function ($app) {
            return new LocationManager($app);
        });

        $this->app->singleton(
            FinancialAuditServiceInterface::class,
            FinancialAuditService::class
        );

        $this->app->singleton(
            FeaturePolicyServiceInterface::class,
            FeaturePolicyService::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Passport::tokensCan([
            'access' => 'Access the storefront API',
        ]);
        Passport::setDefaultScope(['access']);
        Passport::personalAccessTokensExpireIn(now()->addMinutes(15));
        Passport::refreshTokensExpireIn(now()->addDays(7));

        Model::unguard();

        Product::observe(PriceChangeAuditObserver::class);
        ProductVariant::observe(PriceChangeAuditObserver::class);

        Relation::morphMap([
            'product' => Product::class,
            'post' => Post::class,
            'user' => User::class,
            'customer' => Customer::class,
            'brand' => Brand::class,
            'order' => Order::class,
            'order_address' => OrderAddress::class,
            'order_shipping' => OrderShipping::class,
            'order_tax' => OrderTax::class,
            'order_meta' => OrderMeta::class,
            'order_item' => OrderItem::class,
            'page' => Page::class,
            'category' => Category::class,
            'shop_category' => ProductCategory::class,
            'comment' => Comment::class,
        ]);

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Dynamic Mail Configuration
        try {
            $mailSettings = app(MailSettings::class);

            if ($mailSettings->email_host) {
                config([
                    'mail.default' => 'smtp', // Ép buộc sử dụng smtp khi đã có host từ Admin
                    'mail.mailers.smtp.host' => $mailSettings->email_host,
                    'mail.mailers.smtp.port' => $mailSettings->email_port,
                    'mail.mailers.smtp.encryption' => $mailSettings->email_encryption,
                    'mail.mailers.smtp.username' => $mailSettings->email_username,
                    'mail.mailers.smtp.password' => $mailSettings->email_password,
                    'mail.from.address' => $mailSettings->email_from_address,
                    'mail.from.name' => $mailSettings->email_from_name,
                ]);
            }
        } catch (\Exception $e) {
            // Log or ignore if database is not ready
        }

        // Register Mail Log Listener
        Event::listen(
            MessageSent::class,
            LogSentMessage::class
        );
    }
}
