<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\StayController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Séjour Brésil Backend
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => 'Séjour Brésil REST API',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Stays (Séjours) routes
Route::apiResource('stays', StayController::class);

// Bookings (Réservations) routes
Route::apiResource('bookings', BookingController::class);

// Dashboard KPI analytics
Route::get('/stats', [StatsController::class, 'index']);
