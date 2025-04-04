<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TicketController;

use App\Http\Middleware\ApiAuthMiddleware;
use App\Http\Middleware\EntryMiddleware;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::get('/hello', function () {
    return response('hello world');
});

Route::middleware(EntryMiddleware::class)->prefix('entry')->group(function(){
    Route::get('/check', [FormController::class, 'check']);
    Route::get('/form', [FormController::class, 'request']);
    Route::post('/form',[FormController::class, 'submit']);
});

Route::middleware(ApiAuthMiddleware::class)->group(function () {

    Route::prefix('/tickets')->group(function() {
        Route::get('/', [TicketController::class, 'getAll']);
        Route::post('/', [TicketController::class], 'create');
    });

    Route::prefix('/ticket')->group(function ()  {
        
        Route::prefix('/{ticket_id}')->group(function () {
            Route::get('/', [TicketController::class, '']);
            Route::delete('/', [TicketController::class, '']);
            Route::patch('/', [TicketController::class, '']);
        });
    });

    Route::get('/secure-endpoint', function () {
        return response()->json(['message' => 'Access granted']);
    });

});