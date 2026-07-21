<?php

use App\Http\Controllers\APIVersionController;
use Illuminate\Support\Facades\Route;

// Base Endpoint: GET http://localhost/api
Route::get('/', function () {
    return response()->json([
        'message' => 'Welcome to the Celestia Nova API Server',
        'version' => '1.0.0',
    ]);
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'auth-api',
    ]);
});

// Dynamic Versioning Gateway
Route::prefix('{version}')->group(function () {
    Route::get('/', [APIVersionController::class, 'index']);

    Route::any('{any}', [APIVersionController::class, 'handleDynamicMapping'])
        ->where('any', '.*');

});
