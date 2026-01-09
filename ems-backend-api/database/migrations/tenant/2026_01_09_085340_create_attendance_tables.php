<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->string('qr_code_token')->nullable(); // Rotates every few seconds
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('class_sessions')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users');
            $table->string('status')->default('present'); // present, late, absent
            $table->string('method')->default('qr'); // qr, nfc, face, manual
            $table->timestamp('in_time');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('class_sessions');
    }
};
