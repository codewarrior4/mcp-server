<?php

use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('health')->group(function () {
    Route::get('/', [HealthCheckController::class, 'index']);
    Route::get('/database', [HealthCheckController::class, 'database']);
    Route::get('/cache', [HealthCheckController::class, 'cache']);
    Route::get('/queue', [HealthCheckController::class, 'queue']);
    Route::get('/redis', [HealthCheckController::class, 'redis']);
});
