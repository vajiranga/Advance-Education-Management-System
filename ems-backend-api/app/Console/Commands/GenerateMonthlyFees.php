<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Enrollment;
use App\Models\StudentFee;
use Carbon\Carbon;

class GenerateMonthlyFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fees:generate {--month= : The month to generate fees for (YYYY-MM)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly fees for all active student enrollments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Default to current month if not provided
        $month = $this->option('month') ?? Carbon::now()->format('Y-m');

        // Check for custom due date day in settings
        $startDay = \App\Models\SystemSetting::where('key', 'feeCycleStartDay')->value('value') ?? 10;

        // Due date based on settings
        $dueDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->addDays($startDay - 1)->format('Y-m-d');

        $this->info("Generating fees for month: {$month} (Due: {$dueDate})");

        $count = 0;
        $skipped = 0;

        // Fetch active enrollments with fee > 0
        $enrollments = Enrollment::where('status', 'active')
            ->whereHas('course', function($q) {
                $q->where('fee_amount', '>', 0);
            })
            ->with(['course', 'student.parentAccount'])
            ->chunk(100, function ($enrollments) use ($month, $dueDate, &$count, &$skipped) {
                foreach ($enrollments as $enrollment) {
                    // Check if fee already exists
                    $exists = StudentFee::where('student_id', $enrollment->user_id)
                        ->where('course_id', $enrollment->course_id)
                        ->where('month', $month)
                        ->exists();

                    if (!$exists) {
                        $fee = StudentFee::create([
                            'student_id' => $enrollment->user_id,
                            'course_id' => $enrollment->course_id,
                            'month' => $month,
                            'amount' => $enrollment->course->fee_amount,
                            'due_date' => $dueDate,
                            'status' => 'pending'
                        ]);
                        $count++;

                        // Notify Student
                        \App\Models\Notification::create([
                            'user_id' => $enrollment->user_id,
                            'type' => 'fee_due',
                            'title' => 'New Fee Details',
                            'message' => 'Fee for ' . $enrollment->course->name . ' (' . $month . ') is now due.',
                            'data' => json_encode(['fee_id' => $fee->id])
                        ]);

                        // Notify Parent
                        if ($enrollment->student && $enrollment->student->parentAccount) {
                            \App\Models\Notification::create([
                                'user_id' => $enrollment->student->parentAccount->id,
                                'type' => 'fee_due',
                                'title' => 'Fee Due for ' . $enrollment->student->name,
                                'message' => 'Fee for ' . $enrollment->course->name . ' (' . $month . ') is now pending.',
                                'data' => json_encode(['fee_id' => $fee->id, 'student_id' => $enrollment->user_id])
                            ]);
                        }
                    } else {
                        $skipped++;
                    }
                }
            });

        $this->info("Completed! Generated: {$count} records. Skipped (Already Existed): {$skipped}.");
        return 0;
    }
}
