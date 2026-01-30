<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            \Illuminate\Support\Facades\Log::warning("RoleMiddleware: 403 - No User Authenticated. Header: " . $request->header('Authorization'));
            return response()->json(['message' => 'Unauthorized. Access Denied.'], 403);
        }

        // Allow super_admin to access everything
        if ($request->user()->role === 'super_admin') {
            return $next($request);
        }

        $userRole = strtolower(trim($request->user()->role));
        // Ensure roles are flattened if somehow passed as array
        $allowedRoles = [];
        foreach ($roles as $role) {
             if (is_array($role)) {
                 $allowedRoles = array_merge($allowedRoles, $role);
             } else {
                 $allowedRoles[] = $role;
             }
        }
        $allowedRoles = array_map(fn($r) => strtolower(trim($r)), $allowedRoles);
        
        if (!in_array($userRole, $allowedRoles)) {
            \Illuminate\Support\Facades\Log::warning("RoleMiddleware: 403 - FAILURE - User [{$request->user()->id} : {$request->user()->username}] with role [{$userRole}] tried to access protected route. Allowed: " . json_encode($allowedRoles));
            return response()->json(['message' => 'Unauthorized. Access Denied.'], 403);
        }

        \Illuminate\Support\Facades\Log::info("RoleMiddleware: ACCESS GRANTED - User [{$request->user()->id}] Role [{$userRole}] matched Allowed " . json_encode($allowedRoles));
        return $next($request);
    }
}
