<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. 2026
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });

        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. 2026 A/L Theory
            $table->timestamps();
        });

        // Update student profile with batch
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->constrained('batches');
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Physics
            $table->string('code')->nullable();
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Physics 2026 Theory Group A
            $table->foreignId('teacher_id')->constrained('users'); // Linking to user table (role=teacher)
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('batch_id')->constrained('batches');
            $table->decimal('fee_amount', 10, 2)->default(0);
            $table->json('schedule')->nullable(); // { "Monday": "08:00", "Friday": "16:00" }
            $table->string('cover_image_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
        Schema::dropIfExists('subjects');
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');
        });
        Schema::dropIfExists('batches');
        Schema::dropIfExists('academic_years');
    }
};
