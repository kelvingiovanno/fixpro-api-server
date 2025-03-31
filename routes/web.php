<?php 


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrCodePageController;
use App\Http\Controllers\UserFormPageController;
use App\Http\Controllers\WebAuthPageController;

use App\Http\Middleware\WebAuthMiddleware;

// Homepage
Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/', [WebAuthPageController::class, 'index'])->name('form');  
    Route::post('/login', [WebAuthPageController::class, 'login'])->name('login');  
    Route::post('/logout', [WebAuthPageController::class, 'logout'])->name('logout'); 
});

// Protected Routes (Require Authentication)
Route::middleware(WebAuthMiddleware::class)->group(function () {
    
    // User Settings Routes
    Route::prefix('user-form')->name('user-setting.')->group(function () {
        Route::get('/', [UserFormPageController::class, 'index']);
        Route::post('/submit', [UserFormPageController::class, 'handleSubmit'])->name('submit');
    });

    // QR Code Routes
    Route::prefix('qrcode')->name('qrcode.')->group(function () {
        Route::get('/', [QrCodePageController::class, 'index']);
        Route::get('/show', [QrCodePageController::class, 'showQrCode'])->name('show');
        Route::get('/refresh', [QrCodePageController::class, 'refreshQrCode'])->name('refresh');
    });

});