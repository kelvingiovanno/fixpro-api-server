<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\EntryController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\IssueTypeController;

use App\Http\Controllers\FileUploadController;

use App\Http\Middleware\ApiAuthMiddleware;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/hello', function (Request $request) {
    $serverIp = $serverIp = gethostbyname(gethostname());
    return response('The server IP address is: ' . $serverIp);
});

Route::prefix('/auth')->group(function() {
    Route::post('exchange', [AuthController::class, 'exchange']);
    Route::post('refresh', [AuthController::class, 'refresh']);
});

Route::prefix('entry')->group(function(){
    Route::post('/check', [EntryController::class, 'check']);
    Route::get('/form', [EntryController::class, 'getForm']);
    Route::post('/form',[EntryController::class, 'submit']);
});

Route::middleware(ApiAuthMiddleware::class)->group(function () {

    Route::prefix('/ticket')->group(function ()  {
        
        Route::prefix('/{_ticketId}')->group(function () {

            Route::get('/', [TicketController::class, 'getTicket']);
            Route::patch('/', [TicketController::class, 'patchTicket']);
            Route::delete('/', [TicketController::class, 'delTicket']);
            
            Route::get('/print-view', [TicketController::class, 'printView']);
            
            Route::post('/reject', [TicketController::class, 'rejectTicket']);
            Route::post('/cancel', [TicketController::class, 'cancelTicket']);

            Route::post('/close', [TicketController::class, 'close']);
            Route::post('/force-close', [TicketController::class, 'forceClose']);
            
            Route::prefix('evaluate')->group(function (){
                Route::post('/', [TicketController::class,'evaluate']);
                Route::post('/request', [TicketController::class,'evaluateRequest']);
            });

            Route::prefix('handlers')->group(function () {
                Route::get('/', [TicketController::class, 'getHandlers']);
                Route::post('/', [TicketController::class, 'postHandlers']);
            });

            Route::prefix('logs')->group(function () {
                Route::get('/', [TicketController::class, 'getLogs']);
                Route::post('/', [TicketController::class, 'postLog']);
            });
        }); 
    });
        
    Route::prefix('/tickets')->group(function() {
        Route::get('/', [TicketController::class, 'getTickets']);
        Route::post('/', [TicketController::class, 'postTicket']);
    });    
   
    Route::prefix('/issue-types')->group(function () {
        Route::get('/', [IssueTypeController::class, 'getAllIssueType']);
        Route::post('/', [IssueTypeController::class, 'postIssueType']);
        Route::delete('/{_issueTypeId}', [IssueTypeController::class, 'deleteIssueType']);
    });    

    Route::prefix('/area')->group(function() {

        Route::get('/', [AreaController::class, 'index']);
        Route::get('/join-code', [AreaController::class, 'getJoinCode']);
        Route::delete('/join-code', [AreaController::class, 'delJoinCode']);

        Route::prefix('/join-policy')->group(function () {
            Route::get('/', [AreaController::class, 'getJoinPolicy']);
            Route::put('/', [AreaController::class, 'putJoinPolicy']); 
        });

        Route::prefix('members')->group(function() {        
            Route::get('/', [AreaController::class, 'getMembers']);
        });

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
});



Route::post('/upload', [FileUploadController::class, 'upload']);
Route::get('/file/{filename}', [FileUploadController::class, 'getFile']);