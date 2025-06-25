<?php

use App\Enums\MemberRoleEnum;
use Illuminate\Support\Facades\Route;

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

Route::prefix('/auth')->group(function() {
    Route::post('exchange', [AuthController::class, 'exchange']);
    Route::post('refresh', [AuthController::class, 'refresh']);
});

Route::prefix('entry')->group(function(){
    Route::post('/check', [EntryController::class, 'check']);
    Route::get('/form', [EntryController::class, 'index']);
    Route::post('/form',[EntryController::class, 'store']);
});

Route::middleware(['api.auth'])->group(function () {

    Route::middleware('role:' . implode(',', [
        MemberRoleEnum::MANAGEMENT->value,
        MemberRoleEnum::CREW->value,
        MemberRoleEnum::MEMBER->value,
    ]))->group(function () {
        
        Route::prefix('/tickets')->group(function() {
            Route::get('/', [TicketController::class, 'getTickets']);
            Route::post('/', [TicketController::class, 'postTicket']);
        });

        Route::prefix('/ticket')->group(function ()  {
        
            Route::prefix('/{_ticketId}')->group(function () {

                Route::get('/', [TicketController::class, 'getTicket']);
                Route::patch('/', [TicketController::class, 'patchTicket']);
                Route::get('/print-view', [TicketController::class, 'printView']);
                Route::post('/close', [TicketController::class, 'close']);

                Route::prefix('logs')->group(function () {
                    Route::get('/', [TicketController::class, 'getLogs']);
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
                    Route::post('/', [TicketController::class,'evaluate']);
                    Route::post('/request', [TicketController::class,'evaluateRequest']);
                });

                Route::prefix('handlers')->group(function () {
                    Route::get('/', [TicketController::class, 'getHandlers']);
                    Route::post('/', [TicketController::class, 'postHandlers']);
                });
                
                Route::prefix('logs')->group(function () {
                    Route::post('/', [TicketController::class, 'postLog']);
                });
            }); 
        });
    });


    Route::middleware('role:'. 
        MemberRoleEnum::MANAGEMENT->value
    )->group(function () {
        
        Route::prefix('/sla')->group(function (){
            Route::get('/', [SlaController::class, 'get_sla']);
            Route::put('/', [SlaController::class, 'put_sla']);
        });

        Route::prefix('/ticket')->group(function ()  {
        
            Route::prefix('/{_ticketId}')->group(function () {

                Route::post('/force-close', [TicketController::class, 'forceClose']);

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
            Route::prefix('/{_month}')->group(function (){
                Route::get('/report', [AreaController::class, 'get_periodic_report']);
                Route::get('/tickets', [AreaController::class, 'get_ticket_report']);
            });
        });
          
    });
});


Route::prefix('calendar-test')->controller(GoogleCalendarTestController::class)->group(function () {
    Route::post('create', 'createCalendar');
    Route::post('event', 'createEvent');
    Route::get('events', 'getEvents');
});
