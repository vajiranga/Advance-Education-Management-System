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
        // Add indexes to student_fees table for better query performance
        Schema::table('student_fees', function (Blueprint $table) {
            $table->index('student_id', 'idx_student_fees_student_id');
            $table->index('course_id', 'idx_student_fees_course_id');
            $table->index('status', 'idx_student_fees_status');
            $table->index('month', 'idx_student_fees_month');
            $table->index(['student_id', 'status'], 'idx_student_fees_student_status');
            $table->index(['course_id', 'month'], 'idx_student_fees_course_month');
        });

        // Add indexes to payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->index('user_id', 'idx_payments_user_id');
            $table->index('course_id', 'idx_payments_course_id');
            $table->index('status', 'idx_payments_status');
            $table->index('month', 'idx_payments_month');
            $table->index('paid_at', 'idx_payments_paid_at');
            $table->index(['user_id', 'status'], 'idx_payments_user_status');
        });

        // Add indexes to enrollments table
        Schema::table('enrollments', function (Blueprint $table) {
            $table->index('user_id', 'idx_enrollments_user_id');
            $table->index('course_id', 'idx_enrollments_course_id');
            $table->index('status', 'idx_enrollments_status');
            $table->index(['user_id', 'status'], 'idx_enrollments_user_status');
            $table->index(['course_id', 'status'], 'idx_enrollments_course_status');
        });

        // Add indexes to attendances table
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('user_id', 'idx_attendances_user_id');
            $table->index('course_id', 'idx_attendances_course_id');
            $table->index('date', 'idx_attendances_date');
            $table->index('status', 'idx_attendances_status');
            $table->index(['user_id', 'date'], 'idx_attendances_user_date');
        });

        // Add indexes to courses table
        Schema::table('courses', function (Blueprint $table) {
            $table->index('teacher_id', 'idx_courses_teacher_id');
            $table->index('subject_id', 'idx_courses_subject_id');
            $table->index('batch_id', 'idx_courses_batch_id');
            $table->index('parent_course_id', 'idx_courses_parent_course_id');
        });

        // Add indexes to exam_results table
        Schema::table('exam_results', function (Blueprint $table) {
            $table->index('student_id', 'idx_exam_results_student_id');
            $table->index('exam_id', 'idx_exam_results_exam_id');
            $table->index('is_published', 'idx_exam_results_published');
            $table->index(['student_id', 'is_published'], 'idx_exam_results_student_published');
        });

        // Add indexes to exams table
        Schema::table('exams', function (Blueprint $table) {
            $table->index('course_id', 'idx_exams_course_id');
            $table->index('date', 'idx_exams_date');
        });

        // Add indexes to notifications table
        Schema::table('notifications', function (Blueprint $table) {
            $table->index('user_id', 'idx_notifications_user_id');
            $table->index('read_at', 'idx_notifications_read_at');
            $table->index(['user_id', 'read_at'], 'idx_notifications_user_read');
        });

        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'idx_users_role');
            $table->index('is_active', 'idx_users_is_active');
            $table->index('parent_email', 'idx_users_parent_email');
            $table->index('parent_phone', 'idx_users_parent_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropIndex('idx_student_fees_student_id');
            $table->dropIndex('idx_student_fees_course_id');
            $table->dropIndex('idx_student_fees_status');
            $table->dropIndex('idx_student_fees_month');
            $table->dropIndex('idx_student_fees_student_status');
            $table->dropIndex('idx_student_fees_course_month');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_user_id');
            $table->dropIndex('idx_payments_course_id');
            $table->dropIndex('idx_payments_status');
            $table->dropIndex('idx_payments_month');
            $table->dropIndex('idx_payments_paid_at');
            $table->dropIndex('idx_payments_user_status');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex('idx_enrollments_user_id');
            $table->dropIndex('idx_enrollments_course_id');
            $table->dropIndex('idx_enrollments_status');
            $table->dropIndex('idx_enrollments_user_status');
            $table->dropIndex('idx_enrollments_course_status');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_attendances_user_id');
            $table->dropIndex('idx_attendances_course_id');
            $table->dropIndex('idx_attendances_date');
            $table->dropIndex('idx_attendances_status');
            $table->dropIndex('idx_attendances_user_date');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex('idx_courses_teacher_id');
            $table->dropIndex('idx_courses_subject_id');
            $table->dropIndex('idx_courses_batch_id');
            $table->dropIndex('idx_courses_parent_course_id');
        });

        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropIndex('idx_exam_results_student_id');
            $table->dropIndex('idx_exam_results_exam_id');
            $table->dropIndex('idx_exam_results_published');
            $table->dropIndex('idx_exam_results_student_published');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropIndex('idx_exams_course_id');
            $table->dropIndex('idx_exams_date');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_user_id');
            $table->dropIndex('idx_notifications_read_at');
            $table->dropIndex('idx_notifications_user_read');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role');
            $table->dropIndex('idx_users_is_active');
            $table->dropIndex('idx_users_parent_email');
            $table->dropIndex('idx_users_parent_phone');
        });
    }
};
