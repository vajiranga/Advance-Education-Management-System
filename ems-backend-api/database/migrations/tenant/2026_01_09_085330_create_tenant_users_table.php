<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Users Table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('student'); // admin, teacher, student, parent
            $table->string('phone')->nullable();
            $table->string('avatar_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });

        // Student Profiles
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('barcode_id')->unique()->nullable();
            $table->foreignId('guardian_id')->nullable()->constrained('users'); // Parent
            // batch_id will be added in academic structure migration or here if order permits. 
            // Better to add foreign keys in a separate migration or ensure order. 
            // For now, I'll add column but index later or rely on order. 
            // Actually, I'll defer batch_id to the academic migration to avoid failing.
            $table->decimal('wallet_balance', 10, 2)->default(0);
            $table->integer('points')->default(0);
            $table->timestamps();
        });

        // Teacher Profiles
        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('qualifications')->nullable();
            $table->json('bank_details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_profiles');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('users');
    }
};
