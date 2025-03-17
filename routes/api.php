<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\JoinAreaController;
use App\Http\Controllers\UserSettingController;
use App\Http\Controllers\QrCodeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::get('/hello', function () {
    return response('hello world');
});

Route::get('/join', [JoinAreaController::class, 'index']);


Route::post('/user-setting-submit', [UserSettingController::class, 'handleSubmit'])->name('toggle.submit');
Route::get('/qrcode', [QrCodeController::class, 'index'])->name('qrcode.index');
Route::get('/qrcode/show', [QrCodeController::class, 'showQrCode'])->name('qrcode.show');
Route::get('/qrcode/refresh', [QrCodeController::class, 'refreshQrCode'])->name('qrcode.refresh');