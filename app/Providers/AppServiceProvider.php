<?php

namespace App\Providers;

use App\Services\Payment\IOrderPayment;
use App\Services\Payment\KapitalBank\CreateOrderService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(IOrderPayment::class, CreateOrderService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
