<?php

use App\Enums\MemberCapabilityEnum;
use App\Enums\MemberRoleEnum;

use App\Http\Controllers\Api\EntryController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\GoogleCalendarTestController;
use App\Http\Controllers\Api\IssueTypeController;
use App\Http\Controllers\Api\JoinBarcodeController;
use App\Http\Controllers\Api\SlaController;
use App\Http\Controllers\Api\ApplicantController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\TicketHandlerController;
use App\Http\Controllers\Api\TicketLogController;
use App\Http\Controllers\Api\TicketReportController;

use Illuminate\Support\Facades\Route;


Route::get('/hello', function () {
    return response()->json(['message' => 'hello']);
});

Route::prefix('/auth')->group(function() {
    Route::post('exchange', [AuthController::class, 'exchange']);
    Route::post('refresh', [AuthController::class, 'refresh']);
});

Route::prefix('entry')->group(function(){
    Route::post('/check', [EntryController::class, 'check']);
    Route::get('/form', [EntryController::class, 'index']);
    Route::post('/form',[EntryController::class, 'store']);
});

Route::middleware('api.auth')->group(function () {

    Route::middleware('role:' . implode(',', [
        MemberRoleEnum::MANAGEMENT->value,
        MemberRoleEnum::CREW->value,
        MemberRoleEnum::MEMBER->value,
    ]))->group(function () {
        
        Route::prefix('/tickets')->group(function() {
            Route::get('/', [TicketController::class, 'index']);
            Route::post('/', [TicketController::class, 'store']);
        });

        Route::prefix('/ticket')->group(function ()  {
        
            Route::prefix('/{ticket_id}')->group(function () {

                Route::get('/', [TicketController::class, 'show']);
                Route::patch('/', [TicketController::class, 'update']);
                Route::get('/print-view', [TicketController::class, 'print_view']);
                Route::post('/close', [TicketController::class, 'close']);

                Route::prefix('logs')->group(function () {
                    Route::get('/', [TicketLogController::class, 'index']);
                });

            }); 
        });

        Route::prefix('/issue-types')->group(function () {
            Route::get('/', [IssueTypeController::class, 'index']);
        });
        
    });

    Route::middleware('role:' . implode(',', [
        MemberRoleEnum::MANAGEMENT->value,
        MemberRoleEnum::CREW->value,
    ]))->group(function () {
        
        Route::prefix('/area')->group(function (){
            Route::get('/members', [MemberController::class, 'index']);
        });
        
        Route::prefix('/ticket')->group(function ()  {
        
            Route::prefix('/{_ticketId}')->group(function () {
                
                Route::prefix('evaluate')->group(function (){
                    Route::post('/request', [TicketController::class,'evaluate_request']);
                });

                Route::post('/evaluate', [TicketController::class,'evaluate'])
                        ->middleware('capability:' . MemberCapabilityEnum::APPROVAL->value);

                Route::prefix('handlers')->group(function () {
                    Route::get('/', [TicketHandlerController::class, 'index']);
                    Route::post('/', [TicketHandlerController::class, 'store'])
                        ->middleware('capability:' . MemberCapabilityEnum::INVITE->value);
                });
                
                Route::prefix('logs')->group(function () {
                    Route::post('/', [TicketLogController::class, 'store']);
                });
            }); 
        });
    });


    Route::middleware('role:'. 
        MemberRoleEnum::MANAGEMENT->value
    )->group(function () {
        
        Route::prefix('/sla')->group(function (){
            Route::get('/', [SlaController::class, 'index']);
            Route::put('/', [SlaController::class, 'update']);
        });

        Route::prefix('/ticket')->group(function ()  {
        
            Route::prefix('/{_ticketId}')->group(function () {
                Route::post('/force-close', [TicketController::class, 'force_close']);
            }); 

        });

        Route::prefix('/area')->group(function () {

            Route::get('/', [AreaController::class, 'index']);

            Route::prefix('/join-policy')->group(function () {
                Route::get('/', [AreaController::class, 'get_join_policy']);
                Route::put('/', [AreaController::class, 'update_join_policy']); 
            });

            Route::get('/join', [JoinBarcodeController::class, 'barcode']);
        });

        Route::prefix('/issue-types')->group(function () {
            Route::post('/', [IssueTypeController::class, 'store']);
        });

        Route::delete('/issue-type/{issue_id}', [IssueTypeController::class, 'destroy']);

        Route::prefix('/area')->group(function() {

            Route::prefix('pending-memberships')->group(function() {
                Route::get('/', [ApplicantController::class, 'index']);
                Route::post('/', [ApplicantController::class, 'accept']);
                Route::get('/{application_id}', [ApplicantController::class, 'show']);
                Route::delete('/{application_id}', [ApplicantController::class, 'reject']);
            });

            Route::prefix('/member')->group(function () {
                Route::get('/{member_id}', [MemberController::class, 'show']);
                Route::delete('/{member_id}', [MemberController::class, 'destroy']);
                Route::put('/{member_id}', [MemberController::class, 'update']);
            });

        });

        Route::prefix('/statistics')->group(function () {
            Route::prefix('/{month}')->group(function (){
                Route::get('/report', [TicketReportController::class, 'periodic_report']);
                Route::get('/tickets', [TicketReportController::class, 'ticket_report']);
            });
        });
          
    });
});

Route::prefix('calendar-test')->controller(GoogleCalendarTestController::class)->group(function () {
    Route::post('create', 'createCalendar');
    Route::post('event', 'createEvent');
    Route::get('events', 'getEvents');
});
