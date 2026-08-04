<?php

use App\Http\Controllers\Api\MCPToolExecutionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/mcp/execute', MCPToolExecutionController::class)
    ->middleware('auth:sanctum')
    ->name('mcp.execute');
