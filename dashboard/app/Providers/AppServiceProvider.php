<?php

namespace App\Providers;

use App\Ecommerce\Agent\Contracts\AgentDashboardRepositoryInterface;
use App\Ecommerce\Agent\Contracts\AgentDashboardServiceInterface;
use App\Ecommerce\Agent\Repositories\EloquentAgentDashboardRepository;
use App\Ecommerce\Agent\Services\AgentDashboardService;
use App\Ecommerce\Location\Services\Location\LocationManager;
use App\Listeners\LogSentMessage;
use App\Settings\MailSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Model::unguard();

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
