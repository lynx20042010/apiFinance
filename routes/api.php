<?php

use App\Http\Controllers\Api\CompteController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    // Routes d'authentification (publiques)
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
        Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
    });

    // Routes des comptes (protégées)
    Route::middleware(['auth:api', 'logging'])->group(function () {
        // Routes pour tous les utilisateurs authentifiés
        Route::get('comptes', [CompteController::class, 'index']);

        // Routes réservées aux administrateurs (toutes les permissions) - placées avant les routes paramétrées
        Route::middleware('role:admin')->group(function () {
            // Opérations spéciales réservées aux admins
            Route::get('comptes/bloques', [CompteController::class, 'getBlockedAccounts']);
            Route::post('comptes/{compte}/block', [CompteController::class, 'block']);
        });

        // Route paramétrée pour récupérer un compte spécifique
        Route::get('comptes/{compteId}', [CompteController::class, 'show']);

        // Routes réservées aux clients (pour leurs propres comptes)
        Route::middleware('role:client')->group(function () {
            Route::post('comptes', [CompteController::class, 'store']);
            Route::put('comptes/{compteId}', [CompteController::class, 'update']);
            Route::delete('comptes/{compteId}', [CompteController::class, 'destroy']);
        });

        // Routes réservées aux administrateurs (toutes les permissions)
        Route::middleware('role:admin')->group(function () {
            Route::post('comptes', [CompteController::class, 'store']);
            Route::put('comptes/{compteId}', [CompteController::class, 'update']);
            Route::delete('comptes/{compteId}', [CompteController::class, 'destroy']);

            // Note: unblock, archive sont automatiques via jobs - pas d'actions manuelles
        });
    });
});
