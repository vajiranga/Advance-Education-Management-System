<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Check if columns exist before adding
            if (!Schema::hasColumn('payments', 'slip_image')) {
                $table->string('slip_image')->nullable()->after('amount');
            }
             // Ensure status can handle 'pending'. If it was an enum, modify it. 
             // If it's a string, it's fine. If it doesn't exist (unlikely), add it.
             if (!Schema::hasColumn('payments', 'status')) {
                 $table->string('status')->default('paid');
             }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         // Keep it safe
    }
};
