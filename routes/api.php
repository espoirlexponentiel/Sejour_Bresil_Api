<?php

use App\Http\Controllers\Api\AidCenterController;
use App\Http\Controllers\Api\AssistanceRequestController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\DemarcheController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\StayController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Séjour au Brésil (Guide administratif & orientation)
|--------------------------------------------------------------------------
*/

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => 'Séjour au Brésil REST API',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Authentification & Rôles (Admin & Utilisateur)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/user/email-login', [AuthController::class, 'emailLogin']);

// Dépôt public d'une demande d'assistance (avec upload Cloudinary)
Route::post('/assistance-requests', [AssistanceRequestController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Espace Usager (Suivi des demandes d'assistance)
    Route::get('/user/assistance-requests', [AssistanceRequestController::class, 'myRequests']);

    // Espace Admin (Réception & Retours d'assistance)
    Route::get('/admin/assistance-requests', [AssistanceRequestController::class, 'adminIndex']);
    Route::post('/admin/assistance-requests/{id}/respond', [AssistanceRequestController::class, 'adminRespond']);
});

// Démarches administratives (CPF, Titre de séjour, etc.)
Route::apiResource('demarches', DemarcheController::class);

// Annuaire des centres d'accueil et assistance par ville (CRAI, DPU, associations...)
Route::apiResource('centers', AidCenterController::class);

// Stays & Bookings (Modules complémentaires)
Route::apiResource('stays', StayController::class);
Route::apiResource('bookings', BookingController::class);
Route::get('/stats', [StatsController::class, 'index']);
