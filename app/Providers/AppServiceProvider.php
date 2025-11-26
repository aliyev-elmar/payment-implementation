<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Contracts\ICreateOrderService::class,
            \App\Services\Payment\CreateOrderService::class,
        );

        $this->app->bind(
            \App\Contracts\ILogger::class,
            \App\Services\LogService::class,
        );

        $this->app->bind(
            \App\Repositories\Payment\PaymentRepository::class,
            \App\Repositories\Payment\KapitalBankRepository::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
