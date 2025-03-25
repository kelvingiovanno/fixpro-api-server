<?php 


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\UserFormController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\AuthMiddleware;

// Homepage
Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/', [AuthController::class, 'index'])->name('form');  
    Route::post('/login', [AuthController::class, 'login'])->name('login');  
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout'); 
});

// Protected Routes (Require Authentication)
Route::middleware(AuthMiddleware::class)->group(function () {
    
    // User Settings Routes
    Route::prefix('user-form')->name('user-setting.')->group(function () {
        Route::get('/', [UserFormController::class, 'index']);
        Route::post('/submit', [UserFormController::class, 'handleSubmit'])->name('submit');
    });

    // QR Code Routes
    Route::prefix('qrcode')->name('qrcode.')->group(function () {
        Route::get('/', [QrCodeController::class, 'index']);
        Route::get('/show', [QrCodeController::class, 'showQrCode'])->name('show');
        Route::get('/refresh', [QrCodeController::class, 'refreshQrCode'])->name('refresh');
    });

});