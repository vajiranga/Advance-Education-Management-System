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
        Schema::create('student_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('month'); // Format: YYYY-MM
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->string('status')->default('pending'); // pending, paid, overdue
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable(); // card, cash, bank_transfer
            $table->string('transaction_ref')->nullable(); // For online payments
            $table->timestamps();

            // Prevent duplicate fees for same student-course-month
            $table->unique(['student_id', 'course_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_fees');
    }
};
