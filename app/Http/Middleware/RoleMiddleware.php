<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => 'Authentification requise'
                ]
            ], 401);
        }

        // Vérifier le rôle de l'utilisateur
        $userRole = $user->admin ? 'admin' : 'client';

        if ($userRole !== $role) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INSUFFICIENT_PERMISSIONS',
                    'message' => 'Permissions insuffisantes pour accéder à cette ressource',
                    'details' => [
                        'requiredRole' => $role,
                        'userRole' => $userRole
                    ]
                ]
            ], 403);
        }

        return $next($request);
    }
}
