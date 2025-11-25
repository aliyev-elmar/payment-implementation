<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\App\Exceptions\Payment\CreateOrderException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->statusCode);
        });

        $exceptions->render(function (\App\Exceptions\Payment\PaymentIsRequiredException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_PAYMENT_REQUIRED);
        });
    })->create();
