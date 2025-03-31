<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\UserController;

use App\Http\Middleware\ApiAuthMiddleware;
use App\Http\Middleware\NonceMiddleware;
use App\Http\Middleware\ReferralCodeMiddleware;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::get('/hello', function () {
    return response('hello world');
});



Route::prefix('/entry')->group(function () {
    Route::get('/check', [FormController::class, 'check']);
    Route::get('/form', [FormController::class, 'requestForm'])->middleware(ReferralCodeMiddleware::class);
    Route::post('/form',[FormController::class, 'submitForm'])->middleware(NonceMiddleware::class);
});


Route::middleware(ApiAuthMiddleware::class)->group(function () {

    
    Route::prefix('user')->group(function () {

        Route::get('/', [UserController::class], 'index');
        Route::get('/{id}/is-accapeted', [UserController::class, 'isUserStatusAccepted']);
        
    });


    Route::get('/secure-endpoint', function () {
        return response()->json(['message' => 'Access granted']);
    });

});