<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class TenantService
{
    /**
     * Create a new tenant, database, and run migrations.
     */
    public function createTenant(array $data)
    {
        // 1. Create Tenant Record
        $tenant = Tenant::create($data);

        // 2. Create Database Name (Sanitized)
        $dbName = 'ems_tenant_' . str_replace('-', '_', $tenant->id);
        
        // 3. Create the Database
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // 4. Update Tenant Record with DB Name
        $tenant->update(['tenancy_db_name' => $dbName]);

        // 5. Run Migrations for this Tenant
        $this->runTenantMigrations($tenant);

        return $tenant;
    }

    /**
     * Switch context to the given tenant.
     */
    public function switchToTenant(Tenant $tenant)
    {
        // Set the database name for the 'tenant' connection
        Config::set('database.connections.tenant.database', $tenant->tenancy_db_name);
        
        // Purge and reconnect
        DB::purge('tenant');
        DB::reconnect('tenant');
        
        // Use this connection as default if needed, or just use 'tenant' connection explicitly
        // DB::setDefaultConnection('tenant'); 
    }

    /**
     * Run migrations on the tenant database.
     */
    public function runTenantMigrations(Tenant $tenant)
    {
        $this->switchToTenant($tenant);

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);
    }
}
