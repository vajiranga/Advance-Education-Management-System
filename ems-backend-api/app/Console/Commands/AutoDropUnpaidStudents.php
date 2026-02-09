<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemSetting;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\StudentFee;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoDropUnpaidStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollments:auto-drop-unpaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-drop students who have not paid fees for a configurable number of months';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting auto-drop check...");

        // 1. Get the Setting
        $maxUnpaidMonths = (int) SystemSetting::where('key', 'maxUnpaidMonthsBeforeDrop')->value('value');

        if ($maxUnpaidMonths <= 0) {
            $this->info("Auto-drop is disabled (maxUnpaidMonthsBeforeDrop = {$maxUnpaidMonths}). Exiting.");
            return;
        }

        $this->info("Auto-drop threshold: {$maxUnpaidMonths} months. Checking for students with {$maxUnpaidMonths} or more consecutive unpaid months.");

        // We need to look back $maxUnpaidMonths from NOW.
        // e.g., if maxUnpaidMonths = 2, and today is Feb 9, we look at:
        // Jan (Last Month), Dec (Month before).
        // Or if we include current month: Feb, Jan.
        // Let's assume "Unpaid Months" means fully past months + potentially current month if due date passed.
        // For safety and fairness, usually we check PAST months that are fully over.
        // Let's count "pending" or "overdue" fees for ACTIVE enrollments.

        $activeEnrollments = Enrollment::where('status', 'active')->get();

        $droppedCount = 0;

        foreach ($activeEnrollments as $enrollment) {
            $studentId = $enrollment->user_id;
            $courseId = $enrollment->course_id;

            // Check how many UNPAID (pending/overdue) fee records exist for this student-course pair
            // We should only count fees that are actually generated and unpaid.
            // AND we should possibly check if they missed generation? (Complex).
            // Simplest robust way: Count "pending" or "overdue" fees with due_date < NOW.
            
            $unpaidFeesCount = StudentFee::where('student_id', $studentId)
                ->where('course_id', $courseId)
                ->whereIn('status', ['pending', 'overdue'])
                // Optional: Check if the fee month is strictly in the past? 
                // Let's just trust the generated fees. If a fee is generated and not paid, it counts.
                // Maybe add a grace period check? (due_date < now)
                ->where('due_date', '<', Carbon::now()) 
                ->count();

            if ($unpaidFeesCount >= $maxUnpaidMonths) {
                // Drop the student
                $this->dropStudent($enrollment, $unpaidFeesCount);
                $droppedCount++;
            }
        }

        $this->info("Auto-drop process completed. Dropped {$droppedCount} students.");
    }

    protected function dropStudent($enrollment, $unpaidCount)
    {
        $student = User::find($enrollment->user_id);
        $course = Course::find($enrollment->course_id);

        if (!$student || !$course) return;

        $this->info("Dropping Student: {$student->name} (ID: {$student->id}) from Course: {$course->name} (Unpaid Months: {$unpaidCount})");

        // 1. Update Enrollment Status
        $enrollment->update([
            'status' => 'dropped',
            'updated_at' => now()
        ]);
        
        // 2. Notify Student
        Notification::create([
            'user_id' => $student->id,
            'type' => 'enrollment_dropped',
            'title' => 'Course Dropped due to Non-Payment',
            'message' => "You have been dropped from '{$course->name}' because of {$unpaidCount} unpaid fee months. Please contact administration.",
            'data' => json_encode(['course_id' => $course->id])
        ]);

        // 3. Notify Parent (if exists)
        if ($student->parent_email) {
             $parent = User::where('email', $student->parent_email)->first();
             if ($parent) {
                Notification::create([
                    'user_id' => $parent->id,
                    'type' => 'enrollment_dropped',
                    'title' => 'Child Dropped from Course',
                    'message' => "Your child {$student->name} has been dropped from '{$course->name}' due to outstanding fees.",
                    'data' => json_encode(['course_id' => $course->id, 'student_id' => $student->id])
                ]);
             }
        }
    }
}
