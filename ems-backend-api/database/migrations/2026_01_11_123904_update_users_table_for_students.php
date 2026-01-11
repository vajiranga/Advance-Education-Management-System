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
            // Identifier for login (Index Number)
            $table->string('username')->unique()->nullable()->after('name');
            
            // Make email nullable as it's optional
            $table->string('email')->nullable()->change();

            // Student specific fields
            $table->date('dob')->nullable();
            $table->string('gender')->nullable(); // male, female
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('school')->nullable();
            $table->string('grade')->nullable();

            // Parent details (embedded in student record as per requirement)
            $table->string('parent_name')->nullable();
            $table->string('parent_phone')->nullable(); // Emergency/WhatsApp
            $table->string('parent_email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username', 
                'dob', 
                'gender', 
                'phone', 
                'whatsapp', 
                'school', 
                'grade', 
                'parent_name', 
                'parent_phone', 
                'parent_email'
            ]);
            $table->string('email')->nullable(false)->change();
        });
    }
};
