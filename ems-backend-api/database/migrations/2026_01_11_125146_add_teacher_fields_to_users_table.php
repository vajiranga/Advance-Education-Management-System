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
        Schema::table('users', function (Blueprint $table) {
            // Teacher specific fields
            $table->string('nic')->nullable();
            $table->json('qualifications')->nullable(); // Store as JSON array ["AL", "Degree"]
            $table->json('subjects')->nullable();      // Store as JSON array ["Math", "Science"]
            $table->string('experience')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nic', 'qualifications', 'subjects', 'experience']);
        });
    }
};
