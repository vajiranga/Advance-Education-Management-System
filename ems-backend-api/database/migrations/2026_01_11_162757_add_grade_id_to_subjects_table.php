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
        // Skipped to fix order issue. 
        // We will add this column in a later migration.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
