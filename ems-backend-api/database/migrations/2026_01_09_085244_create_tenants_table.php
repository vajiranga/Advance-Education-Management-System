<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();
            
            // Custom Columns
            $table->string('name')->nullable();
            $table->string('status')->default('active'); // active, suspended, cancelled
            $table->string('plan_id')->nullable(); 
            $table->string('tenancy_db_name')->nullable();

            // Stancl Tenancy required
            $table->json('data')->nullable(); // For flexible storage
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
