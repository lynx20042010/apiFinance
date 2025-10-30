<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('api')->user();
        $userId = $user ? $user->id : null;
        $userRole = $user ? ($user->admin ? 'admin' : 'client') : 'anonymous';

        // Log de l'opération
        Log::info('API Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'user_id' => $userId,
            'user_role' => $userRole,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString()
        ]);

        $response = $next($request);

        // Log de la réponse
        Log::info('API Response', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'user_id' => $userId,
            'status_code' => $response->getStatusCode(),
            'timestamp' => now()->toISOString()
        ]);

        return $response;
    }
}
