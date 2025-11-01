<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

// Health check endpoint for Docker and monitoring
Route::get('/health', function () {
    try {
        $databaseStatus = 'disconnected';
        $neonStatus = 'not_configured';

        try {
            DB::connection('render')->getPdo();
            $databaseStatus = 'connected';
        } catch (\Exception $e) {
            $databaseStatus = 'error: ' . $e->getMessage();
        }

        if (env('RENDER2_DB_HOST')) {
            try {
                DB::connection('render2')->getPdo();
                $render2Status = 'connected';
            } catch (\Exception $e) {
                $render2Status = 'error: ' . $e->getMessage();
            }
        } else {
            $render2Status = 'not_configured';
        }

        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'services' => [
                'render_database' => $databaseStatus,
                'render2_database' => $render2Status,
                'cache' => Cache::store()->getStore() ? 'connected' : 'disconnected'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
});