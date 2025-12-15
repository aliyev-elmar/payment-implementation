<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
        $exceptions->render(function (\App\Exceptions\OrderNotFoundException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->statusCode);
        });

        $exceptions->render(function (\App\Exceptions\InvalidRequestException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->statusCode);
        });

        $exceptions->render(function (\App\Exceptions\InvalidTokenException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->statusCode);
        });

        $exceptions->render(function (\App\Exceptions\InvalidOrderStateException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->statusCode);
        });
    })->create();
