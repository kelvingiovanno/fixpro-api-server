<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\JoinAreaController;
use App\Http\Controllers\UserSettingController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::get('/hello', function () {
    return response('hello world');
});

Route::get('/join', [JoinAreaController::class, 'index']);