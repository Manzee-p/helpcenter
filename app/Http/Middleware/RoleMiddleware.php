<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

/**
 * Role-based Authorization Middleware for Laravel 12
 * 
 * Usage in routes:
 * Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
 *     Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
 * });
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  One or more roles (e.g., 'admin', 'vendor', 'client')
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // ========================================
        // 1. CHECK AUTHENTICATION
        // ========================================
        if (!$request->user()) {
            Log::warning('RoleMiddleware: Unauthenticated access attempt', [
                'ip' => $request->ip(),
                'route' => $request->path(),
                'method' => $request->method(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please login first.',
                'code' => 'UNAUTHENTICATED'
            ], 401);
        }

        // ========================================
        // 2. GET USER ROLE
        // ========================================
        $user = $request->user();
        $userRole = $user->role ?? null;

        // Check if user has a role
        if (!$userRole) {
            Log::error('RoleMiddleware: User has no role assigned', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'route' => $request->path()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'User has no role assigned. Please contact administrator.',
                'code' => 'NO_ROLE_ASSIGNED'
            ], 403);
        }

        // ========================================
        // 3. CHECK AUTHORIZATION
        // ========================================
        if (!in_array($userRole, $roles)) {
            Log::warning('RoleMiddleware: Unauthorized access attempt', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $userRole,
                'required_roles' => $roles,
                'route' => $request->path(),
                'method' => $request->method(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have permission to access this resource.',
                'code' => 'INSUFFICIENT_PERMISSIONS',
                'details' => [
                    'required_role' => count($roles) > 1 
                        ? 'One of: ' . implode(', ', $roles) 
                        : $roles[0],
                    'your_role' => $userRole
                ]
            ], 403);
        }

        // ========================================
        // 4. LOG SUCCESS (DEBUG ONLY)
        // ========================================
        if (config('app.debug')) {
            Log::info('RoleMiddleware: Access granted', [
                'user_id' => $user->id,
                'user_role' => $userRole,
                'route' => $request->path(),
                'required_roles' => $roles
            ]);
        }

        // ========================================
        // 5. ALLOW REQUEST
        // ========================================
        return $next($request);
    }
}