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

Route::get('/pdf-layout/periodic-report', function () {

    $this_month_piechart_url = 'https://quickchart.io/chart?c=' . urlencode(json_encode([
        'type' => 'outlabeledPie',
        'data' => [
            'labels' => ['Engineering', 'Housekeeping', 'HSE', 'Security'],
            'datasets' => [[
                'backgroundColor' => ['#FF3784', '#36A2EB', '#4BC0C0', '#F77825', '#9966FF'],
                'data' => [22, 14, 12, 5],
            ]]
        ],
        'options' => [
            'plugins' => [
                'legend' => false,
                'outlabels' => [
                    'text' => '%l %p',
                    'color' => 'white',
                    'stretch' => 35,
                    'font' => [
                        'resizable' => true,
                        'minSize' => 14,
                        'maxSize' => 15
                    ]
                ]
            ]
        ]
    ]));


    $overall_piechart_url = 'https://quickchart.io/chart?c=' . urlencode(json_encode([
        'type' => 'outlabeledPie',
        'data' => [
            'labels' => ['Engineering', 'Housekeeping', 'HSE', 'Security'],
            'datasets' => [[
                'backgroundColor' => ['#FF3784', '#36A2EB', '#4BC0C0', '#F77825', '#9966FF'],
                'data' => [22, 14, 12, 5],
            ]]
        ],
        'options' => [
            'plugins' => [
                'legend' => false,
                'outlabels' => [
                    'text' => '%l %p',
                    'color' => 'white',
                    'stretch' => 35,
                    'font' => [
                        'resizable' => true,
                        'minSize' => 14,
                        'maxSize' => 15
                    ]
                ]
            ]
        ]
    ]));

    
    // Function to simplify doughnut chart creation
    function generateDoughnut($value, $total, $color, $percent) {
        return 'https://quickchart.io/chart?c=' . urlencode(json_encode([
            'type' => 'doughnut',
            'data' => [
                'datasets' => [[
                    'data' => [$value, $total - $value],
                    'backgroundColor' => [$color, '#e8e8e8'],
                    'borderWidth' => 0
                ]]
            ],
            'options' => [
                'plugins' => [
                    'legend' => false,
                    'doughnutlabel' => [
                        'labels' => [[
                            'text' => $percent,
                            'font' => ['size' => 60]
                        ]]
                    ],
                    'datalabels' => ['display' => false]
                ],
                'cutoutPercentage' => 70
            ]
        ]));
    }

    // Doughnut chart URLs
    $engineering = generateDoughnut(9, 22, '#4CAF50', '40.9%');
    $housekeeping = generateDoughnut(4, 14, '#2196F3', '28.5%');
    $hse = generateDoughnut(8, 12, '#FFC107', '66.6%');
    $security = generateDoughnut(2, 5, '#F44336', '40%');

    // Pass to view
    $charts = [
        'this_month_piechart' => $this_month_piechart_url,
        'overall_piechart' => $overall_piechart_url,
        'engineering_chart' => $engineering,
        'housekeeping_chart' => $housekeeping,
        'hse_chart' => $hse,
        'security_chart' => $security,
    ];

    $pdf = Pdf::loadView('pdf.periodic_report', $charts)->setPaper('a4', 'portrait');
    return $pdf->stream('report.pdf');
});
