<?php

namespace App\Providers;

use App\Contracts\{ICreateOrderService, ILogService, IPaymentRepository};
use App\Repositories\Payment\KapitalBankRepository;
use App\Services\LogService;
use App\Services\Payment\CreateOrderService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ICreateOrderService::class, CreateOrderService::class);
        $this->app->bind(ILogService::class, LogService::class);
        $this->app->bind(IPaymentRepository::class, KapitalBankRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
