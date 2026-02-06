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
            // Add teacher settlement tracking fields
            $table->timestamp('teacher_settlement_processed_at')->nullable()->after('status');
            $table->decimal('teacher_deduction_percentage', 5, 2)->nullable()->after('teacher_settlement_processed_at');
            $table->decimal('teacher_deduction_amount', 10, 2)->nullable()->after('teacher_deduction_percentage');
            $table->decimal('teacher_net_amount', 10, 2)->nullable()->after('teacher_deduction_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'teacher_settlement_processed_at',
                'teacher_deduction_percentage',
                'teacher_deduction_amount',
                'teacher_net_amount'
            ]);
        });
    }
};
