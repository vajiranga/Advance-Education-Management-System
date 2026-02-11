<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;

class EnrollmentController extends Controller
{
    // Enroll a student in a course
    // Enroll a student in a course
    public function enroll(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $currentUser = $request->user();
        $targetUserId = $currentUser->id;

        Log::info('Enroll Request', ['request' => $request->all(), 'enroller' => $currentUser->id, 'role' => $currentUser->role]);

        // Allow Admin/SuperAdmin/Teacher to enroll others, OR User enrolling themselves
        if ($request->filled('user_id')) {
            if (in_array($currentUser->role, ['admin', 'super_admin', 'teacher'])) {
                $targetUserId = $request->user_id;
            } elseif ($request->user_id == $currentUser->id) {
                $targetUserId = $currentUser->id;
            } else {
                Log::warning('Enroll Unauthorized', ['user' => $currentUser->id]);
                return response()->json(['message' => 'Unauthorized to enroll students. Role: ' . $currentUser->role], 403);
            }
        }

        Log::info('Target User ID determined', ['target' => $targetUserId]);

        // Check if ALREADY ACTIVE
        $active = DB::table('enrollments')
                    ->where('user_id', $targetUserId)
                    ->where('course_id', $request->course_id)
                    ->where('status', 'active')
                    ->first();

        if ($active) {
             Log::info('User already active', ['user_id' => $targetUserId, 'course_id' => $request->course_id]);
             return response()->json(['message' => 'Already enrolled'], 400);
        }

        // Check for ANY existing record (dropped, pending, etc)
        $existing = DB::table('enrollments')
                    ->where('user_id', $targetUserId)
                    ->where('course_id', $request->course_id)
                    ->first();

        if ($existing) {
            // Re-activate or Activate enrollment
            DB::table('enrollments')
                ->where('id', $existing->id)
                ->update([
                    'status' => 'active',
                    'updated_at' => now(),
                    'enrolled_at' => now()
                ]);

            Log::info('Re-activated user', ['user_id' => $targetUserId]);
            return response()->json(['message' => 'Enrolled successfully (Re-activated)']);
        }

        DB::table('enrollments')->insert([
            'user_id' => $targetUserId,
            'course_id' => $request->course_id,
            'status' => 'active',
            'enrolled_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info('New enrollment created', ['user_id' => $targetUserId]);

        // Generate Fee for the current month immediately
        try {
            $course = Course::find($request->course_id);
            if ($course && $course->fee_amount > 0) {
                 $currentMonth = now()->format('Y-m');
                 $exists = \App\Models\StudentFee::where('student_id', $targetUserId)
                        ->where('course_id', $request->course_id)
                        ->where('month', $currentMonth)
                        ->exists();

                 if (!$exists) {
                     \App\Models\StudentFee::create([
                        'student_id' => $targetUserId,
                        'course_id' => $course->id,
                        'month' => $currentMonth,
                        'amount' => $course->fee_amount,
                        'due_date' => now()->addDays(7), // Due in 7 days for new enrollments
                        'status' => 'pending'
                     ]);

                     // Notify Student about Fee
                     \App\Models\Notification::create([
                        'user_id' => $targetUserId,
                        'type' => 'fee_due',
                        'title' => 'Enrollment Fee Due',
                        'message' => 'Enrollment fee for ' . $course->name . ' is now due.',
                        'data' => json_encode(['course_id' => $course->id])
                     ]);
                 }
            }

            // Notify Teacher
            $teacherId = $course->teacher_id; // Using ID directly if relation not eager loaded
            if ($teacherId) {
                 \App\Models\Notification::create([
                     'user_id' => $teacherId,
                     'type' => 'new_student',
                     'title' => 'New Student Enrolled',
                     'message' => "A new student (ID: {$targetUserId}) has enrolled in {$course->name}",
                     'data' => json_encode(['course_id' => $course->id, 'student_id' => $targetUserId])
                 ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to generate enrollment fee or notification', ['error' => $e->getMessage()]);
        }

        return response()->json(['message' => 'Enrolled successfully']);
    }

    // Get enrolled courses for the logged-in student
    public function myCourses(Request $request)
    {
        $user = $request->user();

        // Get IDs of enrolled courses
        $enrolledCourseIds = $user->courses()->pluck('courses.id');

        // Fetch Regular + Extra Classes linked to enrolled courses
        $courses = Course::where(function($q) use ($enrolledCourseIds) {
            $q->whereIn('id', $enrolledCourseIds)
              ->orWhere(function($sub) use ($enrolledCourseIds) {
                  $sub->whereIn('parent_course_id', $enrolledCourseIds)
                      ->where('type', 'extra')
                      ->where('status', 'approved');
              });
        })->with(['teacher', 'subject', 'batch', 'hall', 'parentCourse'])->withCount('students')->orderBy('created_at', 'desc')->get();

        // Filter out expired Extra Classes based on System Setting (Default 3 days)
        $visDays = (int) (\App\Models\SystemSetting::where('key', 'extraClassVisibilityDays')->value('value') ?? 3);
        $cutoffDate = now()->subDays($visDays)->format('Y-m-d');
        $currentMonth = now()->format('Y-m');

        $filtered = $courses->map(function($course) use ($cutoffDate, $currentMonth, $user) {
            // 1. Check Expiry for Extra Classes
            if ($course->type === 'extra') {
                $schedule = $course->schedule;
                if (isset($schedule['date']) && $schedule['date'] < $cutoffDate) {
                    return null; // Expired (older than 3 days)
                }
            }

            // 2. Determine Fee Status
            $feeStatus = 'due'; // Default
            if ($course->fee_amount <= 0) {
                $feeStatus = 'free';
            } else {
                // Check DB for fee record
                $fee = \App\Models\StudentFee::where('student_id', $user->id)
                            ->where('course_id', $course->id)
                            ->where('month', $currentMonth)
                            ->first();

                if ($fee) {
                    if ($fee->status === 'paid') $feeStatus = 'paid';
                    else if ($fee->status === 'pending') $feeStatus = 'due';
                    else if ($fee->status === 'overdue') $feeStatus = 'overdue';
                } else {
                    // No fee record generated yet? Assume Due if fee > 0
                    // But if it's an Extra class, maybe fee is handled via Parent?
                    // Basic rule: If fee_amount > 0, it is 'due' until paid.
                    $feeStatus = 'due';
                }
            }

            // Attach as custom attribute
            $course->current_month_payment_status = $feeStatus;
            return $course;

        })->filter(); // Remove nulls

        return response()->json(['data' => $filtered->values()]);
    }

    // Drop a course
    public function drop(Request $request, $courseId)
    {
        $user = $request->user();

        DB::table('enrollments')
            ->where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->update(['status' => 'dropped', 'updated_at' => now()]);

        return response()->json(['message' => 'Course dropped successfully']);
    }
}
