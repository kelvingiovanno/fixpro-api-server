<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\UserSettingController;
use App\Services\EncryptionService;

Route::get('/', function () {return view('welcome');});

Route::get('/user-setting', [UserSettingController::class, 'index']);
Route::post('/user-setting-submit', [UserSettingController::class, 'handleSubmit'])->name('toggle.submit');
Route::get('/qrcode-join', [QrCodeController::class, 'index']);

Route::get('/encrypt', function () {

    $service = app(EncryptionService::class);

    $key = $service->generateKey();
    $encrypted = $service->encrypt('Hello, Laravel!');
    $decrypted = $service->decrypt($encrypted);

    return response()->json([
        'Generated Key' => $key,
        'Encrypted' => $encrypted,
        'Decrypted' => $decrypted
    ]);
});

Route::get('/qrcode', [QrCodeController::class, 'index'])->name('qrcode.index');
Route::get('/qrcode/show', [QrCodeController::class, 'showQrCode'])->name('qrcode.show');
Route::get('/qrcode/refresh', [QrCodeController::class, 'refreshQrCode'])->name('qrcode.refresh');