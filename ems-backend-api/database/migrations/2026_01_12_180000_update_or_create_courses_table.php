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
        if (!Schema::hasTable('courses')) {
             Schema::create('courses', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedBigInteger('teacher_id')->nullable(); 
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('batch_id')->nullable();
                $table->decimal('fee_amount', 10, 2)->default(0);
                $table->json('schedule')->nullable();
                $table->string('cover_image_url')->nullable();
                
                // New fields
                $table->string('status')->default('pending');
                $table->text('admin_note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('courses', function (Blueprint $table) {
                if (!Schema::hasColumn('courses', 'status')) {
                    $table->string('status')->default('pending');
                }
                if (!Schema::hasColumn('courses', 'admin_note')) {
                    $table->text('admin_note')->nullable();
                }
                if (!Schema::hasColumn('courses', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('courses')) {
             Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn(['status', 'admin_note', 'created_by']);
            });
        }
    }
};
