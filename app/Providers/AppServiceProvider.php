<?php

namespace App\Providers;

use App\Repositories\Eloquent\ShopRegistrationRepository;
use App\Repositories\Eloquent\ShopRepository;
use App\Repositories\Eloquent\SubscriptionRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Interfaces\ShopRegistrationRepositoryInterface;
use App\Repositories\Interfaces\ShopRepositoryInterface;
use App\Repositories\Interfaces\SubscriptionRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        $this->app->bind(
            ShopRepositoryInterface::class,
            ShopRepository::class
        );

        $this->app->bind(
            ShopRegistrationRepositoryInterface::class,
            ShopRegistrationRepository::class
        );

        $this->app->bind(
            SubscriptionRepositoryInterface::class,
            SubscriptionRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
