<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FormController;
use App\Http\Controllers\JWTController;

use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\ReferralCodeMiddleware;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::get('/hello', function () {
    return response('hello world');
});

Route::get('/generate-token', [JWTController::class, 'generateToken']);


Route::middleware(ReferralCodeMiddleware::class)->group(function () {

    Route::get('/form', [FormController::class, 'requestForm']);
    Route::post('/form/submit',[FormController::class, 'submitForm']);
});


Route::middleware(JwtMiddleware::class)->group(function () {

    




    Route::get('/secure-endpoint', function () {
        return response()->json(['message' => 'Access granted']);
    });

});