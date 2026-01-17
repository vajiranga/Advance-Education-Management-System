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
                     
                     // Notification logic could go here
                 }
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate enrollment fee', ['error' => $e->getMessage()]);
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

        return response()->json(['data' => $courses]);
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
