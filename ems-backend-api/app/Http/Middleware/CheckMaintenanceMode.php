<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get maintenance mode setting from database
        $maintenanceSetting = \App\Models\SystemSetting::where('key', 'maintenanceMode')->first();

        // Check if maintenance mode is enabled
        $isMaintenanceMode = $maintenanceSetting &&
                            ($maintenanceSetting->value === 'true' ||
                             $maintenanceSetting->value === '1' ||
                             $maintenanceSetting->value === 1 ||
                             $maintenanceSetting->value === true);

        if ($isMaintenanceMode) {
            // Allow admin users to access
            $user = $request->user();

            // If user is not authenticated or not an admin, block access
            if (!$user || $user->role !== 'admin') {
                return response()->json([
                    'message' => 'System is currently under maintenance. Please try again later.',
                    'maintenance_mode' => true
                ], 503);
            }
        }

        return $next($request);
    }
}
