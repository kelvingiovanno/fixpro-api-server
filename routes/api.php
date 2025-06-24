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
                Route::post('/cancel', [TicketController::class, 'cancelTicket']);
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
            Route::get('/members', [AreaController::class, 'getMembers']);
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

                Route::post('/reject', [TicketController::class, 'rejectTicket']);
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
                Route::get('/', [AreaController::class, 'getPendingMembers']);
                Route::post('/', [AreaController::class, 'postPendingMembers']);
                Route::get('/{application_id}', [AreaController::class, 'getPendingMember']);
                Route::delete('/{application_id}', [AreaController::class, 'delPendingMember']);
            });

            Route::prefix('/member')->group(function () {
                Route::get('/{member_id}', [AreaController::class, 'getMember']);
                Route::delete('/{member_id}', [AreaController::class, 'deleteMember']);
                Route::put('/{member_id}', [AreaController::class, 'putMember']);
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
