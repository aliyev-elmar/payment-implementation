<?php

use App\Http\Controllers\Student\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['middleware' => ['auth:sanctum', 'jwt_secure']], function () {
    Route::post('/buy-course', [PaymentController::class, 'buyCourse']);
    Route::get('/check/{id}/order', [PaymentController::class, 'checkOrderStatus']);
});
