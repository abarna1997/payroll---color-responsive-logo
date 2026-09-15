<?php

use App\Http\Controllers\Api\AdmsController;
use Illuminate\Support\Facades\Route;

// ZKTeco ADMS Protocol Endpoints
Route::middleware('throttle:adms')->group(function () {
    Route::get('/adms/cdata', [AdmsController::class, 'handshake']);
    Route::post('/adms/cdata', [AdmsController::class, 'receiveData']);
    Route::get('/adms/getrequest', [AdmsController::class, 'getRequest']);
    Route::post('/adms/devicecmd', [AdmsController::class, 'deviceCommandCallback']);
});
