<?php

namespace App\Providers;

use App\Repositories\Eloquent\ShopRegistrationRepository;
use App\Repositories\Interfaces\ShopRegistrationRepositoryInterface;
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
            \App\Repositories\Interfaces\UserRepositoryInterface::class,
            \App\Repositories\Eloquent\UserRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\ShopRepositoryInterface::class,
            \App\Repositories\Eloquent\ShopRepository::class
        );

        $this->app->bind(
            ShopRegistrationRepositoryInterface::class,
            ShopRegistrationRepository::class
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
