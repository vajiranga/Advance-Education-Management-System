<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Services\TenantService;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    /**
     * Display a listing of the tenants (Institutes).
     */
    public function index()
    {
        // Only Super Admin should see this
        return response()->json(Tenant::all());
    }

    /**
     * Store a new tenant (Register a new Institute).
     */
    public function store(Request $request, TenantService $tenantService)
    {
        $validated = $request->validate([
            'id' => 'required|alpha_dash|unique:tenants,id', // e.g., 'royal-college'
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'plan_id' => 'required|string',
            'domain' => 'required|string|unique:domains,domain', // e.g., 'royal.ems.lk'
        ]);

        $tenantData = [
            'id' => $validated['id'],
            'name' => $validated['name'],
            'plan_id' => $validated['plan_id'],
            'data' => [
                'owner_email' => $validated['email']
            ]
        ];

        // Use the service to create tenant and database
        $tenant = $tenantService->createTenant($tenantData);

        // Add domain manually since we are managing it
        DB::table('domains')->insert([
            'domain' => $validated['domain'],
            'tenant_id' => $tenant->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Institute registered successfully. Database and Environment provisioned.',
            'tenant' => $tenant
        ], 201);
    }

    /**
     * Display the specified tenant.
     */
    public function show(string $id)
    {
        return response()->json(Tenant::findOrFail($id));
    }
    
    // Update and destroy methods...
}
