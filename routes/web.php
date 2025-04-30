<?php

use App\Http\Controllers\QrCodePageController;
use App\Http\Controllers\UserFormPageController;
use App\Http\Controllers\WebAuthPageController;
use App\Http\Controllers\GoogleCalenderController;

use App\Http\Middleware\WebAuthMiddleware;

use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/', [WebAuthPageController::class, 'index'])->name('form');  
    Route::post('/login', [WebAuthPageController::class, 'login'])->name('login');  
    Route::post('/logout', [WebAuthPageController::class, 'logout'])->name('logout'); 
});

Route::middleware(WebAuthMiddleware::class)->group(function () {

    Route::get('/', function () {
        return view('welcome');
    });

    Route::prefix('user-form')->name('user-setting.')->group(function () {
        Route::get('/', [UserFormPageController::class, 'index']);
        Route::post('/submit', [UserFormPageController::class, 'handleSubmit'])->name('submit');
    });

    Route::prefix('qrcode')->name('qrcode.')->group(function () {
        Route::get('/', [QrCodePageController::class, 'index']);
        Route::get('/show', [QrCodePageController::class, 'showQrCode'])->name('show');
        Route::get('/refresh', [QrCodePageController::class, 'refreshQrCode'])->name('refresh');
    });

    Route::prefix('/google')->group(function () {
        Route::get('/auth', [GoogleCalenderController::class, 'auth']);
        Route::get('/callback', [GoogleCalenderController::class, 'callback']);
    });

});