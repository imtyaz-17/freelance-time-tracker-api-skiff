<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TimeLogController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Clients
    Route::apiResource('clients', ClientController::class);
    
    // Projects
    Route::apiResource('projects', ProjectController::class);
    
    // Time Logs
    Route::apiResource('time-logs', TimeLogController::class);
    Route::post('/time-logs/start', [TimeLogController::class, 'startTimer']);
    Route::post('/time-logs/{timeLog}/stop', [TimeLogController::class, 'stopTimer']);
    
    // Reports
    Route::get('/reports', [ReportController::class, 'index']);
}); 