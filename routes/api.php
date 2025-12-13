<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdController;
use App\Http\Controllers\AuthController;

Route::prefix('v1')->group(function () {
    
    // Public Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Public Routes
    Route::get('/ads/{id}', [AdController::class, 'show']);

    // Protected Routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/ads', [AdController::class, 'store']);
        Route::get('/my-ads', [AdController::class, 'myAds']);
    });
});