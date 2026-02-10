<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Enrollment;
use App\Models\StudentFee;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class CheckUnpaidFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fees:check-unpaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for students with excessive unpaid fees and mark them as inactive.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Unpaid Fees Check...');

        // 1. Get Settings
        $maxUnpaidMonths = (int) (SystemSetting::where('key', 'maxUnpaidMonths')->value('value') ?? 0);

        if ($maxUnpaidMonths <= 0) {
            $this->info('Max Unpaid Months setting is not configured or disabled (0). Skipping check.');
            return;
        }

        $this->info("Max Unpaid Months Threshold: {$maxUnpaidMonths}");

        // 2. Iterate Active Enrollments
        // We chunk to avoid memory issues with large student bases
        Enrollment::where('status', 'active')
            ->chunk(100, function ($enrollments) use ($maxUnpaidMonths) {
                foreach ($enrollments as $enrollment) {
                    try {
                        // Count unpaid fees for this specific enrollment (student + course)
                        // We count checks that are 'pending' or 'overdue' (not 'paid' or 'free_card')
                        // We also usually check if the fee month is strictly in the past or valid due.
                        // Simplified: Count all non-paid fees in the fees table.
                        // Assumption: The system generates fees for valid months.

                        $unpaidCount = StudentFee::where('student_id', $enrollment->user_id)
                            ->where('course_id', $enrollment->course_id)
                            ->whereIn('status', ['pending', 'overdue', 'unpaid', 'partially_paid'])
                            ->count();

                        if ($unpaidCount >= $maxUnpaidMonths) {
                            // Threshold exceeded, mark inactive
                            $enrollment->update(['status' => 'inactive']);

                            $this->warn("Marked Student ID {$enrollment->user_id} as INACTIVE for Course ID {$enrollment->course_id}. Unpaid Months: {$unpaidCount}");
                            Log::info("Auto-Suspend: Student {$enrollment->user_id} suspended from Course {$enrollment->course_id} due to {$unpaidCount} unpaid months.");
                        }

                    } catch (\Exception $e) {
                        $this->error("EXCEPTION processing enrollment {$enrollment->id}: " . $e->getMessage());
                        Log::error("Unpaid Fees Check Error for Enrollment {$enrollment->id}: " . $e->getMessage());
                    }
                }
            });

        $this->info('Unpaid Fees Check Completed.');
    }
}
