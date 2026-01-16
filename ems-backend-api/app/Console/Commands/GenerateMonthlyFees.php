<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Course;
use App\Models\StudentFee;
use App\Models\User;
use App\Models\Notification; // Assuming simple Notification model
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateMonthlyFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fees:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly fees for all active students and update active status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting fee generation...');
        
        $currentMonth = Carbon::now()->format('Y-m'); // 2026-01
        $dueDate = Carbon::now()->setDay(10)->format('Y-m-d');

        // 1. Generate Fees
        $courses = Course::with(['students' => function($q) {
            $q->wherePivot('status', 'active'); // Only active students
        }])->get();

        $count = 0;

        foreach ($courses as $course) {
            foreach ($course->students as $student) {
                // Check if fee already exists
                $exists = StudentFee::where('student_id', $student->id)
                    ->where('course_id', $course->id)
                    ->where('month', $currentMonth)
                    ->exists();

                if (!$exists) {
                    $feeAmount = $course->fee_amount ?? 0;
                    
                    if ($feeAmount > 0) {
                        StudentFee::create([
                            'student_id' => $student->id,
                            'course_id' => $course->id,
                            'month' => $currentMonth,
                            'amount' => $feeAmount,
                            'due_date' => $dueDate,
                            'status' => 'pending'
                        ]);
                        
                        // Create Notification
                        try {
                            // Assuming a simple notification table structure
                            // Adjust fields based on actual table
                            Notification::create([
                                'user_id' => $student->id,
                                'type' => 'payment_due',
                                'title' => 'New Fee Generated',
                                'message' => "Payment for {$course->name} ({$currentMonth}) is now available.",
                                'is_read' => false
                            ]);
                            
                            // Notify Parent if exists
                            if ($student->parent_id) {
                                 Notification::create([
                                    'user_id' => $student->parent_id,
                                    'type' => 'payment_due',
                                    'title' => 'Child Fee Due',
                                    'message' => "Payment for {$student->name} - {$course->name} ({$currentMonth}) is now available.",
                                    'is_read' => false
                                ]);
                            }
                        } catch (\Exception $e) {
                            // Ignore notification errors
                        }

                        $count++;
                    }
                }
            }
        }
        
        $this->info("Generated {$count} fee records.");

        // 2. Check for Inactive Students (4 months unpaid)
        $this->info('Checking for inactive students...');
        
        $students = User::where('role', 'student')->where('status', 'active')->get();
        
        foreach ($students as $student) {
             // Count unpaid fees before current month
             $unpaidMonths = StudentFee::where('student_id', $student->id)
                ->where('status', 'pending')
                ->where('month', '<', $currentMonth)
                ->count();
                
             if ($unpaidMonths >= 4) {
                 $student->status = 'inactive';
                 $student->save();
                 $this->info("Deactivated student: {$student->name} (ID: {$student->id}) due to {$unpaidMonths} unpaid months.");
             }
        }
        
        $this->info('Fee generation completed.');
    }
}
