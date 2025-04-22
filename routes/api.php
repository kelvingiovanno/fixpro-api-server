<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AreaController;

use App\Http\Middleware\ApiAuthMiddleware;
use App\Http\Middleware\EntryMiddleware;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/hello', function () {
    return response('hello world');
});

Route::prefix('/auth')->group(function() {
    Route::post('exchange', [AuthController::class, 'exchange']);
    Route::post('refresh', [AuthController::class, 'refresh']);
});

Route::middleware(EntryMiddleware::class)->prefix('entry')->group(function(){
    Route::post('/check', [FormController::class, 'check']);
    Route::get('/form', [FormController::class, 'getForm']);
    Route::post('/form',[FormController::class, 'submit']);
});

Route::prefix('/ticket')->group(function ()  {
        
    Route::prefix('/{ticket_id}')->group(function () {
        Route::get('/', [TicketController::class, 'get']);
        Route::delete('/', [TicketController::class, 'delete']);
    }); 
});

Route::prefix('/area')->group(function() {

    Route::get('/', [AreaController::class, 'index']);
    Route::get('/join-code', [AreaController::class, 'getJoinCode']);
    Route::delete('/join-code', [AreaController::class, 'delJoinCode']);

    Route::prefix('members')->group(function() {        
        Route::get('/', [AreaController::class, 'getMembers']);
        Route::post('/', [AreaController::class, 'postMembers']);

        Route::prefix('pending')->group(function() {
            Route::get('/', [AreaController::class, 'getPendingMembers']);
            Route::get('/{application_id}', [AreaController::class, 'getApplicant']);
            Route::delete('/{application_id}', [AreaController::class, 'delApplicant']);
        });
    });

    Route::prefix('/member')->group(function () {
        Route::get('/{member_id}', [AreaController::class, 'getMember']);
        Route::delete('/{member_id}', [AreaController::class, 'delMember']);
    });
    
});

Route::middleware(ApiAuthMiddleware::class)->group(function () {

    Route::prefix('/tickets')->group(function() {
        Route::get('/', [TicketController::class, 'getAll']);
        Route::post('/', [TicketController::class, 'create']);
    });    

});