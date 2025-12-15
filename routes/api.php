<?php

use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('orders')->group(function () {
    Route::post('/', [OrderController::class, 'store'])->middleware('throttle:10,1');
    Route::post('/{id}/set-src-token', [OrderController::class, 'setSourceTokenById'])->middleware('throttle:10,1');
    Route::get('/{id}/simple-status', [OrderController::class, 'getSimpleStatusById'])->middleware('throttle:30,1');
});
