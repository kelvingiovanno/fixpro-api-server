<?php

use App\Http\Controllers\QrCodePageController;
use App\Http\Controllers\WebAuthPageController;
use App\Http\Controllers\GoogleCalenderController;
use App\Http\Controllers\SettingController;

use App\Http\Middleware\WebAuthMiddleware;
use Barryvdh\DomPDF\Facade\Pdf;
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

    Route::prefix('/settings')->group(function () {
        Route::get('/', [SettingController::class, 'index']);
        Route::get('/area', [SettingController::class, 'area'])->name('settings.area');
        Route::get('/member', [SettingController::class, 'member'])->name('settings.member');
        Route::get('/issue', [SettingController::class, 'issue'])->name('settings.issue');
        Route::get('/storage', [SettingController::class, 'storage'])->name('settings.storage');
        Route::get('/calender', [SettingController::class, 'calender'])->name('settings.calender');
        
        Route::post('/submit-area', [SettingController::class, 'submitSettingArea'])->name('settings.area.submit');
        Route::post('/submit-member', [SettingController::class, 'submitSettingMember'])->name('settings.member.submit');
        Route::post('/submit-issue', [SettingController::class, 'submitSettingIssue'])->name('settings.issue.submit');
        Route::post('/submit-storage', [SettingController::class, 'submitSettingStorage'])->name('settings.storage.submit');
        Route::post('/submit-calender', [SettingController::class, 'submitSettingCalender'])->name('settings.calendar.submit');
    });
});


Route::get('/pdf-layout/service-request-form', function () {

    $data = [
        ['name' => 'NAMA A', 'title' => 'TITLE A'],
        ['name' => 'NAMA B', 'title' => 'TITLE B'],
        ['name' => 'NAMA C', 'title' => 'TITLE C'],
        ['name' => 'NAMA D', 'title' => 'TITLE D'],
        ['name' => 'NAMA E', 'title' => 'TITLE E'],
        ['name' => 'NAMA F', 'title' => 'TITLE F'],
    ];

    $chunks = array_chunk($data, ceil(count($data) / 2));
    $leftTable = $chunks[0];
    $rightTable = $chunks[1];

    $pdf = Pdf::loadView('pdf.service_request_form', compact('leftTable', 'rightTable'))->setPaper('a4', 'portrait');;
    return $pdf->stream('report.pdf');
});

Route::get('/pdf-layout/ticket-report', function () {

    $data = [
        ['name' => 'NAMA A', 'title' => 'TITLE A'],
        ['name' => 'NAMA B', 'title' => 'TITLE B'],
        ['name' => 'NAMA C', 'title' => 'TITLE C'],
        ['name' => 'NAMA D', 'title' => 'TITLE D'],
        ['name' => 'NAMA E', 'title' => 'TITLE E'],
        ['name' => 'NAMA F', 'title' => 'TITLE F'],
    ];

    $chunks = array_chunk($data, ceil(count($data) / 2));
    $leftTable = $chunks[0];
    $rightTable = $chunks[1];

    $pdf = Pdf::loadView('pdf.ticket_report', compact('leftTable', 'rightTable'))->setPaper('a4', 'portrait');;
    return $pdf->stream('report.pdf');
});

Route::get('/pdf-layout/work-order', function () {

    $members = [
        ['name' => 'NAMA A', 'title' => 'TITLE A'],
        ['name' => 'NAMA B', 'title' => 'TITLE B'],
        ['name' => 'NAMA C', 'title' => 'TITLE C'],
        ['name' => 'NAMA D', 'title' => 'TITLE D'],
        ['name' => 'NAMA E', 'title' => 'TITLE E'],
        ['name' => 'NAMA F', 'title' => 'TITLE F'],
    ];

    $chunks = array_chunk($members, ceil(count($members) / 2));
    $leftTable = $chunks[0];
    $rightTable = $chunks[1];

    $data = [
        'leftTable' => $leftTable,
        'rightTable' => $rightTable,
    ];

    $pdf = Pdf::loadView('pdf.work_order', $data)->setPaper('a4', 'portrait');
    return $pdf->stream('report.pdf');
});

Route::get('/pdf-layout/periodic-report', function () {

    $pdf = Pdf::loadView('pdf.periodic_report')->setPaper('a4', 'portrait');
    return $pdf->stream('report.pdf');
});