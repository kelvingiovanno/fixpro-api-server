<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\UserSettingController;

Route::get('/', function () {return view('welcome');});

Route::get('/user-setting', [UserSettingController::class, 'index']);
Route::get('/qrcode-join', [QrCodeController::class, 'index']);