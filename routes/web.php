<?php

use App\Http\Controllers\QrCodePageController;
use App\Http\Controllers\UserFormPageController;
use App\Http\Controllers\WebAuthPageController;
use App\Http\Controllers\GoogleCalenderController;
use App\Http\Controllers\SetupController;

use App\Http\Middleware\WebAuthMiddleware;

use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/', [WebAuthPageController::class, 'index'])->name('form');  
    Route::post('/login', [WebAuthPageController::class, 'login'])->name('login');  
    Route::post('/logout', [WebAuthPageController::class, 'logout'])->name('logout'); 
});

Route::middleware(WebAuthMiddleware::class)->group(function () {

    Route::get('/', [QrCodePageController::class, 'index']);

    Route::prefix('qrcode')->name('qrcode.')->group(function () {
        Route::get('/show', [QrCodePageController::class, 'showQrCode'])->name('show');
        Route::get('/refresh', [QrCodePageController::class, 'refreshQrCode'])->name('refresh');
    });

    Route::prefix('/google')->name('google.')->group(function () {
        Route::get('/auth', [GoogleCalenderController::class, 'auth'])->name('auth');
        Route::get('/callback', [GoogleCalenderController::class, 'callback'])->name('callback');
    });    

    Route::prefix('/setup')->group(function () {
        Route::get('/', [SetupController::class, 'index']);
        Route::post('/submit', [SetupController::class, 'submit'])->name('setup.submit');
    });
});
