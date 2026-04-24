<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Blog Policies
        \App\Models\Post::class => \App\Policies\PostPolicy::class,
        \App\Models\PostCategory::class => \App\Policies\PostCategoryPolicy::class,

        // Shop Policies
        \App\Models\Order::class => \App\Policies\OrderPolicy::class,
        \App\Models\Brand::class => \App\Policies\BrandPolicy::class,
        \App\Models\ProductCategory::class => \App\Policies\ProductCategoryPolicy::class,
        \App\Models\Customer::class => \App\Policies\CustomerPolicy::class,
        \App\Models\Product::class => \App\Policies\ProductPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        \Illuminate\Support\Facades\Gate::before(fn ($user, $ability) => $user->hasRole('super_admin') ? true : null);
    }
}
