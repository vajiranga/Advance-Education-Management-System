<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    // Enroll a student in a course
    public function enroll(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $user = $request->user();
        
        // check if already enrolled
        $exists = DB::table('enrollments')
                    ->where('user_id', $user->id)
                    ->where('course_id', $request->course_id)
                    ->first();

        if ($exists) {
            return response()->json(['message' => 'Already enrolled'], 400);
        }

        DB::table('enrollments')->insert([
            'user_id' => $user->id,
            'course_id' => $request->course_id,
            'status' => 'active', // For now auto-active, later maybe 'pending' for payment
            'enrolled_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Enrolled successfully']);
    }

    // Get enrolled courses for the logged-in student
    public function myCourses(Request $request) 
    {
        $user = $request->user();

        // Use Eloquent relationship defined in User model
        $courses = $user->courses()->with(['teacher', 'subject', 'batch', 'hall'])->get();

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
